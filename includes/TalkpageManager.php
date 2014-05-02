<?php

namespace Flow;

use Flow\Content\BoardContent;
use Flow\Exception\InvalidInputException;
use Flow\Data\Workflow;
use Article;
use ContentHandler;
use Revision;
use Title;

// I got the feeling NinetyNinePercentController was a bit much.
interface OccupationController {
	public function isTalkpageOccupied( $title );
	public function ensureFlowRevision( Article $title, Workflow $workflow );
}

class TalkpageManager implements OccupationController {

	protected $occupiedPages;

	/**
	 * @param array $occupiedNamespaces See documentation for $wgFlowOccupyNamespaces
	 * @param array $occupiedPages See documentation for $wgFlowOccupyPages
	 */
	public function __construct( array $occupiedNamespaces, array $occupiedPages ) {
		$this->occupiedNamespaces = $occupiedNamespaces;
		$this->occupiedPages = $occupiedPages;
	}

	/**
	 * Determines whether or not a talk page is "occupied" by Flow.
	 *
	 * Internally, determines whether or not 1% of the talk page contains
	 * 99% of the discussions.
	 * @param  Title  $title Title object to check for occupation status
	 * @return boolean True if the talk page is occupied, False otherwise.
	 */
	public function isTalkpageOccupied( $title ) {
		if ( !$title || !is_object( $title ) ) {
			// Invalid parameter
			return false;
		}

		return in_array( $title->getPrefixedText(), $this->occupiedPages )
			|| ( in_array( $title->getNamespace(), $this->occupiedNamespaces )
				&& !$title->isSubpage() );
	}

	/**
	 * When a page is taken over by Flow, add a revision.
	 * First, it provides a clearer history should Flow be disabled again later,
	 * and a descriptive message when people attempt to use regular API to fetch
	 * data for this "Page", which will no longer contain any useful content,
	 * since Flow has taken over.
	 * Also: Parsoid performs an API call to fetch page information, so we need
	 * to make sure a page actually exists ;)
	 *
	 * @param \Article $article
	 * @param Flow\Data\Workflow $workflow
	 * @throws InvalidInputException
	 */
	public function ensureFlowRevision( Article $article, Workflow $workflow ) {
		$title = $article->getTitle();
		if ( !$this->isTalkpageOccupied( $title ) ) {
			throw new InvalidInputException( 'Requested article is not Flow enabled', 'invalid-input' );
		}

		// comment to add to the Revision to indicate Flow taking over
		$comment = '/* Taken over by Flow */';

		$page = $article->getPage();
		$revision = $page->getRevision();

		// make sure a Flow revision has not yet been inserted
		if ( $revision === null || $revision->getComment( Revision::RAW ) != $comment ) {
			$content = new BoardContent( 'flow-board', $workflow );
			$page->doEditContent( $content, $comment, EDIT_FORCE_BOT | EDIT_SUPPRESS_RC );
		}
	}
}
