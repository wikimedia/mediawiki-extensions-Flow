<?php
use Flow\Container;
use Flow\Dump\Exporter;
use Flow\Exception\DataModelException;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;

use Wikimedia\Rdbms\IMaintainableDatabase;

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance'
	: dirname( __FILE__ ) . '/../../../maintenance' );
require_once $maintPath . '/Maintenance.php';
require_once $maintPath . '/backup.inc';
require_once $maintPath . '/backupTextPass.inc';

class FlowTextPassDumper extends TextPassDumper {
	protected $firstBoardWritten = false;
	protected $lastBoardWritten = false;

	/**
	 * @param array $args For backward compatibility
	 */
	function __construct( $args = null ) {
		parent::__construct();

		$this->addDescription( <<<TEXT
This script postprocesses Flow XML dumps from dumpBackup.php to add
revision text which was stubbed out (using --stub).

XML input is accepted on stdin.
XML output is sent to stdout; progress reports are sent to stderr.
TEXT
		);
		$this->stderr = fopen( "php://stderr", "wt" );

		$this->addOption( 'stub', 'To load a compressed stub dump instead of stdin. ' .
			'Specify as --stub=<type>:<file>.', false, true );
		$this->addOption( 'prefetch', 'Use a prior dump file as a text source, to savepressure on the ' .
			'database. (Requires the XMLReader extension). Specify as --prefetch=<type>:<file>',
			false, true );
		$this->addOption( 'quiet', 'Don\'t dump status reports to stderr.' );
		$this->addOption( 'current', 'Base ETA on number of boards in database instead of all revisions' );
		$this->addOption( 'buffersize', 'Buffer size in bytes to use for reading the stub. ' .
			'(Default: 512KB, Minimum: 4KB)', false, true );

		if ( $args ) {
			$this->loadWithArgv( $args );
			$this->processOptions();
		}
	}


        function processOptions() {
                parent::processOptions();

                if ( $this->hasOption( 'prefetch' ) ) {
		        $IP = getenv( 'MW_INSTALL_PATH' );
			if ( $IP === false ) {
				$IP = __DIR__ . '/../../..';
			}
		        require_once "$IP/extensions/Flow/maintenance/backupPrefetch.inc";
                        $url = $this->processFileOpt( $this->getOption( 'prefetch' ) );
                        $this->prefetch = new FlowBaseDump( $url );
	        }

        }

	function dump( $history, $text = WikiExporter::TEXT ) {
		// Notice messages will foul up your XML output even if they're
		// relatively harmless.
		if ( ini_get( 'display_errors' ) ) {
			ini_set( 'display_errors', 'stderr' );
		}

		// We are trying to get an initial database connection to avoid that the
		// first try of this request's first call to getFlowText fails. However, if
		// obtaining a good DB connection fails it's not a serious issue, as
		// getFlowText does retry upon failure and can start without having a working
		// DB connection.
		try {
			$this->rotateDb();
		} catch ( Exception $e ) {
			// We do not even count this as failure. Just let eventual
			// watchdogs know.
			$this->progress( "Getting initial DB connection failed (" .
				$e->getMessage() . ")" );
		}

		$this->egress = $this->sink;
		$input = fopen( $this->input, "rt" );
		$this->readDump( $input );

	}

	/**
	 * @throws MWException Failure to parse XML input
	 * @param string $input
	 * @return bool
	 */
	function readDump( $input ) {
		$this->thisBoard = 0;
		return parent::readDump( $input );
	}

	function getRevById( $revId ) {
		$dbFactory = Container::get( 'db.factory' );
		$dbr = $dbFactory->getDB( DB_SLAVE );
		$uuid = UUID::create( $revId );
		$row = $dbr->selectRow('flow_revision', '*', [ 'rev_id' => $uuid->getBinary() ], __METHOD__);
		if ( $row ) {
		        return( $row );
		}
                else {
			throw new DataModelException( __METHOD__ . ': Query failed: ' . $dbr->lastError(), 'process-data' );
		}
	}


