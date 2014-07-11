<?php

namespace Flow\Formatter;

use IContextSource;
use Linker;

class CheckUser extends AbstractFormatter {
	protected function getHistoryType() {
		return 'checkuser';
	}

	/**
	 * @return string
	 */
	protected function changeSeparator() {
		return ' . . ';
	}

	/**
	 * Create an array of links to be used when formatting the row in checkuser
	 *
	 * @param FormatterRow $row Row of data provided by the check user special page
	 * @param IContextSource $ctx
	 * @return array|null
	 */
	public function format( FormatterRow $row, IContextSource $ctx ) {
		// @todo: this currently only implements CU links
		// we'll probably want to add a hook to CheckUser that lets us blank out
		// the entire line for entries that !isAllowed( <this-revision>, 'checkuser' )
		// @todo: we probably want to get the action description (generated revision comment)
		// into checkuser sooner or later as well.

		$data = $this->serializer->formatApi( $row, $ctx );
		if ( $data['links'] === null ) {
			wfDebugLog( 'Flow', __METHOD__ . ': No links were generated for revision ' . $row->revision->getAlphadecimal() );
			return null;
		}

		return array(
			'links' => $this->formatAnchorsAsPipeList( $data['links'], $ctx ),
			'title' => $this->changeSeparator() . $this->getTitleLink( $data, $row, $ctx ),
		);
	}
}
