<?php

use Flow\Container;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Search\Updater;

/**
 * Script that dumps wiki pages or logging database into an XML interchange
 * wrapper format for export or backup
 *
 * Copyright © 2005 Brion Vibber <brion@pobox.com>
 * https://www.mediawiki.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Dump Maintenance
 */

$originalDir = getcwd();

$optionsWithArgs = array( 'pagelist', 'start', 'end', 'revstart', 'revend' );

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance'
	: dirname( __FILE__ ) . '/../../../maintenance' );
require_once $maintPath . '/commandLine.inc';
require_once $maintPath . '/backup.inc';

class FlowExporter extends WikiExporter {
	public static function schemaVersion() {
		return '1';
	}

	/**
	 * Generates the distinct list of authors of an article
	 * Not called by default (depends on $this->list_authors)
	 * Can be set by Special:Export when not exporting whole history
	 *
	 * @param array $cond
	 */
	protected function do_list_authors( $cond ) {
		// @todo: need this?

		$this->author_list = "<contributors>";
		// rev_deleted

		$res = $this->db->select(
			array( 'page', 'revision' ),
			array( 'DISTINCT rev_user_text', 'rev_user' ),
			array(
				$this->db->bitAnd( 'rev_deleted', Revision::DELETED_USER ) . ' = 0',
				$cond,
				'page_id = rev_id',
			),
			__METHOD__
		);

		foreach ( $res as $row ) {
			$this->author_list .= "<contributor>" .
				"<username>" .
				htmlentities( $row->rev_user_text ) .
				"</username>" .
				"<id>" .
				$row->rev_user .
				"</id>" .
				"</contributor>";
		}
		$this->author_list .= "</contributors>";
	}

