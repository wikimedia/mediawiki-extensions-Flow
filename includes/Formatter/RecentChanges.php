<?php

namespace Flow\Formatter;

use Flow\Model\PostRevision;
use ChangesList;
use IContextSource;
use Linker;

class RecentChanges extends AbstractFormatter {

	/**
	 * @param RecentChangesRow $row
	 * @param IContextSource $ctx
	 * @return string|bool Output line, or false on failure
	 */
	public function format( RecentChangesRow $row, IContextSource $ctx ) {
		if ( !$this->permissions->isAllowed( $row->revision, 'recentchanges' ) ) {
			return false;
		}
		if ( $row->revision instanceof PostRevision &&
			!$this->permissions->isAllowed( $row->rootPost, 'recentchanges' ) ) {
			return false;
		}

		$this->serializer->setIncludeHistoryProperties( true );
		$data = $this->serializer->formatApi( $row, $ctx );
		// @todo where should this go?
		$data['size'] = array(
			'old' => $row->recentChange->getAttribute( 'rc_old_len' ),
			'new' => $row->recentChange->getAttribute( 'rc_new_len' ),
		);

		// The ' . . ' text between elements
		$separator = $this->changeSeparator();

		$links = array();
		$links[] = $this->getDiffAnchor( $data['links'], $ctx );
		$links[] = $this->getHistAnchor( $data['links'], $ctx );

		return $this->formatAnchorsAsPipeList( $links, $ctx ) .
			' ' .
			Linker::link( $row->workflow->getArticleTitle() ) .
			$ctx->msg( 'semicolon-separator' )->escaped() .
			' ' .
			$this->formatTimestamp( $data, 'time' ) .
			$separator .
			ChangesList::showCharacterDifference(
			  $data['size']['old'],
			  $data['size']['new'],
			  $ctx
			) .
			$separator .
			$this->formatDescription( $data, $ctx );
	}
}
