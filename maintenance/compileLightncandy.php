<?php

use Flow\Container;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Populate the *_user_ip fields within flow.  This only updates
 * the database and not the cache.  The model loading layer handles
 * cached old values.
 *
 * @ingroup Maintenance
 */
class CompileLightncandy extends Maintenance {
	public function execute() {
		$templateName = $this->getArg( 0 );
		$lightncandy = Container::get( 'lightncandy' );

		$filename = $lightncandy->getTemplateFilename( $templateName );
		if ( !file_exists( $filename ) ) {
			$this->error( "Could not find template at: $filename" );
		}
		$compiled = "$filename.php";
		if ( file_exists( $compiled ) ) {
			if ( !unlink( $compiled ) ) {
				$this->error( "Failed to unlink previously compiled code: $compiled" );
			}
		}

		$lightncandy->getTemplate( $templateName );
		if ( !file_exists( $compiled ) ) {
			$this->error( "Template compilation completed, but no compiled code found on disk" );
		}

		echo "\nSuccessfully compiled $templateName to $compiled\n\n";
	}
}

$maintClass = 'CompileLightncandy'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
