<?php

namespace Flow\Formatter;

use Flow\Model\PostRevision;
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
			( Utils::htmlToPlaintext( $description ) ? $separator . $description : '' );
	}
}
