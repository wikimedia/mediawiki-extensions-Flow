<?php

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/commandLine.inc";
require_once "$IP/extensions/Flow/FlowActions.php";

if ( !isset( $argv[1] ) ) {
	die( "Usage: {$argv[0]} <csv>\n\n" );
}
if ( !is_file( $argv[1] ) ) {
	die( 'Provided CSV file does not exist' );
}
$csv = fopen( $argv[1], "r" );
if ( fgetcsv( $csv ) !== [ 'uuid', 'esurl', 'flags' ] ) {
	die( 'Provided CSV file does not have the expected header' );
}

$fixed = 0;
$dbw = Flow\Container::get( 'db.factory' )->getDB( DB_MASTER );
while ( $row = fgetcsv( $csv ) ) {
	if ( count( $row ) !== 3 ) {
		var_dump( $row );
		die( 'All rows in CSV file must have 2 entries' );
	}
	list( $uuid, $esUrl, $flags ) = $row;
	if ( !$uuid || !$esUrl || !$flags ) {
		var_dump( $row );
		die( 'All rows in CSV file must have a uuid, flags and an external store url' );
	}
	$uuid = Flow\Model\UUID::create( $uuid );
	$dbw->update(
		/* table */'flow_revision',
		/* set */ [
			'rev_content' => $esUrl,
			'rev_flags' => $flags,
		],
		/* where */ [
			'rev_id' => $uuid->getBinary()
		]
	);
	++$fixed;
}

echo "Updated $fixed revisions\n\n";