	/**
	 * Tries to get the revision text for a revision id (UUID).
	 *
	 * Upon errors, retries (Up to $this->maxFailures tries each call).
	 * If still no good revision text could be found even after this retrying, "" is returned.
	 * If no good revision text could be returned for
	 * $this->maxConsecutiveFailedTextRetrievals consecutive calls to getFlowText, MWException
	 * is thrown.
	 *
	 * @param string $id The revision id to get the text for
	 * @param array $attrs The attributes of the revision as retrieved from xlm element
	 *
	 * @throws MWException
	 * @return string The revision text for $id, or ""
	 */
	function getFlowText( $id, $attrs ) {
		global $wgContentHandlerUseDB;
                global $wgContLang;

		$prefetchNotTried = true; // Whether or not we already tried to get the text via prefetch.
		$text = false; // The candidate for a good text. false if no proper value.
		$failures = 0; // The number of times, this invocation of getFlowText already failed.

		// The number of times getFlowText failed without yielding a good text in between.
		static $consecutiveFailedTextRetrievals = 0;

		$this->fetchCount++;

		// To allow to simply return on success and do not have to worry about book keeping,
		// we assume, this fetch works (possible after some retries). Nevertheless, we koop
		// the old value, so we can restore it, if problems occur (See after the while loop).
		$oldConsecutiveFailedTextRetrievals = $consecutiveFailedTextRetrievals;
		$consecutiveFailedTextRetrievals = 0;

		$row = $this->getRevById( $id );
		if ( $row->rev_type == 'post' ) {
			if ( !isset( $attrs['treerevid'] ) ) {
				return "";
			}
			$row->tree_rev_id = $attrs['treerevid'];
			if ( !isset( $attrs['treeparentid'] ) ) {
				$row->tree_parent_id = null;
			} else {
				$row->tree_parent_id = $attrs['treeparentid'];
			}
			if ( isset( $attrs['treeoriguserid'] ) ) {
				$row->tree_orig_user_id = $attrs['treeoriguserid'];
			} else {
				$row->tree_orig_user_id = null;
			}
			if ( isset( $attrs['treeoriguserip'] ) ) {
				$row->tree_orig_user_ip = $attrs['treeoriguserip'];
			} else {
				$row->tree_orig_user_ip = null;
			}
			if ( isset( $attrs['treeoriguserwiki'] ) ) {
				$row->tree_orig_user_wiki = $attrs['treeoriguserwiki'];
			} else {
				$row->tree_orig_user_wiki = null;
			}
			$revision = PostRevision::fromStorageRow( (array)$row );
		} elseif ( $row->rev_type = 'header' ) {
			$revision = Header::fromStorageRow( (array)$row );
		} elseif ( $row->rev_type = 'post-summary' ) {
			$revision = PostSummary::fromStorageRow( (array)$row );
		}
		else {
			// don't know how to convert it and get content, give up
			return "";
		}
                $format = $revision->getContentFormat();

		while ( $failures < $this->maxFailures ) {

			// As soon as we found a good text for the $id, we will return immediately.
			// Hence, if we make it past the try catch block, we know that we did not
			// find a good text.

			try {
                                // Utterly untested, FIXME
				// Trying to get prefetch, if it has not been tried before
				if ( $text === false && isset( $this->prefetch ) && $prefetchNotTried ) {
					$prefetchNotTried = false;
					$tryIsPrefetch = true;
					$boardId = UUID::create( $this->thisBoard );
					$revId = UUID::create( $id );
					$text = $this->prefetch->prefetch( $boardId->getHex(),
						$revId->getHex() );
					if ( $text === null ) {
						$text = false;
					}
				}

				if ( $text === false ) {
					// Fallback to asking the database
					$tryIsPrefetch = false;
					$text = $revision->getContent( $format );
					if ( $text !== false ) {
						return $text;
					}
				}

				if ( $text === false ) {
					throw new MWException( "Generic error while obtaining text for id " . $id );
				}

				if ( $tryIsPrefetch ) {
					$this->prefetchCount++;
				}
				return $text;
			} catch ( Exception $e ) {
				$msg = "getting/checking text " . $id . " failed (" . $e->getMessage() . ")";
				if ( $failures + 1 < $this->maxFailures ) {
					$msg .= " (Will retry " . ( $this->maxFailures - $failures - 1 ) . " more times)";
				}
				$this->progress( $msg );
			}

			// Something went wrong; we did not get a text that was plausible :(
			$failures++;

			// A failure in a prefetch hit does not warrant resetting db connection etc.
			if ( !$tryIsPrefetch ) {
				// After backing off for some time, we try to reboot the whole process as
				// much as possible to not carry over failures from one part to the other
				// parts
				sleep( $this->failureTimeout );
				try {
					$this->rotateDb();
				} catch ( Exception $e ) {
					$this->progress( "Rebooting getFlowText infrastructure failed (" . $e->getMessage() . ")" .
						" Trying to continue anyways" );
				}
			}
		}

		// Retrieving a good text for $id failed (at least) maxFailures times.
		// We abort for this $id.

		// Restoring the consecutive failures, and maybe aborting, if the dump
		// is too broken.
		$consecutiveFailedTextRetrievals = $oldConsecutiveFailedTextRetrievals + 1;
		if ( $consecutiveFailedTextRetrievals > $this->maxConsecutiveFailedTextRetrievals ) {
			throw new MWException( "Graceful storage failure" );
		}

		return "";
	}

