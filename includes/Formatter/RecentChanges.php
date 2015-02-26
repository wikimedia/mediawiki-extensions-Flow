<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\Anchor;
use ChangesList;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
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
	 * @throws FlowException
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
		$this->serializer->setIncludeContent( false );

		$data = $this->serializer->formatApi( $row, $ctx );
		if ( !$data ) {
			throw new FlowException( 'Could not format data for row ' . $row->revision->getRevisionId()->getAlphadecimal() );
		}

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
			$separator .
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

		// '(' + '' + '←' + summary + ')'
		$text = Linker::commentBlock( $prefix . $link . $summary );

		// Linker::commentBlock escaped everything, but what we built was safe
		// and should not be escaped so let's go back to decoded entities
		return htmlspecialchars_decode( $text );
	}

	/**
	 * This overrides the default title link to include highlights for the posts
	 * that have not yet been seen.
	 *
	 * @param array $data
	 * @param FormatterRow $row
	 * @param IContextSource $ctx
	 * @return string
	 */
	protected function getTitleLink( array $data, FormatterRow $row, IContextSource $ctx ) {
		if ( !$row instanceof RecentChangesRow ) {
			// actually, this should be typehint, but can't because this needs
			// to match the parent's more generic typehint
			return parent::getTitleLink( $data, $row, $ctx );
		}

		if ( !isset( $data['links']['topic'] ) || !$data['links']['topic'] instanceof Anchor ) {
			// no valid title anchor (probably header entry)
			return parent::getTitleLink( $data, $row, $ctx );
		}

		$watched = $row->recentChange->getAttribute( 'wl_notificationtimestamp' );
		if ( is_bool( $watched ) ) {
			// RC & watchlist share most code; the latter is unaware of when
			// something was watched though, so we'll ignore that here
			return parent::getTitleLink( $data, $row, $ctx );
		}

		if ( $watched === null ) {
			// there is no data for unread posts - they've all been seen
			return parent::getTitleLink( $data, $row, $ctx );
		}

		// get comparison UUID corresponding to this last watched timestamp
		$uuid = UUID::getComparisonUUID( $watched );

		// add highlight details to anchor
		/** @var Anchor $anchor */
		$anchor = clone $data['links']['topic'];
		$anchor->query['fromnotif'] = '1';
		$anchor->fragment = '#flow-post-' . $uuid->getAlphadecimal();
		$data['links']['topic'] = $anchor;

		// now pass it on to parent with the new, updated, link ;)
		return parent::getTitleLink( $data, $row, $ctx );
	}

	/**
	 * @param RecentChangesRow $row
	 * @param IContextSource $ctx
	 * @param array $block
	 * @param array $links
	 * @return array
	 * @throws FlowException
	 * @throws \Flow\Exception\InvalidInputException
	 */
	public function getLogTextLinks( RecentChangesRow $row, IContextSource $ctx, array $block, array $links = array() ) {
		$old = unserialize( $block[count( $block ) - 1]->mAttribs['rc_params'] );
		$oldId = $old ? UUID::create( $old['flow-workflow-change']['revision'] ) : $row->revision->getRevisionId();

		$data = $this->serializer->formatApi( $row, $ctx );
		if ( !$data ) {
			throw new FlowException( 'Could not format data for row ' . $row->revision->getRevisionId()->getAlphadecimal() );
		}

		// add highlight details to anchor
		/** @var Anchor $anchor */
		$anchor = clone $data['links']['topic'];
		$anchor->query['fromnotif'] = '1';
		$anchor->fragment = '#flow-post-' . $oldId->getAlphadecimal();

		$changes = count($block);
		// link text: "n changes"
		$text = $ctx->msg( 'nchanges' )->numParams( $changes )->escaped();

		// override total changes link
		$links['total-changes'] = $anchor->toHtml( $text );

		return $links;
	}
}
