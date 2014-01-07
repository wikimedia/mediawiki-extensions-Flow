<?php

namespace Flow\SpamFilter;

use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Title;
use Status;

class Controller {
	/**
	 * @var array Array of SpamFilter objects
	 */
	protected $spamfilters = array();

	/**
	 * Accepts multiple spamfilters.
	 *
	 * @param SpamFilter $spamfilter
	 */
	public function __construct( SpamFilter $spamfilter /* [, SpamFilter $spamfilter2 [, ...]] */ ) {
		$this->spamfilters = func_get_args();

		// validate data
		foreach ( $this->spamfilters as $spamfilter ) {
			if ( !$spamfilter instanceof SpamFilter ) {
				throw new FlowException( 'Invalid spamfilter', 'default' );
			}
		}
	}

	/**
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision[optional] $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		foreach ( $this->spamfilters as $spamfilter ) {
			if ( !$spamfilter->enabled() ) {
				continue;
			}

			$status = $spamfilter->validate( $newRevision, $oldRevision, $title );

			// no need to go through other filters when invalid data is discovered
			if ( !$status->isOK() ) {
				return $status;
			}
		}

		return Status::newGood();
	}
}
