<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\Anchor;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use ChangesList;
use IContextSource;

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
			( Utils::htmlToPlaintext( $description ) ? $separator . $description : '' );
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
}