	/**
	 * @param array|null $pages
	 * @param int|null $startId
	 * @param int|null $endId
	 * @param UUID|null $revStartId
	 * @param UUID|null $revEndId
	 * @throws Exception
	 * @throws TimestampException
	 * @throws \Flow\Exception\InvalidInputException
	 */
	public function dump( array $pages = null, $startId = null, $endId = null, $revStartId = null, $revEndId = null ) {
		/** @var Updater[] $updaters */
		$updaters = Container::get( 'searchindex.updaters' );
		foreach ( $updaters as $updaterType => $updater ) {
			while ( true ) {
				// fetch in batches
				$options = array( 'LIMIT' => 50 ); // @todo

				$conditions = $updater->buildQueryConditions( $revStartId, $revEndId, null );
				if ( $pages ) {
					$conditions['workflow_page_id'] = $pages;
				}
				if ( $startId ) {
					/** @var DatabaseBase $dbr */
					$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );
					$conditions[] = 'workflow_page_id >= ' . $dbr->addQuotes( $startId );
				}
				if ( $endId ) {
					/** @var DatabaseBase $dbr */
					$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );
					$conditions[] = 'workflow_page_id <= ' . $dbr->addQuotes( $endId );
				}
				$revisions = $updater->getRevisions( $conditions, $options );

				// stop if we're all out of revisions
				if ( !$revisions ) {
					break;
				}

				var_dump($revisions);
				// @todo: all of this is to fetch the revs - steal code from dumpFrom to output it

//				$total += $updater->updateRevisions( $revisions, null, null );
//				$this->output( "Indexed $total $updaterType document(s)\n" );

				// prepare for next batch, starting at the next id
				// prevFromId will default to around unix epoch - there can be
				// no data before that
				$prevStartId = $revStartId ?: UUID::getComparisonUUID( '1' );
				$revStartId = $this->getNextFromId( $revisions );

				// make sure we don't get stuck in an infinite loop
				$diff = $prevStartId->getTimestampObj()->diff( $revStartId->getTimestampObj() );
				// invert will be 1 if the diff is a negative time period from
				// $prevFromId to $fromId, which means that the new $timestamp is
				// more recent than our current $result
				if ( $diff->invert ) {
					$this->error(
						'Got stuck in an infinite loop.' . "\n" .
						'workflow_last_update_timestamp is likely incorrect ' .
						'for some workflows.' . "\n" .
						'Run maintenance/FlowFixWorkflowLastUpdateTimestamp.php ' .
						'to automatically fix those.', 1 );
				}

				// prevent memory from being filled up
				Container::get( 'storage' )->clear();
			}
		}
	}

	/**
	 * @param AbstractRevision[] $revisions
	 * @return UUID
	 */
	protected function getNextFromId( array $revisions ) {
		/** @var AbstractRevision $last */
		$last = end( $revisions );

		if ( $last instanceof \Flow\Model\Header ) {
			$timestamp = $last->getRevisionId()->getTimestampObj();
		} else {
			$timestamp = $last->getCollection()->getWorkflow()->getLastUpdatedObj();
		}

		// $timestamp is the timestamp of the last revision we fetched. fromId
		// is inclusive, and we don't want to include what we already have here,
		// so we'll advance 1 more and call that the next fromId
		$timestamp = (int) $timestamp->getTimestamp( TS_UNIX );
		return UUID::getComparisonUUID( $timestamp + 1 );
	}

	/**
	 * @param string $cond
	 * @throws MWException
	 * @throws Exception
	 */
	public function dumpFrom( $cond = '' ) {
		# For logging dumps...
		if ( $this->history & self::LOGS ) {
			$where = array( 'user_id = log_user' );
			# Hide private logs
			$hideLogs = LogEventsList::getExcludeClause( $this->db );
			if ( $hideLogs ) {
				$where[] = $hideLogs;
			}
			# Add on any caller specified conditions
			if ( $cond ) {
				$where[] = $cond;
			}
			# Get logging table name for logging.* clause
			$logging = $this->db->tableName( 'logging' );

			if ( $this->buffer == WikiExporter::STREAM ) {
				$prev = $this->db->bufferResults( false );
			}
			$result = null; // Assuring $result is not undefined, if exception occurs early
			try {
				$result = $this->db->select( array( 'logging', 'user' ),
					array( "{$logging}.*", 'user_name' ), // grab the user name
					$where,
					__METHOD__,
					array( 'ORDER BY' => 'log_id', 'USE INDEX' => array( 'logging' => 'PRIMARY' ) )
				);
				$this->outputLogStream( $result );
				if ( $this->buffer == WikiExporter::STREAM ) {
					$this->db->bufferResults( $prev );
				}
			} catch ( Exception $e ) {
				// Throwing the exception does not reliably free the resultset, and
				// would also leave the connection in unbuffered mode.

				// Freeing result
				try {
					if ( $result ) {
						$result->free();
					}
				} catch ( Exception $e2 ) {
					// Already in panic mode -> ignoring $e2 as $e has
					// higher priority
				}

				// Putting database back in previous buffer mode
				try {
					if ( $this->buffer == WikiExporter::STREAM ) {
						$this->db->bufferResults( $prev );
					}
				} catch ( Exception $e2 ) {
					// Already in panic mode -> ignoring $e2 as $e has
					// higher priority
				}

				// Inform caller about problem
				throw $e;
			}
			# For page dumps...
		} else {
			$tables = array( 'page', 'revision' );
			$opts = array( 'ORDER BY' => 'page_id ASC' );
			$opts['USE INDEX'] = array();
			$join = array();
			if ( is_array( $this->history ) ) {
				# Time offset/limit for all pages/history...
				$revJoin = 'page_id=rev_page';
				# Set time order
				if ( $this->history['dir'] == 'asc' ) {
					$op = '>';
					$opts['ORDER BY'] = 'rev_timestamp ASC';
				} else {
					$op = '<';
					$opts['ORDER BY'] = 'rev_timestamp DESC';
				}
				# Set offset
				if ( !empty( $this->history['offset'] ) ) {
					$revJoin .= " AND rev_timestamp $op " .
						$this->db->addQuotes( $this->db->timestamp( $this->history['offset'] ) );
				}
				$join['revision'] = array( 'INNER JOIN', $revJoin );
				# Set query limit
				if ( !empty( $this->history['limit'] ) ) {
					$opts['LIMIT'] = intval( $this->history['limit'] );
				}
			} elseif ( $this->history & WikiExporter::FULL ) {
				# Full history dumps...
				$join['revision'] = array( 'INNER JOIN', 'page_id=rev_page' );
			} elseif ( $this->history & WikiExporter::CURRENT ) {
				# Latest revision dumps...
				if ( $this->list_authors && $cond != '' ) { // List authors, if so desired
					$this->do_list_authors( $cond );
				}
				$join['revision'] = array( 'INNER JOIN', 'page_id=rev_page AND page_latest=rev_id' );
			} elseif ( $this->history & WikiExporter::STABLE ) {
				# "Stable" revision dumps...
				# Default JOIN, to be overridden...
				$join['revision'] = array( 'INNER JOIN', 'page_id=rev_page AND page_latest=rev_id' );
				# One, and only one hook should set this, and return false
				if ( Hooks::run( 'WikiExporter::dumpStableQuery', array( &$tables, &$opts, &$join ) ) ) {
					throw new MWException( __METHOD__ . " given invalid history dump type." );
				}
			} elseif ( $this->history & WikiExporter::RANGE ) {
				# Dump of revisions within a specified range
				$join['revision'] = array( 'INNER JOIN', 'page_id=rev_page' );
				$opts['ORDER BY'] = array( 'rev_page ASC', 'rev_id ASC' );
			} else {
				# Unknown history specification parameter?
				throw new MWException( __METHOD__ . " given invalid history dump type." );
			}
			# Query optimization hacks
			if ( $cond == '' ) {
				$opts[] = 'STRAIGHT_JOIN';
				$opts['USE INDEX']['page'] = 'PRIMARY';
			}
			# Build text join options
			if ( $this->text != WikiExporter::STUB ) { // 1-pass
				$tables[] = 'text';
				$join['text'] = array( 'INNER JOIN', 'rev_text_id=old_id' );
			}

			if ( $this->buffer == WikiExporter::STREAM ) {
				$prev = $this->db->bufferResults( false );
			}

			$result = null; // Assuring $result is not undefined, if exception occurs early
			try {
				Hooks::run( 'ModifyExportQuery',
					array( $this->db, &$tables, &$cond, &$opts, &$join ) );

				# Do the query!
				$result = $this->db->select( $tables, '*', $cond, __METHOD__, $opts, $join );
				# Output dump results
				$this->outputPageStream( $result );

				if ( $this->buffer == WikiExporter::STREAM ) {
					$this->db->bufferResults( $prev );
				}
			} catch ( Exception $e ) {
				// Throwing the exception does not reliably free the resultset, and
				// would also leave the connection in unbuffered mode.

				// Freeing result
				try {
					if ( $result ) {
						$result->free();
					}
				} catch ( Exception $e2 ) {
					// Already in panic mode -> ignoring $e2 as $e has
					// higher priority
				}

				// Putting database back in previous buffer mode
				try {
					if ( $this->buffer == WikiExporter::STREAM ) {
						$this->db->bufferResults( $prev );
					}
				} catch ( Exception $e2 ) {
					// Already in panic mode -> ignoring $e2 as $e has
					// higher priority
				}

				// Inform caller about problem
				throw $e;
			}
		}
	}
}

