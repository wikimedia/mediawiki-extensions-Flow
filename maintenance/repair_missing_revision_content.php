<?php

require_once "$IP/maintenance/commandLine.inc";
require_once "$IP/extensions/Flow/FlowActions.php";

$moderationChangeTypes = array(
	'hide-post',
	'hide-topic',
	'delete-post',
	'delete-topic',
	'suppress-post',
	'suppress-topic',
	'lock-topic',
	'restore-post',
	'restore-topic',
);

$it = new EchoBatchRowIterator(
	Flow\Container::get( 'db.factory' )->getDB( DB_SLAVE ),
	'flow_revision',
	array( 'rev_id' ),
	10
);
$it->addConditions( array( 'rev_user_wiki' => wfWikiId() ) );
$it->setFetchColumns( array( 'rev_content', 'rev_content_length', 'rev_change_type', 'rev_parent_id' ) );

$dbr = wfGetDB( DB_SLAVE );
$totalConsidered = 0;
$totalCompleteMatch = 0;
$totalMultipleMatches = 0;
$totalNoMatch = 0;
$totalNoChangeRevisions = 0;
foreach ( $it as $batch ) {
	foreach ( $batch as $rev ) {
		$item = ExternalStore::fetchFromURL( $rev->rev_content );
		if ( $item ) {
			// contains valid data
			continue;
		}

		++$totalConsidered;
		$uuid = Flow\Model\UUID::create( $rev->rev_id );
		echo "Processing revision " . $uuid->getAlphadecimal() . "\n";
		$tsEscaped = $dbr->addQuotes( $uuid->getTimestamp( TS_MW ) );

		$changeType = $rev->rev_change_type;
		while( is_string( $wgFlowActions[$changeType] ) ) {
			$changeType = $wgFlowActions[$changeType];
		}
		if ( in_array( $changeType, $moderationChangeTypes ) ) {
			$totalNoChangeRevisions++;
			echo "Revision inherits parent content, not searching\n";
			echo "\n********************\n";
			continue;
		}
		// Collect 10 core revisions each before and after our revision
		$before = query_revisions( $dbr, '<=', $tsEscaped );
		$after = query_revisions( $dbr, '>', $tsEscaped );

		$first = reset( $before );
		$last = end( $after );
		echo "Considering core revisions from " . $first->rev_timestamp . " to " . $last->rev_timestamp . "\n";

		$esUrls = array();
		foreach ( array( $before, $after ) as $results ) {
			foreach ( $results as $row ) {
				$parts = explode( '/', $row->old_text );
				$cluster = $parts[2];
				$id = (int)$parts[3];
				$esUrls[$cluster][] = $id;
			}
		}

		// find any gaps in ES within this area
		$matches = $lengths = array();
		echo "Expected length: " . $rev->rev_content_length . "\n";
		foreach ( array_keys( $esUrls ) as $cluster	) {
			sort( $esUrls[$cluster] );
			$lastId = -1;
			foreach ( $esUrls[$cluster] as $id ) {
				if ( $lastId !== -1 && $id > $lastId + 1 ) {
					$range = range( $lastId + 1, $id - 1 );
					if ( count( $range ) > 100 ) {
						echo "More than 100 potential es urls, skipping\n";
						echo "\n********************\n";
						$lengths = $matches = array();
						break 2;
					}
					echo "Checking " . count( $range ) . " es urls\n";
					foreach ( $range as $possible ) {

						$url = "DB://$cluster/$possible";
						$content = gzinflate( ExternalStore::fetchFromURL( $url ) );
						if ( false !== @unserialize( $content ) ) {
							// if it unserializes, its not our content
							continue;
						}
						$len = mb_strlen( $content );
						// @todo probably need to look up the real title
						$parsoidLen = parsoid_len( $content );
						if ( in_array( $rev->rev_content_length, array( $len, $parsoidLen ) ) ) {
							$doAppend = true;
							if ( $matches ) {
								foreach ( $matches as $match ) {
									if ( $match[1] === $content ) {
										$doAppend = false;
									}
								}
							}
							if ( $doAppend ) {
								$matches[] = array( $url, $content );
							}
						} else {
							$lengths[] = array( $len, $parsoidLen );
						}
					}
				}
				$lastId = $id;
			}
		}
		if ( !$matches ) {
			echo "NO MATCH\n";
			var_dump( $lengths );
			++$totalNoMatch;
		} elseif ( count( $matches ) === 1 ) {
			list( $url, $content ) = reset( $matches );
			echo "SINGLE DIRECT MATCH: $url : " . substr( $content, 0, 1024 ) . "\n";
			++$totalCompleteMatch;
		} else {
			echo "MULTIPLE POTENTIAL MATCHES:\n";
			++$totalMultipleMatches;
			foreach ( $matches as $match ) {
				list( $url, $content ) = $match;
				echo "\t$url : " . substr( $content, 0, 1024 ) . "\n";
			}
		}
		echo "\n********************\n";
	}
}

echo "\n\n\nLooked at $totalConsidered flow revisions\n";
echo "Found matches for $totalCompleteMatch (" . number_format( 100 * $totalCompleteMatch / $totalConsidered ) . "%)\n";
echo "Found multiple matches for $totalMultipleMatches (" . number_format( 100 * $totalMultipleMatches / $totalConsidered ) . "%)\n";
echo "Found no match for $totalNoMatch (" . number_format( 100 * $totalNoMatch / $totalConsidered ) . "%)\n";
echo "Found $totalNoChangeRevisions that will inherit parent content (" . number_format( 100 * $totalNoChangeRevisions / $totalConsidered ) . "%)\n";

function query_revisions( $dbr, $op, $tsEscaped ) {
		$res = $dbr->select(
			/* table */ array( 'revision', 'text' ),
			/* select */ array( 'rev_timestamp', 'old_text' ),
			/* where */ array(
				'rev_timestamp ' . $op . ' ' . $tsEscaped,
			),
			__FILE__,
			/* options */ array(
				'LIMIT' => 10,
				'ORDER BY' => 'rev_timestamp ' . ( $op[0] === '>' ? 'ASC' : 'DESC' )
			),
			/* joins */ array(
				'text' => array( 'LEFT JOIN', 'rev_text_id = old_id' ),
			)
		);
		return iterator_to_array( $res );
}


function parsoid_len( $content, $retry = 3 ) {
	static $cache = array();
	$hash = md5( $content );
	if ( isset( $cache[$hash] ) ) {
		return $cache[$hash];
	}
	try {
		return $cache[$hash] = mb_strlen( Flow\Parsoid\Utils::convert( 'html', 'wt', $content, Title::newMainPage() ) );
	} catch ( Flow\Exception\NoParsoidException $e ) {
		echo "failed to estimate parsoid length of " . substr( $content, 0, 1024 ) . "\n";
		return $cache[$hash] = -1;
	}
}
