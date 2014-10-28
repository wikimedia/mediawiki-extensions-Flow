<?php

namespace Flow\Formatter;

use ChangesList;
use Flow\Model\PostRevision;
use Flow\Parsoid\Utils;
use IContextSource;
use Linker;

class RecentChanges extends AbstractFormatter {
	protected function getHistoryType() {
		return 'recentchanges';
	}

	/**
	 * @param RecentChangesRow $row
	 * @param IContextSource $ctx
	 * @param bool $linkOnly
	 * @return string|false Output line, or false on failure
	 */
	public function format( RecentChangesRow $row, IContextSource $ctx, $linkOnly = false ) {
		if ( !$this->permissions->isAllowed( $row->revision, 'recentchanges' ) ) {
			return false;
		}
		if ( $row->revision instanceof PostRevision &&
			!$this->permissions->isAllowed( $row->rootPost, 'recentchanges' ) ) {
			return false;
		}

		$this->serializer->setIncludeHistoryProperties( true );
		$data = $this->serializer->formatApi( $row, $ctx );
		if ( !$data ) {
			throw new FlowException( 'Could not format data for row ' . $row->revision->getRevisionId()->getAlphadecimal() );
		}

		// @todo where should this go?
		$data['size'] = array(
			'old' => $row->recentChange->getAttribute( 'rc_old_len' ),
			'new' => $row->recentChange->getAttribute( 'rc_new_len' ),
		);

		if ( $linkOnly ) {
			return $this->getTitleLink( $data, $row, $ctx );
		}

		// The ' . . ' text between elements
		$separator = $this->changeSeparator();

		$links = array();
		$links[] = $this->getDiffAnchor( $data['links'], $ctx );
		$links[] = $this->getHistAnchor( $data['links'], $ctx );

		$description = $this->formatDescription( $data, $ctx );

		return $this->formatAnchorsAsPipeList( $links, $ctx ) .
			' ' .
			$this->getTitleLink( $data, $row, $ctx ) .
			$ctx->msg( 'semicolon-separator' )->escaped() .
			' ' .
			$this->formatTimestamp( $data, 'time' ) .
			$separator .
			ChangesList::showCharacterDifference(
			  $data['size']['old'],
			  $data['size']['new'],
			  $ctx
			) .
			( Utils::htmlToPlaintext( $description ) ? $separator . $description : '' ) .
			$this->getEditSummary( $row, $ctx, $data );
	}

	/**
	 * @param RecentChangesRow $row
	 * @param IContextSource $ctx
	 * @param array $data
	 * @return string
	 */
	public function getEditSummary( RecentChangesRow $row, IContextSource $ctx, array $data ) {
		// Build description message, piggybacking on history i18n
		$changeType = $data['changeType'];
		$actions = $this->permissions->getActions();

		$key = $actions->getValue( $changeType, 'history', 'i18n-message' );
		// Find specialized message for summary
		// i18n messages: flow-rev-message-new-post-recentchanges-summary,
		// flow-rev-message-edit-post-recentchanges-summary
		$msg = $ctx->msg( $key . '-' . $this->getHistoryType() . '-summary' );
		if ( !$msg->exists() ) {
			// No summary for this action
			return '';
		}

		$msg = $msg->params( $this->getDescriptionParams( $data, $actions, $changeType ) );

		// Below code is inspired by Linker::formatAutocomments
		$prefix = $ctx->msg( 'autocomment-prefix' )->inContentLanguage()->escaped();
		$link = Linker::link(
			$title = $row->workflow->getOwnerTitle(),
			$ctx->getLanguage()->getArrow('backwards'),
			array(),
			array(),
			'noclasses'
		);
		$summary = '<span class="autocomment">' . $msg->text() . '</span>';

		// '(' + '' + '←' + summary + ':' + user content + ')'
		$content = $this->getEditSummaryContent( $row, $ctx, $data );
		$text = Linker::commentBlock( $prefix . $link . $summary . $content );

		// Linker::commentBlock escaped everything, but what we built was safe
		// and should not be escaped so let's go back to decoded entities
		return htmlspecialchars_decode( $text );
	}

	/**
	 * @param RecentChangesRow $row
	 * @param IContextSource $ctx
	 * @param array $data
	 * @return string
	 */
	protected function getEditSummaryContent( RecentChangesRow $row, IContextSource $ctx, array $data ) {
		$changeType = $data['changeType'];
		if ( $changeType !== 'new-post' ) {
			return '';
		}

		// For a new topic, we're supposed to show a truncated excerpt of the
		// first reply (I guess only when the topic was posted with an initial
		// post - could also be submitted without...)
		// I'm not sure how to best first this first reply's content; all of the
		// options I can think of suck.
		// Best idea would be to let RecentChangesQuery::loadMetadataBatch also
		// fetch first child for all new-topic revisions, and let ::getResult
		// add it to $row, similar to $row->previousRevision & ->nextRevision.
		// The reasons I hate that solution is:
		// * it's inconsistent with other Formatter thingies
		// * it's inconsistent with other actions
		// * frankly, this RC formatter is hideous enough already
		// Anyone any better ideas?

		$content = ''; // @todo: see comment above
		$content = strip_tags( $content );
		$content = $ctx->getLanguage()->truncate( $content, 200 );

		return $ctx->msg( 'colon-separator' )->inContentLanguage()->escaped() . $content;
	}
}
