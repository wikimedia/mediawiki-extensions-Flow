<?php

namespace Flow\Formatter;

use DifferenceEngine;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\UrlGenerator;
use IContextSource;
use TextContent;

class RevisionViewFormatter {
	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var RevisionFormatter
	 */
	protected $serializer;

	/**
	 * @param UrlGenerator $urlGenerator
	 * @param RevisionFormatter $serializer
	 */
	public function __construct( UrlGenerator $urlGenerator, RevisionFormatter $serializer ) {
		$this->urlGenerator = $urlGenerator;
		$this->serializer = $serializer;
	}

	public function setContentFormat( $format, UUID $revisionId = null ) {
		$this->serializer->setContentFormat( $format, $revisionId );
	}

	/**
	 * @param FormatterRow $row
	 * @param IContextSource $ctx
	 * @return array
	 */
	public function formatApi( FormatterRow $row, IContextSource $ctx ) {
		$res = $this->serializer->formatApi( $row, $ctx );
		$res['rev_view_links'] = $this->buildLinks( $row );
		$res['human_timestamp'] = $this->getHumanTimestamp( $res['timestamp'] );
		if ( $row->revision instanceof PostRevision ) {
			$res['properties']['topic-of-post'] = $this->serializer->processParam(
				'topic-of-post',
				$row->revision,
				$row->workflow->getId(),
				$ctx
			);
		}
		if ( $row->revision instanceof PostSummary ) {
			$res['properties']['post-of-summary'] = $this->serializer->processParam(
				'post-of-summary',
				$row->revision,
				$row->workflow->getId(),
				$ctx
			);
		}
		return $res;
	}

	/**
	 * Generate the links for single and diff vie actions
	 * @param FormatterRow $row
	 * @return array
	 */
	public function buildLinks( FormatterRow $row ) {
		$workflowId = $row->workflow->getId();

		$boardTitle = $row->workflow->getOwnerTitle();
		$title = $row->workflow->getArticleTitle();
		$links = array(
			'hist' => $this->urlGenerator->boardHistoryLink( $title ),
			'board' => $this->urlGenerator->boardLink( $boardTitle ),
		);

		if ( $row->revision instanceof PostRevision || $row->revision instanceof PostSummary ) {
			$links['root'] = $this->urlGenerator->topicLink( $row->workflow->getArticleTitle(), $workflowId );
			$links['root']->setMessage( $title->getPrefixedText() );
		}

		if ( $row->revision instanceof PostRevision ) {
			$links['single-view'] = $this->urlGenerator->postRevisionLink(
				$title,
				$workflowId,
				$row->revision->getPostId(),
				$row->revision->getRevisionId()
			);
			$links['single-view']->setMessage( $title->getPrefixedText() );
		} elseif ( $row->revision instanceof Header ) {
			$links['single-view'] = $this->urlGenerator->headerRevisionLink(
				$title,
				$workflowId,
				$row->revision->getRevisionId()
			);
			$links['single-view']->setMessage( $title->getPrefixedText() );
		} elseif ( !$row->revision instanceof PostSummary ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Received unknown revision type ' . get_class( $row->revision ) );
		}

		if ( $row->revision->getPrevRevisionId() !== null ) {
			$links['diff'] = $this->urlGenerator->diffLink(
				$row->revision,
				null,
				$workflowId
			);
			$links['diff']->setMessage( wfMessage( 'diff' ) );
		} else {
			$links['diff'] = array(
				'url' => '',
				'title' => ''
			);
		}

		return $links;
	}

	public function getHumanTimestamp( $timestamp ) {
		$ts = new \MWTimestamp( $timestamp );
		return $ts->getHumanTimestamp();
	}

}

class RevisionDiffViewFormatter {

	protected $revisionViewFormatter;

	public function __construct( RevisionViewFormatter $revisionViewFormatter ) {
		$this->revisionViewFormatter = $revisionViewFormatter;
	}

	/**
	 * Diff would format against two revisions
	 */
	public function formatApi( FormatterRow $newRow, FormatterRow $oldRow, IContextSource $ctx ) {
		$oldRes = $this->revisionViewFormatter->formatApi( $oldRow, $ctx );
		$newRes = $this->revisionViewFormatter->formatApi( $newRow, $ctx );

		$oldContent = $oldRow->revision->getContent( 'wikitext' );
		$newContent = $newRow->revision->getContent( 'wikitext' );

		$differenceEngine = new DifferenceEngine();

		$differenceEngine->setContent(
			new TextContent( $oldContent ),
			new TextContent( $newContent )
		);

		return array( 'new' => $newRes, 'old' => $oldRes, 'diff_content' => $differenceEngine->getDiffBody() );
	}
}

class RevisionUndoViewFormatter {
	protected $revisionViewFormatter;

	public function __construct( RevisionViewFormatter $revisionViewFormatter ) {
		$this->revisionViewFormatter = $revisionViewFormatter;
	}

	/**
	 * Undoes the change that occured between $start and $stop
	 */
	public function formatApi(
		FormatterRow $start,
		FormatterRow $stop,
		FormatterRow $current,
		IContextSource $context
	) {
		$undoContent = $this->getUndoContent(
			$start->revision->getContent( 'wikitext' ),
			$stop->revision->getContent( 'wikitext' ),
			$current->revision->getContent( 'wikitext' )
		);

		$differenceEngine = new DifferenceEngine();
		$differenceEngine->setContent(
			new TextContent( $current->revision->getContent( 'wikitext' ) ),
			new TextContent( $undoContent )
		);

		$this->revisionViewFormatter->setContentFormat( 'wikitext' );

		// @todo if stop === current we could do a little less processing
		return array(
			'start' => $this->revisionViewFormatter->formatApi( $start, $context ),
			'stop' => $this->revisionViewFormatter->formatApi( $stop, $context ),
			'current' => $this->revisionViewFormatter->formatApi( $current, $context ),
			'undo' => array(
				'possible' => $undoContent !== false,
				'content' => $undoContent,
				'diff_content' => $differenceEngine->getDiffBody(),
			),
		);
	}

	protected function getUndoContent( $startContent, $stopContent, $currentContent ) {

		if ( $currentContent === $stopContent ) {
			return $startContent;
		} else {
			// 3-way merge
			$ok = wfMerge( $stopContent, $startContent, $currentContent, $result );
			if ( $ok ) {
				return $result;
			} else {
				return false;
			}
		}
	}
}