class FlowBackupDumper extends BackupDumper {
	function dump( $history, $text = FlowExporter::TEXT ) {
		# Notice messages will foul up your XML output even if they're
		# relatively harmless.
		if ( ini_get( 'display_errors' ) ) {
			ini_set( 'display_errors', 'stderr' );
		}

		$this->initProgress( $history );

		$db = $this->backupDb();
		$exporter = new FlowExporter( $db, $history, FlowExporter::STREAM, $text );
		$exporter->dumpUploads = $this->dumpUploads; // @todo
		$exporter->dumpUploadFileContents = $this->dumpUploadFileContents; // @todo

		$wrapper = new ExportProgressFilter( $this->sink, $this );
		$exporter->setOutputSink( $wrapper );

		if ( !$this->skipHeader ) {
			$exporter->openStream();
		}

		$revStartId = $this->revStartId ? UUID::create( $this->revStartId ) : null;
		$revEndId = $this->revEndId ? UUID::create( $this->revEndId ) : null;
		$exporter->dump( $this->pages, $this->startId, $this->endId, $revStartId, $revEndId );

		if ( !$this->skipFooter ) {
			$exporter->closeStream();
		}

		$this->report( true );
	}
}

$dumper = new FlowBackupDumper( $argv );

if ( isset( $options['quiet'] ) ) {
	$dumper->reporting = false;
}

