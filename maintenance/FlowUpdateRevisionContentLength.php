<?php

use Flow\Container;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

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
	static private $revisionTypes = array(
		'post' => 'Flow\Model\PostRevision',
		'header' => 'Flow\Model\Header',
		'post-summary' => 'Flow\Model\PostSummary',
	);

	/**
	 * @var Flow\DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var Flow\Data\ManagerGroup
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
		$this->mDescription = "Updates content length for revisions with unset content length.";
	}

	public function getUpdateKey() {
		return __CLASS__;
	}

	public function doDBUpdates() {
		// Can't be done in constructor, happens too early in
		// boot process
		$this->dbFactory = Container::get( 'db.factory' );
		$this->storage = Container::get( 'storage' );
		// Since this is a one-shot maintenance script just reach in via reflection
		// to change lenghts
		$this->contentLengthProperty = new ReflectionProperty(
			'Flow\Model\AbstractRevision',
			'contentLength'
		);
		$this->contentLengthProperty->setAccessible( true );
		$this->previousContentLengthProperty = new ReflectionProperty(
			'Flow\Model\AbstractRevision',
			'previousContentLength'
		);
		$this->previousContentLengthProperty->setAccessible( true );

		$dbw = $this->dbFactory->getDb( DB_MASTER );
		// Walk through the flow_revision table
		$it = new EchoBatchRowIterator(
			$dbw,
			/* table = */'flow_revision',
			/* primary key = */'rev_id',
			$this->mBatchSize
		);
		// Only fetch rows with current and previous content length set to 0
		$it->addConditions( array(
			'rev_content_length' => 0,
			'rev_previous_content_length' => 0,
		) );
		// We only need the id and type field
		$it->setFetchColumns( array( 'rev_id', 'rev_type' ) );

		$total = $fail = 0;
		foreach ( $it as $batch ) {
			$dbw->begin();
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
						$this->output( 'Could not locate previous revision: ' . $obj->getPrevRevisionId()->getAlphadecimal() );
						$fail++;
						continue;
					}
				}

				$this->updateRevision( $obj, $previous );
				$om->put( $obj );
				$this->output( '.' );
			}
			$dbw->commit();
			$this->storage->clear();
			$this->dbFactory->waitForSlaves();
		}

		return true;
	}

	protected function updateRevision( AbstractRevision $revision, AbstractRevision $previous = null ) {
		$this->contentLengthProperty->setValue(
			$revision,
			mb_strlen( $revision->getContent( 'wikitext' ) )
		);
		if ( $previous !== null ) {
			$this->previousContentLengthProperty->setValue(
				$revision,
				mb_strlen( $previous->getContent( 'wikitext' ) )
			);
		}
	}
}

$maintClass = 'FlowUpdateRevisionContentLength';
require_once( RUN_MAINTENANCE_IF_MAIN );
