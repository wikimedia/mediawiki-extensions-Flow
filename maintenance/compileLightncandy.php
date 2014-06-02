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

		$filenames = $lightncandy->getTemplateFilenames( $templateName );

		if ( !file_exists( $filenames['template'] ) ) {
			$this->error( "Could not find template at: {$filenames['template']}" );
		}
		if ( file_exists( $filenames['compiled'] ) ) {
			if ( !unlink( $filenames['compiled'] ) ) {
				$this->error( "Failed to unlink previously compiled code: {$filenames['compiled']}" );
			}
		}

		$lightncandy->getTemplate( $templateName );
		if ( !file_exists( $filenames['compiled'] ) ) {
			$this->error( "Template compilation completed, but no compiled code found on disk" );
		}

		echo "\nSuccessfully compiled $templateName to {$filenames['compiled']}\n\n";
	}
}

$maintClass = 'CompileLightncandy'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
