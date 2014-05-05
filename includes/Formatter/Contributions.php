<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use ChangesList;
use IContextSource;

class Contributions extends AbstractFormatter {

	/**
	 * @param FormatterRow $row With properties workflow, revision, previous_revision
	 * @param IContextSource $ctx
	 * @return string|bool HTML for contributions entry, or false on failure
	 */
	public function format( FormatterRow $row, IContextSource $ctx ) {
		try {
			if ( !$this->permissions->isAllowed( $row->revision, 'contributions' ) ) {
				return false;
			}
			if ( $row->revision instanceof PostRevision &&
				!$this->permissions->isAllowed( $row->rootPost, 'contributions' ) ) {
				return false;
			}
			return $this->formatHtml( $row, $ctx );
		} catch ( FlowException $e ) {
			\MWExceptionHandler::logException( $e );
			return false;
		}
	}

	/**
	 * @param FormatterRow $row
	 * @param IContextSource $ctx
	 * @return string
	 */
	protected function formatHtml( FormatterRow $row, IContextSource $ctx ) {
		$data = $this->serializer->formatApi( $row, $ctx );

		$charDiff = ChangesList::showCharacterDifference(
			$data['size']['old'],
			$data['size']['new']
		);

		$separator = $this->changeSeparator();

		$links = array();
		$links[] = $this->getDiffAnchor( $data['links'], $ctx );
		$links[] = $this->getHistAnchor( $data['links'], $ctx );

		// Put it all together
		return
			$this->formatTimestamp( $data ) . ' ' .
			$this->formatAnchorsAsPipeList( $links, $ctx ) .
			$separator .
			$charDiff .
			$separator .
			$this->formatDescription( $data, $ctx );
	}
}
