<?php

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Exception\InvalidDataException;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Collection\PostCollection;
use Flow\Maintenance\LogRowUpdateGenerator;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Fixes Flow log entries.
 *
 * @ingroup Maintenance
 */
class FlowFixLog extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Fixes Flow log entries';

		$this->setBatchSize( 300 );
	}

	protected function getUpdateKey() {
		return 'FlowFixLog:version2';
	}

	protected function doDBUpdates() {
		$iterator = new BatchRowIterator( wfGetDB( DB_SLAVE ), 'logging', 'log_id', $this->mBatchSize );
		$iterator->setFetchColumns( array( 'log_id', 'log_params' ) );
		$iterator->addConditions( array(
			'log_type' => array( 'delete', 'suppress' ),
			'log_action' => array(
				'flow-delete-post', 'flow-suppress-post', 'flow-restore-post',
				'flow-delete-topic', 'flow-suppress-topic', 'flow-restore-topic',
			),
		) );

		$updater = new BatchRowUpdate(
			$iterator,
			new BatchRowWriter( wfGetDB( DB_MASTER ), 'logging' ),
			new LogRowUpdateGenerator( $this )
		);
		$updater->setOutput( array( $this, 'output' ) );
		$updater->execute();

		return true;
	}

	/**
	 * parent::output() is a protected method, only way to access it from a
	 * callback in php5.3 is to make a public function. In 5.4 can replace with
	 * a Closure.
	 *
	 * @param string $out
	 * @param mixed $channel
	 */
	public function output( $out, $channel = null ) {
		parent::output( $out, $channel );
	}

	/**
	 * parent::error() is a protected method, only way to access it from the
	 * outside is to make it public.
	 *
	 * @param string $err
	 * @param int $die
	 */
	public function error( $err, $die = 0 ) {
		parent::error( $err, $die );
	}
}

$maintClass = 'FlowFixLog';
require_once( RUN_MAINTENANCE_IF_MAIN );
