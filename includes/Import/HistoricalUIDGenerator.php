<?php

namespace Flow\Import;

use DeferredUpdates;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Import\Postprocessor\Postprocessor;
use Flow\Import\Postprocessor\ProcessorGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoaderFactory;
use IP;
use MWCryptRand;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use SplQueue;
use Title;
use UIDGenerator;
use User;

/**
 * Modified version of UIDGenerator generates historical timestamped
 * uid's for use when importing older data.
 *
 * DO NOT USE for normal UID generation, this is likely to run into
 * id collisions.
 *
 * The import process needs to identify collision failures reported by
 * the database and re-try importing that item with another generated
 * uid.
 */
class HistoricalUIDGenerator extends UIDGenerator {
	public static function historicalTimestampedUID88( $timestamp, $base = 10 ) {
		static $counter = false;
		if ( $counter === false ) {
			$counter = mt_rand( 0, 256 );
		}

		$time = array(
			// seconds
			wfTimestamp( TS_UNIX, $timestamp ),
			// milliseconds
			mt_rand( 0, 1000 )
		);

		// The UIDGenerator is implemented very specifically to have
		// a single instance, we have to reuse that instance.
		$gen = self::singleton();
		self::rotateNodeId( $gen );
		$binaryUUID = $gen->getTimestampedID88(
			array( $time, ++$counter % 1024 )
		);

		return wfBaseConvert( $binaryUUID, 2, $base );
	}

	/**
	 * Rotate the nodeId to a random one. The stable node is best for
	 * generating "now" uid's on a cluster of servers, but repeated
	 * creation of historical uid's with one or a smaller number of
	 * machines requires use of a random node id.
	 *
	 * @param UIDGenerator $gen
	 */
	protected static function rotateNodeId( UIDGenerator $gen ) {
		// 4 bytes = 32 bits
		$gen->nodeId32 = wfBaseConvert( MWCryptRand::generateHex( 8, true ), 16, 2, 32 );
		// 6 bytes = 48 bits, used for 128bit uid's
		//$gen->nodeId48 = wfBaseConvert( MWCryptRand::generateHex( 12, true ), 16, 2, 48 );
	}
}