        function writeOpenBoard() {
                // horrible but avoids adding Flow-specific methods to DumpOutput in core
		$this->sink->writeOpenPage( null, $this->buffer );
        }

        function writeCloseBoard() {
                // horrible but avoids adding Flow-specific methods to DumpOutput in core
		$this->sink->writeclosePage( $this->buffer );
        }

	function startElement( $parser, $name, $attribs ) {

		if ( $name == 'revision' ) {
		        $this->clearOpenElement( null );
		        $this->lastName = $name;
			$this->state = $name;
			$this->writeOpenBoard( null, $this->buffer );
			$this->buffer = "";
                        if ( isset( $attribs['id'] ) ) {
                                $id = $attribs['id'];
                                $text = $this->getFlowText( $id, $attribs );
                                $this->openElement = [ $name, $attribs ];
                                if ( strlen( $text ) > 0 ) {
				        # FIXME this needs conversion in the routine or after
                                        $this->characterData( $parser, $text );
                                }
                        }
		} elseif ( $name == 'board' ) {
		        $this->clearOpenElement( null );
		        $this->lastName = $name;
			$this->state = $name;
                        if ( isset( $attribs['id'] ) ) {
				$this->thisBoard = $attribs['id'];
			}
			if ( $this->atStart ) {
                                $this->sink->writeOpenStream( $this->buffer );
				$this->buffer = "";
				$this->atStart = false;
			}
                        $this->openElement = [ $name, $attribs ];
		} else {
                        parent::startElement( $parser, $name, $attribs );
                }
	}

	function endElement( $parser, $name ) {
		if ( $name == 'board' ) {
		        if ( $this->openElement ) {
			        $this->clearOpenElement( "" );
		        } else {
			        $this->buffer .= "</$name>";
		        }
			if ( !$this->firstBoardWritten ) {
				$this->firstBoardWritten = trim( $this->thisBoard );
			}
			$this->lastBoardWritten = trim( $this->thisBoard );
                        $this->writeCloseBoard( $this->buffer );
                        $this->buffer = "";
                        $this->thisPage = "";
                }  else {
                        parent::endElement( $parser, $name );
                }
	}

}

$maintClass = 'FlowTextPassDumper';
require_once RUN_MAINTENANCE_IF_MAIN;
