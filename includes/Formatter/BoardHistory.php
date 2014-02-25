<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use ChangesList;
use Html;
use IContextSource;

class BoardHistory extends AbstractFormatter {

	/**
	 * @param \stdClass $row A result row from BoardHistoryQuery
	 * @param IContextSource $ctx
	 * @return string|false HTML string representing $row
	 */
	public function format( $row, IContextSource $ctx ) {
		try {
			if ( !$this->permissions->isAllowed( $row->revision, 'board-history' ) ) {
				return false;
			}
			if ( $row->revision instanceof PostRevision &&
				!$this->permissions->isAllowed( $row->root_post, 'board-history' ) ) {
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

		$charDiff = ChangesList::showCharacterDifference(
			$data['size']['old'],
			$data['size']['new']
		);

		$separator = $this->changeSeparator();

		// timestamp needs to render before links list to not duplicate the link
		$formattedTimestamp = $this->formatTimestamp( $data );

		return $this->formatLinksAsPipeList( $data['links'], $ctx ) . ' '
			. $formattedTimestamp
			. $separator
			. $this->formatDescription( $data, $ctx )
			. ( $charDiff ? $separator . $charDiff : '' );
	}
}
