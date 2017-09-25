<?php

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/commandLine.inc";
require_once "$IP/extensions/Flow/FlowActions.php";

$moderationChangeTypes = [
	'hide-post',
	'hide-topic',
	'delete-post',
	'delete-topic',
	'suppress-post',
	'suppress-topic',
	'lock-topic',
	'restore-post',
	'restore-topic',
];

$csvOutput = fopen( 'repair_results_from_parent_' . wfWikiID() . '.csv', 'w' );
if ( !$csvOutput ) {
	die( "Could not open results file\n" );
}
fputcsv( $csvOutput, [ "uuid", "esurl", "flags" ] );

$dbr = Flow\Container::get( 'db.factory' )->getDB( DB_REPLICA );
$it = new BatchRowIterator(
	$dbr,
	'flow_revision',
	[ 'rev_id' ],
	10
);
$it->addConditions( [ 'rev_user_wiki' => wfWikiID() ] );
$it->setFetchColumns( [ 'rev_change_type', 'rev_parent_id' ] );

$totalNullContentWithParent = 0;
$totalNullParentContent = 0;
$totalBadQueryResult = 0;
$totalMatched = 0;
foreach ( $it as $batch ) {
	foreach ( $batch as $rev ) {
		$item = ExternalStore::fetchFromURL( $rev->rev_content );
		if ( $item ) {
			// contains valid data
			continue;
		}

		$changeType = $rev->rev_change_type;
		while ( is_string( $wgFlowActions[$changeType] ) ) {
			$changeType = $wgFlowActions[$changeType];
		}
		if ( !in_array( $changeType, $moderationChangeTypes ) ) {
			// doesn't inherit content
			continue;
		}

		$uuid = Flow\Model\UUID::create( $rev->rev_id );
		echo "\n********************\n\nProcessing revision " . $uuid->getAlphadecimal() . "\n";

		++$totalNullContentWithParent;
		$res = iterator_to_array( $dbr->select(
			/* from */ 'flow_revision',
			/* select */ [ 'rev_content', 'rev_flags' ],
			/* where */ [
				'rev_id' => new \Flow\Model\UUIDBlob( $rev->rev_parent_id ),
			],
			__FILE__
		) );
		// not likely ... but lets be careful
		if ( !$res ) {
			echo "No parent found?\n";
			$totalBadQueryResult++;
			continue;
		} elseif ( count( $res ) > 1 ) {
			echo "Multiple parents found?\n";
			$totalBadQueryResult++;
			continue;
		}

		$parent = reset( $res );
		$parentItem = ExternalStore::fetchFromURL( $parent->rev_content );
		if ( $parentItem ) {
			echo "MATCHED\n";
			fputcsv( $csvOutput, [ $uuid->getAlphadecimal(), $parent->rev_content, $parent->rev_flags ] );
			++$totalMatched;
		} else {
			echo "Parent item is null\n";
			++$totalNullParentContent;
		}
	}
}

echo "Considered $totalNullContentWithParent revisions with parents and no content\n";
if ( $totalNullContentWithParent > 0 ) {
	echo "Could not fix $totalNullParentContent (" . number_format( 100 * $totalNullParentContent / $totalNullContentWithParent ) . "%) due to parent not having content\n";
	echo "Could not fix $totalBadQueryResult (" . number_format( 100 * $totalBadQueryResult / $totalNullContentWithParent ) . "%) due to not finding the parent revision\n";
	echo "Found matches for $totalMatched (" . number_format( 100 * $totalMatched / $totalNullContentWithParent ) . "%)\n";
}
