<?php

namespace Flow\Dump;

use BatchRowIterator;
use DatabaseBase;
use Exception;
use Flow\Collection\PostSummaryCollection;
use Flow\Container;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Search\Iterators\AbstractIterator;
use Flow\Search\Iterators\HeaderIterator;
use Flow\Search\Iterators\TopicIterator;
use TimestampException;
use WikiExporter;
use Xml;

class Exporter extends WikiExporter {
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
//				'xmlns'  => "http://www.mediawiki.org/xml/export-$version/",
//				'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
//				'xsi:schemaLocation' => "http://www.mediawiki.org/xml/export-$version/ http://www.mediawiki.org/xml/export-$version.xsd",
				'version' => $version,
				'xml:lang' => $wgLanguageCode
			)
		) . "\n";
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

				$this->formatWorkflow( $workflow, $headerIterator, $topicIterator );
			}
		}
	}

	protected function formatWorkflow( Workflow $workflow, HeaderIterator $headerIterator, TopicIterator $topicIterator ) {
		$output = Xml::openElement( 'board', array(
			'id' => $workflow->getId(),
			'title' => $workflow->getOwnerTitle()->getPrefixedDBkey(),
		) ) . "\n";
		$this->sink->write( $output );

		foreach ( $headerIterator as $revision ) {
			$this->formatHeader( $revision );
		}
		foreach ( $topicIterator as $revision ) {
			$this->formatTopic( $revision );
		}

		$output = Xml::closeElement( 'board' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatTopic( PostRevision $revision ) {
		$output = Xml::openElement( 'topic', array(
			'id' => $revision->getCollectionId()->getAlphadecimal(),
		) ) . "\n";
		$this->sink->write( $output );

		// find summary for this topic & add it as revision
		$summaryCollection = PostSummaryCollection::newFromId( $revision->getCollectionId() );
		try {
			/** @var PostSummary $summary */
			$summary = $summaryCollection->getLastRevision();
			$this->formatSummary( $summary );
		} catch ( \Exception $e ) {
			// no summary - that's ok!
		}

		$this->formatPost( $revision );

		$output = Xml::closeElement( 'topic' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatHeader( Header $revision ) {
		$output = Xml::openElement( 'description', array(
			'id' => $revision->getCollectionId()->getAlphadecimal()
		) ) . "\n";
		$this->sink->write( $output );

		$this->formatRevisions( $revision );

		$output = Xml::closeElement( 'description' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatPost( PostRevision $revision ) {
		$output = Xml::openElement( 'post', array(
			'id' => $revision->getCollectionId()->getAlphadecimal()
		) ) . "\n";
		$this->sink->write( $output );

		$this->formatRevisions( $revision );

		if ( $revision->getChildren() ) {
			$output = Xml::openElement( 'children' ) . "\n";
			$this->sink->write( $output );

			foreach ( $revision->getChildren() as $child ) {
				$this->formatPost( $child );
			}

			$output = Xml::closeElement( 'children' ) . "\n";
			$this->sink->write( $output );
		}

		$output = Xml::closeElement( 'post' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatSummary( PostSummary $revision ) {
		$output = Xml::openElement( 'summary', array(
			'id' => $revision->getCollectionId()->getAlphadecimal()
		) ) . "\n";
		$this->sink->write( $output );

		$this->formatRevisions( $revision );

		$output = Xml::closeElement( 'summary' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatRevisions( AbstractRevision $revision ) {
		$output = Xml::openElement( 'revisions' ) . "\n";
		$this->sink->write( $output );

		if ( $this->history === WikiExporter::FULL ) {
			$collection = $revision->getCollection();

			$revisions = array_reverse( $collection->getAllRevisions() );
			foreach ( $revisions as $revision ) {
				$this->formatRevision( $revision );
			}
		} elseif ( $this->history === WikiExporter::CURRENT ) {
			$this->formatRevision( $revision );
		}

		$output = Xml::closeElement( 'revisions' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatRevision( AbstractRevision $revision ) {
		$attribs = $revision->toStorageRow( $revision );

		// references to external store etc. are useless; we'll include the real content
		// (in html format, if possible) as CDATA
		unset( $attribs['rev_content'], $attribs['rev_content_url'] );
		$attribs['rev_flags'] = 'utf-8,' . ( $revision->isFormatted() ? $revision->getContentFormat() : 'wikitext' );

		// @todo: user ids are probably useless?

		$output = Xml::openElement( 'revision', $attribs );
		$output .= '<![CDATA[' . $revision->getContent( 'html' ) . ']]>';
		$output .= Xml::closeElement( 'revision' ) . "\n";
		$this->sink->write( $output );
	}
}
