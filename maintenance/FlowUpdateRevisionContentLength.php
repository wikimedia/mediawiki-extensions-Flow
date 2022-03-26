<?php

namespace Flow\Maintenance;

use BatchRowIterator;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use LoggedUpdateMaintenance;
use ReflectionProperty;
use WikiMap;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * @ingroup Maintenance
 */
class FlowUpdateRevisionContentLength extends LoggedUpdateMaintenance {
	/**
	 * Map from AbstractRevision::getRevisionType() to the class that holds
	 * that type.
	 * @todo seems this should be elsewhere for access by any code
	 *
	 * @var string[]
	 */
	private static $revisionTypes = [
		'post' => \Flow\Model\PostRevision::class,
		'header' => \Flow\Model\Header::class,
		'post-summary' => \Flow\Model\PostSummary::class,
	];

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var ReflectionProperty
	 */
	protected $contentLengthProperty;

	/**
	 * @var ReflectionProperty
	 */
	protected $previousContentLengthProperty;

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Updates content length for revisions with unset content length." );
		$this->setBatchSize( 300 );
		$this->requireExtension( 'Flow' );
	}

	public function getUpdateKey() {
		return 'FlowUpdateRevisionContentLength:version2';
	}

	public function doDBUpdates() {
		// Can't be done in constructor, happens too early in
		// boot process
		$this->dbFactory = Container::get( 'db.factory' );
		$this->storage = Container::get( 'storage' );
		// Since this is a one-shot maintenance script just reach in via reflection
		// to change lengths
		$this->contentLengthProperty = new ReflectionProperty(
			AbstractRevision::class,
			'contentLength'
		);
		$this->contentLengthProperty->setAccessible( true );
		$this->previousContentLengthProperty = new ReflectionProperty(
			AbstractRevision::class,
			'previousContentLength'
		);
		$this->previousContentLengthProperty->setAccessible( true );

		$dbw = $this->dbFactory->getDB( DB_PRIMARY );
		// Walk through the flow_revision table
		$it = new BatchRowIterator(
			$dbw,
			/* table = */'flow_revision',
			/* primary key = */'rev_id',
			$this->getBatchSize()
		);
		// Only fetch rows created by users from the current wiki.
		$it->addConditions( [
			'rev_user_wiki' => WikiMap::getCurrentWikiId(),
		] );
		// We only need the id and type field
		$it->setFetchColumns( [ 'rev_id', 'rev_type' ] );

		$it->setCaller( __METHOD__ );

		$total = $fail = 0;
		foreach ( $it as $batch ) {
			$this->beginTransaction( $dbw, __METHOD__ );
			foreach ( $batch as $row ) {
				$total++;
				if ( !isset( self::$revisionTypes[$row->rev_type] ) ) {
					$this->output( 'Unknown revision type: ' . $row->rev_type );
					$fail++;
					continue;
				}
				$om = $this->storage->getStorage( self::$revisionTypes[$row->rev_type] );
				$revId = UUID::create( $row->rev_id );
				$obj = $om->get( $revId );
				if ( !$obj ) {
					$this->output( 'Could not load revision: ' . $revId->getAlphadecimal() );
					$fail++;
					continue;
				}
				if ( $obj->isFirstRevision() ) {
					$previous = null;
				} else {
					$previous = $om->get( $obj->getPrevRevisionId() );
					if ( !$previous ) {
						$this->output( 'Could not locate previous revision: ' .
							$obj->getPrevRevisionId()->getAlphadecimal() );
						$fail++;
						continue;
					}
				}

				$this->updateRevision( $obj, $previous );

				try {
					$om->put( $obj );
				} catch ( \Exception $e ) {
					$this->error(
						'Failed to update revision ' . $obj->getRevisionId()->getAlphadecimal() .
							': ' . $e->getMessage() . "\n" .
						'Please make sure rev_content, rev_content_length, rev_flags & ' .
							'rev_previous_content_length are part of RevisionStorage::$allowedUpdateColumns.'
					);
					throw $e;
				}
				$this->output( '.' );
			}
			$this->commitTransaction( $dbw, __METHOD__ );
			$this->storage->clear();
			$this->dbFactory->waitForReplicas();
		}

		return true;
	}

	protected function updateRevision( AbstractRevision $revision, AbstractRevision $previous = null ) {
		$this->contentLengthProperty->setValue(
			$revision,
			$this->calcContentLength( $revision )
		);
		if ( $previous !== null ) {
			$this->previousContentLengthProperty->setValue(
				$revision,
				$this->calcContentLength( $previous )
			);
		}
	}

	protected function calcContentLength( AbstractRevision $revision ) {
		if ( $revision->isModerated() && !$revision->isLocked() ) {
			return 0;
		} else {
			return $revision->getContentLength() ?: $revision->calculateContentLength();
		}
	}
}

$maintClass = FlowUpdateRevisionContentLength::class;
require_once RUN_MAINTENANCE_IF_MAIN;
