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

$plaintextChangeTypes = [
	'edit-title',
	'new-topic',
];

$csvOutput = fopen( 'repair_results_' . wfWikiID() . '.csv', 'w' );
if ( !$csvOutput ) {
	die( "Could not open results file\n" );
}
fputcsv( $csvOutput, [ "uuid", "esurl", "flags" ] );

$it = new BatchRowIterator(
	Flow\Container::get( 'db.factory' )->getDB( DB_REPLICA ),
	'flow_revision',
	[ 'rev_id' ],
	10
);
$it->addConditions( [ 'rev_user_wiki' => wfWikiID() ] );
$it->setFetchColumns( [ 'rev_content', 'rev_content_length', 'rev_change_type', 'rev_parent_id' ] );

$dbr = wfGetDB( DB_REPLICA );
$totalMissingConsidered = 0;
$totalCompleteMatch = 0;
$totalMultipleMatches = 0;
$totalResolvedMultipleMatches = 0;
$totalNoMatch = 0;
$totalNoChangeRevisions = 0;
$totalMatchButInvalid = 0;
foreach ( $it as $batch ) {
	foreach ( $batch as $rev ) {
		$item = ExternalStore::fetchFromURL( $rev->rev_content );
		if ( $item ) {
			// contains valid data
			continue;
		}

		++$totalMissingConsidered;
		$uuid = Flow\Model\UUID::create( $rev->rev_id );
		echo "\n********************\n\nProcessing revision " . $uuid->getAlphadecimal() . "\n";
		$tsEscaped = $dbr->addQuotes( $uuid->getTimestamp( TS_MW ) );

		$changeType = $rev->rev_change_type;
		while ( is_string( $wgFlowActions[$changeType] ) ) {
			$changeType = $wgFlowActions[$changeType];
		}
		if ( in_array( $changeType, $moderationChangeTypes ) ) {
			$totalNoChangeRevisions++;
			echo "Revision inherits parent content, not searching\n";
			continue;
		}
		// Collect 10 core revisions each before and after our revision
		$before = query_revisions( $dbr, '<=', $tsEscaped );
		$after = query_revisions( $dbr, '>', $tsEscaped );

		$first = reset( $before );
		$last = end( $after );
		echo "Considering core revisions from " . $first->rev_timestamp . " to " . $last->rev_timestamp . "\n";

		$esIdsForCluster = [];
		foreach ( [ $before, $after ] as $results ) {
			foreach ( $results as $row ) {
				$parts = explode( '/', $row->old_text );
				if ( isset( $parts[4] ) ) {
					// Part of a multi-revision blob.  This was not created
					// at rev_timestamp
					continue;
				}
				$cluster = $parts[2];
				$id = (int)$parts[3];
				$esIdsForCluster[$cluster][] = $id;
			}
		}

		// find any gaps in ES within this area
		$matches = $lengths = [];
		$invalid = false;

		$flags = 'utf-8,gzip,external';
		if ( in_array( $changeType, $plaintextChangeTypes ) ) {
			$flags .= ',wikitext';
		} else {
			$flags .= ',html';
		}
		echo "Expected length: " . $rev->rev_content_length . "\n";
		foreach ( array_keys( $esIdsForCluster ) as $cluster ) {
			sort( $esIdsForCluster[$cluster] );
			$lastId = reset( $esIdsForCluster[$cluster] );

			foreach ( $esIdsForCluster[$cluster] as $id ) {
				if ( $id === $lastId || $id === $lastId + 1 ) {
					$lastId = $id;
					continue;
				}
				$range = range( $lastId + 1, $id - 1 );
				$lastId = $id;
				echo "Checking " . count( $range ) . " es urls\n";
				if ( count( $range ) > 100 ) {
					echo "More than 100 potential es urls, skipping\n";
					$invalid = true;
					continue;
				}
				foreach ( $range as $possible ) {
					$url = "DB://$cluster/$possible";
					$content = gzinflate( ExternalStore::fetchFromURL( $url ) );
					Wikimedia\suppressWarnings();
					$unserializedContent = unserialize( $content );
					Wikimedia\restoreWarnings();
					if ( false !== $unserializedContent ) {
						// if it unserializes, its not our content
						continue;
					}
					Wikimedia\suppressWarnings();
					$json = json_decode( $content, true );
					Wikimedia\restoreWarnings();
					if ( $json && count( $json ) === 1 && isset( $json['flow-workflow'] ) ) {
						// while technically possible to be a topic title, i'm almost
						// certain this is a core revisions inserted by flow in the form
						// of: {"flow-workflow":"sbk26yv6cpcxxm87"}
						continue;
					}
					if ( !in_array( $changeType, $plaintextChangeTypes ) ) {
						if ( false === strpos( $content, 'data-parsoid' ) ) {
							continue;
						}
						$content = parsoid_to_wikitext( $content );
					}
					$len = mb_strlen( $content );
					if ( $rev->rev_content_length == $len ) {
						$doAppend = true;
						foreach ( $matches as $match ) {
							if ( $match[1] === $content ) {
								$doAppend = false;
								break;
							}
						}
						if ( $doAppend ) {
							$matches[] = [ $url, $content, $flags ];
						}
					} else {
						$lengths[] = $len;
					}
				}
			}
		}
		if ( $invalid && count( $matches ) === 1 ) {
			echo "MATCHED BUT INVALID\n";
			var_dump( $matches );
			++$totalMatchButInvalid;
		} elseif ( $invalid || !$matches ) {
			echo "NO MATCH\n";
			var_dump( $matches );
			var_dump( $lengths );
			++$totalNoMatch;
		} elseif ( count( $matches ) === 1 ) {
			list( $url, $content, $flags ) = reset( $matches );
			echo "SINGLE DIRECT MATCH: $url : " . truncate( $content, 1024 ) . "\n";
			++$totalCompleteMatch;
			fputcsv( $csvOutput, [ $uuid->getAlphadecimal(), $url, $flags ] );
		} else {
			echo "MULTIPLE POTENTIAL MATCHES:\n";
			++$totalMultipleMatches;
			$multipleMatches[$uuid->getAlphadecimal()] = $matches;
			foreach ( $matches as $match ) {
				list( $url, $content, $flags ) = $match;
				echo "\t$url : " . truncate( $content, 1024 ) . "\n";
			}
		}
	}
}

