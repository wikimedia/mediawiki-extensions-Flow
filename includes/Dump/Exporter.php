<?php

namespace Flow\Dump;

use BatchRowIterator;
use DatabaseBase;
use Exception;
use Flow\Collection\PostSummaryCollection;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\RevisionActionPermissions;
use Flow\Search\Iterators\AbstractIterator;
use Flow\Search\Iterators\HeaderIterator;
use Flow\Search\Iterators\TopicIterator;
use TimestampException;
use User;
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
	 * @param string[]|null $pages Array of DB-prefixed page titles
	 * @param int|null $startId page_id to start from (inclusive)
	 * @param int|null $endId page_id to end (exclusive)
	 * @return BatchRowIterator
	 */
	public function getWorkflowIterator( array $pages = null, $startId = null, $endId = null ) {
		/** @var DatabaseBase $dbr */
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );

		$iterator = new BatchRowIterator( $dbr, 'flow_workflow', 'workflow_id', 300 );
		$iterator->setFetchColumns( array( '*' ) );
		$iterator->addConditions( array( 'workflow_wiki' => wfWikiId() ) );
		$iterator->addConditions( array( 'workflow_type' => 'discussion' ) );

		if ( $pages ) {
			$pageConds = array();
			foreach ( $pages as $page ) {
				$title = \Title::newFromDBkey( $page );
				$pageConds = $dbr->makeList(
					array(
						'page_namespace' => $title->getNamespace(),
						'page_title' => $title->getDBkey()
					),
					LIST_AND
				);
			}

			$iterator->addConditions( array( $dbr->makeList( $pageConds, LIST_OR ) ) );
		}
		if ( $startId ) {
			$iterator->addConditions( array( 'workflow_page_id >= ' . $dbr->addQuotes( $startId ) ) );
		}
		if ( $endId ) {
			$iterator->addConditions( array( 'workflow_page_id < ' . $dbr->addQuotes( $endId ) ) );
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
			/** @var Header $revision */
			$this->formatHeader( $revision );
		}
		foreach ( $topicIterator as $revision ) {
			/** @var PostRevision $revision */
			$this->formatTopic( $revision );
		}

		$output = Xml::closeElement( 'board' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatTopic( PostRevision $revision ) {
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

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
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

		$output = Xml::openElement( 'description', array(
			'id' => $revision->getCollectionId()->getAlphadecimal()
		) ) . "\n";
		$this->sink->write( $output );

		$this->formatRevisions( $revision );

		$output = Xml::closeElement( 'description' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatPost( PostRevision $revision ) {
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

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
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

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

		$collection = $revision->getCollection();
		if ( $this->history === WikiExporter::FULL ) {
			$revisions = array_reverse( $collection->getAllRevisions() );
			foreach ( $revisions as $revision ) {
				$this->formatRevision( $revision );
			}
		} elseif ( $this->history === WikiExporter::CURRENT ) {
			$first = $collection->getFirstRevision();

			// storing only last revision won't work (it'll reference non-existing
			// parents): we'll construct a bogus revision with most of the original
			// metadata, but with the current content & id (= timestamp)
			$first = $first->toStorageRow( $first );
			$last = $revision->toStorageRow( $revision );
			$first['rev_id'] = $last['rev_id'];
			$first['rev_content'] = $last['rev_content'];
			$first['rev_flags'] = $last['rev_flags'];
			if ( isset( $first['tree_rev_id'] ) ) {
				// PostRevision-only: tree_rev_id must match rev_id
				$first['tree_rev_id'] = $first['rev_id'];
			}

			// clear buffered cache, to make sure it doesn't serve the existing (already
			// loaded) revision when trying to turn our bogus mixed data into a revision
			/** @var ManagerGroup $storage */
			$storage = Container::get( 'storage' );
			$storage->clear();

			$mix = $revision->fromStorageRow( $first );

			$this->formatRevision( $mix );
		}

		$output = Xml::closeElement( 'revisions' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatRevision( AbstractRevision $revision ) {
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

		// @todo: strip rev_ prefix? (and re-apply in import)
		// @todo: what about tree_rev_ prefixes?
		// @todo: stop using isFormatted() after https://gerrit.wikimedia.org/r/#/c/243066/

		$attribs = $revision->toStorageRow( $revision );

		// references to external store etc. are useless; we'll include the real content
		// (in html format, if possible) as CDATA
		unset( $attribs['rev_content'], $attribs['rev_content_url'] );
		$attribs['rev_flags'] = 'utf-8,' . ( $revision->isFormatted() ? $revision->getContentFormat() : 'wikitext' );

		$content = $revision->getContent( 'html' );
		// if ]]> occurs in content, it should be split inside different CDATA
		// nodes to prevent the original CDATA from being closed
		$content = str_replace(']]>', ']]]]><![CDATA[>', $content );

		$output = Xml::openElement( 'revision', $attribs );
		$output .= '<![CDATA[' . $content . ']]>';
		$output .= Xml::closeElement( 'revision' ) . "\n";
		$this->sink->write( $output );
	}

	/**
	 * Test if anon users are allowed to view a particular revision.
	 *
	 * @param AbstractRevision $revision
	 * @return bool
	 */
	protected function isAllowed( AbstractRevision $revision ) {
		$user = User::newFromId( 0 );
		$actions = Container::get( 'flow_actions' );
		$permissions = new RevisionActionPermissions( $actions, $user );

		return $permissions->isAllowed( $revision, 'view' );
	}
}