if ( isset( $options['pagelist'] ) ) {
	$olddir = getcwd();
	chdir( $originalDir );
	$pages = file( $options['pagelist'] );
	chdir( $olddir );
	if ( $pages === false ) {
		echo "Unable to open file {$options['pagelist']}\n";
		die( 1 );
	}
	$pages = array_map( 'trim', $pages );
	$dumper->pages = array_filter( $pages, create_function( '$x', 'return $x !== "";' ) );
}

if ( isset( $options['start'] ) ) {
	$dumper->startId = intval( $options['start'] );
}
if ( isset( $options['end'] ) ) {
	$dumper->endId = intval( $options['end'] );
}

if ( isset( $options['revstart'] ) ) {
	$dumper->revStartId = intval( $options['revstart'] );
}
if ( isset( $options['revend'] ) ) {
	$dumper->revEndId = intval( $options['revend'] );
}
$dumper->skipHeader = isset( $options['skip-header'] );
$dumper->skipFooter = isset( $options['skip-footer'] );
$dumper->dumpUploads = isset( $options['uploads'] );
$dumper->dumpUploadFileContents = isset( $options['include-files'] );

$textMode = isset( $options['stub'] ) ? WikiExporter::STUB : WikiExporter::TEXT;

if ( isset( $options['full'] ) ) {
	$dumper->dump( WikiExporter::FULL, $textMode );
} elseif ( isset( $options['current'] ) ) {
	$dumper->dump( WikiExporter::CURRENT, $textMode );
} elseif ( isset( $options['revrange'] ) ) {
	$dumper->dump( WikiExporter::RANGE, $textMode );
} else {
	$dumper->progress( <<<ENDS
This script dumps the wiki page or logging database into an
XML interchange wrapper format for export or backup.

XML output is sent to stdout; progress reports are sent to stderr.

WARNING: this is not a full database dump! It is merely for public export
         of your wiki. For full backup, see our online help at:
         https://www.mediawiki.org/wiki/Backup

Usage: php dumpBackup.php <action> [<options>]
Actions:
  --full      Dump all revisions of every page.
  --current   Dump only the latest revision of every page.
  --pagelist=<file>
              Where <file> is a list of page titles to be dumped
  --revrange  Dump specified range of revisions, requires
              revstart and revend options.
Options:
  --quiet     Don't dump status reports to stderr.
  --report=n  Report position and speed after every n pages processed.
              (Default: 100)
  --server=h  Force reading from MySQL server h
  --start=n   Start from page_id or log_id n
  --end=n     Stop before page_id or log_id n (exclusive)
  --revstart=n  Start from rev_id n
  --revend=n    Stop before rev_id n (exclusive)
  --skip-header Don't output the <mediawiki> header
  --skip-footer Don't output the </mediawiki> footer
  --stub      Don't perform old_text lookups; for 2-pass dump
  --uploads   Include upload records without files
  --include-files Include files within the XML stream
  --conf=<file> Use the specified configuration file (LocalSettings.php)

  --wiki=<wiki>  Only back up the specified <wiki>

Fancy stuff: (Works? Add examples please.)
  --plugin=<class>[:<file>]   Load a dump plugin class
  --output=<type>:<file>      Begin a filtered output stream;
                              <type>s: file, gzip, bzip2, 7zip
  --filter=<type>[:<options>] Add a filter on an output branch

ENDS
	);
}
