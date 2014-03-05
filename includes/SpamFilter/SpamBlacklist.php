<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use Title;
use Status;
use BaseBlacklist;

class SpamBlacklist implements SpamFilter {
	/**
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		$spamObj = BaseBlacklist::getInstance( 'spam' );
		if ( !$spamObj instanceof \SpamBlacklist ) {
			wfWarn( __METHOD__ . ': Expected a SpamBlacklist instance but instead received: ' . get_class( $spamObj ) );
			return Status::newFatal( 'something' );
		}
		$links = $this->getLinks( $newRevision, $title );
		$matches = $spamObj->filter( $links, $title );

		if ( $matches !== false ) {
			$status = Status::newFatal( 'spamprotectiontext' );

			foreach ( $matches as $match ) {
				$status->fatal( 'spamprotectionmatch', $match );
			}

			return $status;
		}

		return Status::newGood();
	}

	/**
	 * @param AbstractRevision $revision
	 * @param Title $title
	 * @return array
	 */
	public function getLinks( AbstractRevision $revision, Title $title ) {
		global $wgParser;
		$options = new \ParserOptions;
		$output = $wgParser->parse( $revision->getContent( 'wikitext' ), $title, $options );
		return array_keys( $output->getExternalLinks() );
	}

	/**
	 * Checks if SpamBlacklist is enabled.
	 *
	 * @return bool
	 */
	public function enabled() {
		return class_exists( 'BaseBlacklist' );
	}
}
