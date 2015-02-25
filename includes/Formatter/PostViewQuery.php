<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Exception\InvalidInputException;
use Flow\Exception\PermissionException;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;

class PostViewQuery extends RevisionViewQuery {

	/**
	 * {@inheritDoc}
	 */
	protected function createRevision( $revId ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}
		return $this->storage->get(
			'PostRevision',
			$revId
		);
	}
}
