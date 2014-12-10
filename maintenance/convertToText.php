<?php

use Flow\Parsoid\Utils;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

class ConvertToText extends Maintenance {
	/**
	 * @var Title
	 */
	protected $pageTitle;

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts a specific Flow page to text";

		$this->addArg( 'page', 'The page to convert', true /*required*/ );
	}

	public function execute() {
		$pageName = $this->getArg( 0 );
		$this->pageTitle = Title::newFromText( $pageName );

		if ( ! $this->pageTitle ) {
			$this->error( 'Invalid page title', true );
		}

		$exporter = new Flow\Utils\Export;
		print $exporter->export( $this->pageTitle );
	}
}

$maintClass = "ConvertToText";
require_once( RUN_MAINTENANCE_IF_MAIN );
