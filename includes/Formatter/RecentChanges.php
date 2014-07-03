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

		$hack = $this->tempHackyChanges( $row, $ctx, $data, $links, $separator );
		if ( $hack ) {
			return $hack;
		}

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

	/**
	 * Really hacky temporary implementation to change the output of just a
	 * couple of actions - the rest is still to be figured out.
	 *
	 * @see https://trello.com/c/NJ93TAzJ/174-c-5-log-item-improvements-for-watchlist-1
	 *
	 * @param RecentChangesRow $row
	 * @param IContextSource $ctx
	 * @param array $data
	 * @param $links
	 * @param $separator
	 * @return bool|string
	 *
	 * @deprecated Seriously, we shouldn't even have this in the first place ;)
	 */
	protected function tempHackyChanges( RecentChangesRow $row, IContextSource $ctx, array $data, $links, $separator ) {
		if ( !in_array( $data['changeType'], array( 'new-topic', 'reply', 'edit-post' ) ) ) {
			return false;
		}

		$linkText = wfMessage( 'flow-rc-topic-of-board' )
			->params( $data['properties']['topic-of-post'] )
			->params( $row->workflow->getArticleTitle()->getText() );

		$description = $data['properties']['user-links']['raw'];
		if ( $data['changeType'] === 'edit-post' ) {
			$description .= ' (<em>' . strip_tags( $data['content'] ) . '</em>)';
		}

		return $this->formatAnchorsAsPipeList( $links, $ctx ) .
			' ' .
			Linker::link( $row->workflow->getArticleTitle(), $linkText->text() ) .
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
			$description;
	}
}
