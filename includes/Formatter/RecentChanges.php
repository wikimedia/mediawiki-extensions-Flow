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

		$data = $this->serializer->formatApi( $row, $ctx );
		// @todo where should this go?
		$data['size'] = array(
			'old' => $row->recentChange->getAttribute( 'rc_old_len' ),
			'new' => $row->recentChange->getAttribute( 'rc_new_len' ),
		);

		$links = array();
		if ( isset( $data['links']['diff'] ) ) {
			$links[] = $data['links']['diff'];
		} else {
			// plain text with no link
			$links[] = wfMessage( 'diff' );
		}

		if ( isset( $data['links']['post-history'] ) ) {
			$links[] = $data['links']['post-history'];
		} elseif ( isset( $data['links']['topic-history'] ) ) {
			$links[] = $data['links']['topic-history'];
		} elseif ( isset( $data['links']['board-history'] ) ) {
			$links[] = $data['links']['board-history'];
		} else {
			// plain text with no link
			$links[] = wfMessage( 'hist' );
		}


		// The ' . . ' text between elements
		$separator = $this->changeSeparator();

		return $this->formatLinksAsPipeList( $links, $ctx ) .
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
