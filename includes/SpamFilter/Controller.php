<?php

namespace Flow\SpamFilter;

use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use IContextSource;
use Title;
use Status;

class Controller {
	/**
	 * @var SpamFilter[] Array of SpamFilter objects
	 */
	protected $spamfilters = array();

	/**
	 * Accepts multiple spamfilters.
	 *
	 * @param SpamFilter $spamfilter...
	 * @throws FlowException When provided arguments are not an instance of SpamFilter
	 */
	public function __construct( SpamFilter $spamfilter /* [, SpamFilter $spamfilter2 [, ...]] */ ) {
		$this->spamfilters = array_filter( func_get_args() );

		// validate data
		foreach ( $this->spamfilters as $spamfilter ) {
			if ( !$spamfilter instanceof SpamFilter ) {
				throw new FlowException( 'Invalid spamfilter', 'default' );
			}
		}
	}

	/**
	 * @param IContextSource $context
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( IContextSource $context, AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		foreach ( $this->spamfilters as $spamfilter ) {
			if ( !$spamfilter->enabled() ) {
				continue;
			}

			$status = $spamfilter->validate( $context, $newRevision, $oldRevision, $title );

			// no need to go through other filters when invalid data is discovered
			if ( !$status->isOK() ) {
				$titleString = $title->getPrefixedDBkey();
				$oldRevid = ( $oldRevision !== null ) ? $oldRevision->getRevisionId()->getAlphadecimal() : 'None';
				$newRevid = $newRevision->getRevisionId()->getAlphadecimal();
				$klass = get_class( $spamfilter );
				wfDebugLog( 'Flow', __METHOD__ . ": Spam filter failed on '" . $titleString . "'.  Old revid: $oldRevid.  New revid: $newRevid.  Filter: $klass" );
				return $status;
			}
		}

		return Status::newGood();
	}
}
