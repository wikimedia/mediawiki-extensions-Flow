<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use ChangesList;
use ContribsPager;
use Html;
use IContextSource;

class Contributions extends AbstractFormatter {

	/**
	 * @param \stdClass $row With properties workflow, revision, previous_revision
	 * @param IContextSource $ctx
	 * @return string|bool HTML for contributions entry, or false on failure
	 */
	public function format( $row, IContextSource $ctx ) {
		try {
			// @todo check root post?
			if ( !$this->permissions->isAllowed( $row->revision, 'contributions' ) ) {
				return false;
			}
			if ( $row->revision instanceof PostRevision &&
				!$this->permissions->isAllowed( $row->root_post, 'contributions' ) ) {
				return false;
			}
			return $this->formatHtml( $row, $ctx );
		} catch ( FlowException $e ) {
			// Comment out for now since we expect some flow exceptions, when gerrit 111952 is
			// merged, then we will turn this back on so we can catch unexpected exceptions.
			//\MWExceptionHandler::logException( $e );continueger
			return false;
		}
	}

	protected function formatHtml( $row, IContextSource $ctx ) {
		$data = $this->serializer->formatApi( $row, $ctx );

		$diffLink = '';
		if ( isset( $data['links']['diff'] ) ) {
			$diffLink = $ctx->msg( 'parentheses' )
				->rawParams( $this->apiLinkToAnchor( $data['links']['diff'] ) )
				->escaped();
		}

		$charDiff = ChangesList::showCharacterDifference(
			$data['size']['old'],
			$data['size']['new']
		);

		$separator = $this->changeSeparator();

		// Put it all together
		return
			$this->formatTimestamp( $data ) . ' ' .
			$this->formatLinksAsPipeList( $data['links'], $ctx ) .
			$separator .
			$charDiff .
			$separator .
			$this->formatDescription( $data, $ctx ) . ' ' .
			$diffLink;
	}
}
