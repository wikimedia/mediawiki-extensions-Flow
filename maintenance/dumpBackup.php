<?php

use Flow\Collection\PostSummaryCollection;
use Flow\Container;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Search\Iterators\AbstractIterator;
use Flow\Search\Iterators\HeaderIterator;
use Flow\Search\Iterators\TopicIterator;

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

	public function openStream() {
		global $wgLanguageCode;
		$version = static::schemaVersion();

		$output = Xml::openElement(
			'mediawiki',
			array(
				// @todo: update after creating schema
//				'xmlns'              => "http://www.mediawiki.org/xml/export-$version/",
//				'xmlns:xsi'          => "http://www.w3.org/2001/XMLSchema-instance",
//				'xsi:schemaLocation' => "http://www.mediawiki.org/xml/export-$version/ " .
//					"http://www.mediawiki.org/xml/export-$version.xsd",
				'version'            => $version,
				'xml:lang'           => $wgLanguageCode
			)
		);

		$this->sink->write( $output );
	}

	/**
	 * @param array|null $pages
	 * @param int|null $startId
	 * @param int|null $endId
	 * @return BatchRowIterator
	 */
	public function getWorkflowIterator( array $pages = null, $startId = null, $endId = null ) {
		global $wgFlowDefaultWorkflow;

		/** @var DatabaseBase $dbr */
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );

		$iterator = new BatchRowIterator( $dbr, 'flow_workflow', 'workflow_id', 300 );
		$iterator->setFetchColumns( array( '*' ) );
		$iterator->addConditions( array( 'workflow_wiki' => wfWikiId() ) );
		$iterator->addConditions( array( 'workflow_type' => $wgFlowDefaultWorkflow ) );

		if ( $pages ) {
			$iterator->addConditions( array( 'workflow_page_id' => $pages ) );
		}
		if ( $startId ) {
			$iterator->addConditions( array( 'workflow_page_id >= ' . $dbr->addQuotes( $startId ) ) );
		}
		if ( $endId ) {
			$iterator->addConditions( array( 'workflow_page_id <= ' . $dbr->addQuotes( $endId ) ) );
		}

		return $iterator;
	}

	/**
	 * @param BatchRowIterator $workflowIterator
	 * @param UUID|null $revStartId
	 * @param UUID|null $revEndId
	 * @throws Exception
	 * @throws TimestampException
	 * @throws \Flow\Exception\InvalidInputException
	 */
	public function dump( BatchRowIterator $workflowIterator, $revStartId = null, $revEndId = null ) {
		foreach ( $workflowIterator as $rows ) {
			foreach ( $rows as $row ) {
				$workflow = Workflow::fromStorageRow( (array) $row );

				$headerIterator = Container::get( 'search.index.iterators.header' );
				$topicIterator = Container::get( 'search.index.iterators.topic' );
				/** @var AbstractIterator $iterator */
				foreach ( array( $headerIterator, $topicIterator ) as $iterator ) {
					$iterator->setPage( $row->workflow_page_id );
					$iterator->setFrom( $revStartId );
					$iterator->setTo( $revEndId );
				}

				$output = $this->formatWorkflow( $workflow, $headerIterator, $topicIterator );
				$this->sink->write( $output );
			}
		}
	}

	protected function formatWorkflow( Workflow $workflow, HeaderIterator $headerIterator, TopicIterator $topicIterator ) {
		$output = Xml::openElement( 'board', array(
			'id' => $workflow->getId(),
			'title' => $workflow->getOwnerTitle()->getPrefixedDBkey(),
			// @todo: more?
		) ) . "\n";

		foreach ( $headerIterator as $revision ) {
			$output .= $this->formatHeader( $revision );
		}
		foreach ( $topicIterator as $revision ) {
			$output .= $this->formatTopic( $revision );
		}

		$output .= Xml::closeElement( 'board' );

		return $output . "\n";
	}

	protected function formatTopic( PostRevision $revision ) {
		$output = Xml::openElement( 'topic', array(
			'id' => $revision->getCollectionId(),
			'dumpVersion' => $this->schemaVersion(), // @todo: this must go elsewhere
			// @todo: more?
		) ) . "\n";

		// find summary for this topic & add it as revision
		$summaryCollection = PostSummaryCollection::newFromId( $revision->getCollectionId() );
		try {
			/** @var PostSummary $summary */
			$summary = $summaryCollection->getLastRevision();
			$output .= $this->formatSummary( $summary );
		} catch ( \Exception $e ) {
			// no summary - that's ok!
		}

		$output .= $this->formatPost( $revision );

		$output .= Xml::closeElement( 'topic' );

		return $output . "\n";
	}

	protected function formatHeader( Header $revision ) {
		$output = Xml::openElement(
			'description',
			array(
				'id' => $revision->getCollectionId(),
				'timestamp' => $revision->getCollectionId()->getTimestamp( TS_MW ), // @todo: collection id timestamp or revision id timestamp?
				'format' => $revision->isFormatted() ? 'html' : $revision->getContentFormat(),
				// @todo: more?
			)
		);
		$output .= '<![CDATA[' . $revision->getContent( 'html' ) . ']]>';
		$output .= Xml::closeElement( 'description' );

		return $output . "\n";
	}

	protected function formatPost( PostRevision $revision ) {
		$output = Xml::openElement(
			'post',
			array(
				'id' => $revision->getCollectionId(),
				'timestamp' => $revision->getCollectionId()->getTimestamp( TS_MW ), // @todo: collection id timestamp or revision id timestamp?
				'format' => $revision->isFormatted() ? 'html' : $revision->getContentFormat(),
				'user' => $revision->getUser()->getName(),
				'moderation_state' => $revision->getModerationState(),
				// @todo: more?
			)
		);

		$output .= '<![CDATA[' . $revision->getContent( 'html' ) . ']]>';

		if ( $revision->getChildren() ) {
			$output .= "\n";
			$output .= Xml::openElement( 'children' ) . "\n";
			foreach ( $revision->getChildren() as $child ) {
				$output .= $this->formatPost( $child );
			}
			$output .= Xml::closeElement( 'children' ) . "\n";
		}

		$output .= Xml::closeElement( 'post' );

		return $output . "\n";
	}

	protected function formatSummary( PostSummary $revision ) {
		$output = Xml::openElement(
			'summary',
			array(
				'id' => $revision->getCollectionId(),
				'timestamp' => $revision->getCollectionId()->getTimestamp( TS_MW ), // @todo: collection id timestamp or revision id timestamp?
				'format' => $revision->isFormatted() ? 'html' : $revision->getContentFormat(),
				// @todo: more?
			)
		);
		$output .= '<![CDATA[' . $revision->getContent( 'html' ) . ']]>';
		$output .= Xml::closeElement( 'summary' );

		return $output . "\n";
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
		$wrapper = new DumpOutput( $this->sink, $this );
		$exporter->setOutputSink( $wrapper );

		if ( !$this->skipHeader ) {
			$exporter->openStream();
		}

		$workflowIterator = $exporter->getWorkflowIterator( $this->pages, $this->startId, $this->endId );

		$revStartId = $this->revStartId ? UUID::create( $this->revStartId ) : null;
		$revEndId = $this->revEndId ? UUID::create( $this->revEndId ) : null;
		$exporter->dump( $workflowIterator, $revStartId, $revEndId );

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
