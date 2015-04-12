<?php

require_once "$IP/maintenance/commandLine.inc";
require_once "$IP/extensions/Flow/FlowActions.php";

if ( !isset( $argv[1] ) ) {
	die( "Usage: {$argv[0]} <csv>\n\n" );
}
if ( !is_file( $argv[1] ) ) {
	die( 'Provided CSV file does not exist' );
}
$csv = fopen( $argv[1], "r" );
if ( fgetcsv( $csv ) !== array( 'uuid', 'esurl' ) ) {
	die( 'Provided CSV file does not have the expected header' );
}


$fixed = 0;
$dbw = Flow\Container::get( 'db.factory' )->getDB( DB_MASTER );
while ( $row = fgetcsv( $csv ) ) {
	if ( count( $row ) !== 2 ) {
		var_dump( $row );
		die( 'All rows in CSV file must have 2 entries' );
	}
	list( $uuid, $esUrl ) = $row;
	if ( !$uuid || !$esUrl ) {
		var_dump( $row );
		die( 'All rows in CSV file must have a uuid and an external store url' );
	}
	$uuid = Flow\Model\UUID::create( $uuid );
	$dbw->update(
		/* table */'flow_revision',
		/* set */ array(
			'rev_content' => $esUrl,
		),
		/* where */ array(
			'rev_id' => $uuid->getBinary()
		)
	);
	++$fixed;
}

echo "Updated $fixed revisions\n\n";