if ( $multipleMatches ) {
	echo "\n********************\n\nAttempting to resolve multiple match sets\n";
	while ( $multipleMatches ) {
		echo "\n********************\n\n";
		// Grab the first key/value pair from $multipleMatches as our
		// first matching group
		$current = reset( $multipleMatches );
		$group = [
			key( $multipleMatches ) => $current,
		];
		array_shift( $multipleMatches );
		// Look for other revisions in $multipleMatches that matched at least
		// one of the same pieces of ExternalStore data.
		do {
			$repeat = false;
			foreach ( $multipleMatches as $uuid => $matches ) {
				foreach ( $matches as $idx => $subMatch ) {
					if ( array_search( $subMatch, $current ) !== false ) {
						$group[$uuid] = $matches;
						// expand $current to contain all individual
						// external store data represented in $group
						$current = array_merge( $current, $matches );
						unset( $multipleMatches[$uuid] );
						// because $current has expanded we need to go
						// back to the begining of $multipleMatches
						$repeat = true;
						break 2;
					}
				}
			}
		} while ( $repeat );

		// Look through all the data in $group.  Consider it a match if every
		// revision matched the exact same set of external store urls.
		// Basically what we are looking for is, for example, a set of 3 revisions
		// that all matched the same 3 pieces of ExternalStore data.
		$valid = true;
		$expectedMatches = reset( $group );
		foreach ( $group as $uuid => $matches ) {
			if ( count( $matches ) !== count( $group ) ) {
				echo "Number of matches does not line up: " . count( $matches ) . " !== " . count( $group ) . "\n";
				$valid = false;
				break;
			}
			if ( $matches != $expectedMatches ) {
				echo "Matched subsets do not line up: " . json_encode( $matches ) . " != " . json_encode( $expectedMatches ) . "\n";
				$valid = false;
				break;
			}
		}
		if ( $valid ) {
			// We have multiple revisions that all matched the exact same external
			// store data.  Make the assumption that those revisions and the es id's
			// were created in a strictly ordered fashion, such that the first revision
			// lines up with the first ES id, the second with the second, etc.
			echo "declare victory!\n";
			foreach ( array_keys( $group ) as $uuid ) {
				$match = array_shift( $expectedMatches );
				list( $url, $content, $flags ) = $match;
				fputcsv( $csvOutput, [ $uuid, $url, $flags ] );
				--$totalMultipleMatches;
				++$totalResolvedMultipleMatches;
			}
		} else {
			var_dump( $group );
		}
	}
	echo "\n********************\n";
}

echo "\n\n\nLooked at $totalMissingConsidered flow revisions\n";
echo "Found matches for $totalCompleteMatch (" . number_format( 100 * $totalCompleteMatch / $totalMissingConsidered ) . "%)\n";
echo "Found multiple matches for $totalMultipleMatches (" . number_format( 100 * $totalMultipleMatches / $totalMissingConsidered ) . "%)\n";
echo "Found no match for $totalNoMatch (" . number_format( 100 * $totalNoMatch / $totalMissingConsidered ) . "%)\n";
echo "Found $totalNoChangeRevisions that will inherit parent content (" . number_format( 100 * $totalNoChangeRevisions / $totalMissingConsidered ) . "%)\n";
echo "Found a match but invalid due to size of es gaps for $totalMatchButInvalid (" . number_format( 100 * $totalMatchButInvalid / $totalMissingConsidered ). "%)\n";
echo "Resolved $totalResolvedMultipleMatches multiple matches (" . number_format( 100 * $totalResolvedMultipleMatches / $totalMissingConsidered ) . "%)\n";

function query_revisions( $dbr, $op, $tsEscaped ) {
	$direction = $op[0] === '>' ? 'ASC' : 'DESC';
	$sql = "SELECT revision.rev_timestamp, text.old_text " .
		"     FROM revision " .
		"     JOIN text ON revision.rev_text_id = old_id " .
		"LEFT JOIN revision parent ON parent.rev_id = revision.rev_parent_id " .
		"    WHERE revision.rev_timestamp $op $tsEscaped " .
		"      AND revision.rev_text_id <> parent.rev_text_id " .
		"    ORDER BY revision.rev_timestamp $direction " .
		"    LIMIT 10";

	$res = $dbr->query( $sql, __METHOD__ );
	return iterator_to_array( $res );
}

function parsoid_to_wikitext( $content, $retry = 3 ) {
	static $cache = [];
	$hash = md5( $content );
	if ( isset( $cache[$hash] ) ) {
		return $cache[$hash];
	}
	try {
		$wikitext = Flow\Conversion\Utils::convert( 'html', 'wt', $content, Title::newMainPage() );
		$cache[$hash] = $wikitext;
		return $cache[$hash];
	} catch ( Flow\Exception\NoParserException $e ) {
		echo "failed to convert to wikitext: " . truncate( $content, 1024 ) . "\n";
		$cache[$hash] = $content;
		return $cache[$hash];
	}
}

function truncate( $string, $length ) {
	if ( strlen( $string ) > $length ) {
		return substr( $string, 0, $length ) . '...';
	} else {
		return $string;
	}
}
