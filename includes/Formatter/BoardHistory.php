<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use ChangesList;
use IContextSource;

class BoardHistory extends AbstractFormatter {

	/**
	 * @param \stdClass $row A result row from BoardHistoryQuery
	 * @param IContextSource $ctx
	 * @return string|false HTML string representing $row
	 */
	public function format( $row, IContextSource $ctx ) {
		//try {
			if ( !$this->permissions->isAllowed( $row->revision, 'history' ) ) {
				return false;
			}
			if ( $row->revision instanceof PostRevision &&
				!$this->permissions->isAllowed( $row->rootPost, 'history' ) ) {
				return false;
			}
			return $this->formatHtml( $row, $ctx );
		//} catch ( FlowException $e ) {
		//	\MWExceptionHandler::logException( $e );
		//	return false;
		//}
	}

	protected function formatHtml( $row, IContextSource $ctx ) {
		$data = $this->serializer->formatApi( $row, $ctx );

		$charDiff = ChangesList::showCharacterDifference(
			$data['size']['old'],
			$data['size']['new']
		);

		$separator = $this->changeSeparator();

		$links = array();
		$links[] = $this->getDiffCurAnchor( $data['links'], $ctx );
		$links[] = $this->getDiffPrevAnchor( $data['links'], $ctx );
		if ( isset( $data['links']['topic'] ) ) {
			$links[] = $data['links']['topic'];
		}

		$formattedTimestamp = $this->formatTimestamp( $data );

		return $this->formatAnchorsAsPipeList( $data['links'], $ctx ) . ' '
			. $formattedTimestamp
			. $separator
			. $this->formatDescription( $data, $ctx )
			. ( $charDiff ? $separator . $charDiff : '' );
	}
}
