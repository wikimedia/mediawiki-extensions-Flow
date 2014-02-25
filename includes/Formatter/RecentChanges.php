<?php

namespace Flow\Formatter;

use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use ChangesList;
use Html;
use IContextSource;
use Linker;
use RecentChange;

class RecentChanges extends AbstractFormatter {

	/**
	 * @param ChangesList $cl
	 * @param RecentChange $rc
	 * @param bool $watchlist
	 * @return string|bool Output line, or false on failure
	 */
	public function format( $row, IContextSource $ctx ) {
		try {
			if ( !$this->permissions->isAllowed( $row->revision, 'recentchanges' ) ) {
				return false;
			}
			if ( $row->revision instanceof PostRevision &&
				!$this->permissions->isAllowed( $row->root_post, 'recentchanges' ) ) {
				return false;
			}
			return $this->formatHtml( $row, $ctx );
		} catch ( FlowException $e ) {
			\MWExceptionHandler::logException( $e );
			return false;
		}
	}

	protected function formatHtml( $row, IContextSource $ctx ) {
		$data = $this->serializer->formatApi( $row, $ctx );
		// @todo where should this go?
		$data['size'] = array(
			'old' => $row->recent_change->getAttribute( 'rc_old_len' ),
			'new' => $row->recent_change->getAttribute( 'rc_new_len' ),
		);

		if ( isset( $data['links']['diff'] ) ) {
			$diffLink = $ctx->msg( 'parentheses' )
				->rawParams( $this->apiLinkToAnchor( $data['links']['diff'] ) )
				->escaped();
		} else {
			$diffLink = '';
		}

		// The ' . . ' text between elements
		$separator = $this->changeSeparator();

		return $diffLink .
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
