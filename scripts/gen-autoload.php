<?php

require_once __DIR__ . '/../../../includes/utils/AutoloadGenerator.php';

function main() {
	$base = dirname( __DIR__ );
	$generator = new AutoloadGenerator( $base );
	foreach ( array( 'includes', 'tests/phpunit', 'vendor' ) as $dir ) {
		$generator->readDir( $base . '/' . $dir );
	}
	foreach ( glob( $base . '/*.php' ) as $file ) {
		$generator->readFile( $file );
	}
	// read entire maint dir, move helper to includes? to core?
	$generator->readFile( $base . '/maintenance/MaintenanceDebugLogger.php' );

	$target = $generator->getTargetFileInfo();

	file_put_contents(
		$target['filename'],
		$generator->getAutoload( basename( __DIR__ ) . '/' . basename( __FILE__ ) )
	);

	echo "Done.\n\n";
}

main();
