<?php

namespace Flow\Tests;

use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use User;

class PostRevisionTestCase extends \MediaWikiTestCase {
	/**
	 * PostRevision object, created from $this->generatePost()
	 *
	 * @var PostRevision
	 */
	protected $revision;

	/**
	 * Creates a $this->revision object, for use in classes that extend this one.
	 */
	protected function setUp() {
		parent::setUp();

		$this->revision = $this->generateObject();
	}

	/**
	 * Returns an array, representing flow_revision & flow_tree_revision db
	 * columns.
	 *
	 * You can pass in arguments to override default data.
	 * With no arguments tossed in, default data (resembling a newly-created
	 * topic title) will be returned.
	 *
	 * @param array[optional] $row DB row data (only specify override columns)
	 * @return array
	 */
	protected function generateRow( array $row = array() ) {
		$uuidPost = UUID::create();
		$uuidRevision = UUID::create();

		$user = User::newFromName( 'UTSysop' );
		list( $userId, $userIp ) = PostRevision::userFields( $user );

		return $row + array(
			// flow_revision
			'rev_id' => $uuidRevision->getBinary(),
			'rev_type' => 'post',
			'rev_user_id' => $userId,
			'rev_user_ip' => $userIp,
			'rev_parent_id' => null,
			'rev_flags' => 'html',
			'rev_content' => 'test content',
			'rev_change_type' => 'new-post',
			'rev_mod_state' => AbstractRevision::MODERATED_NONE,
			'rev_mod_user_id' => null,
			'rev_mod_user_ip' => null,
			'rev_mod_timestamp' => null,
			'rev_mod_reason' => null,
			'rev_last_edit_id' => null,
			'rev_edit_user_id' => null,
			'rev_edit_user_ip' => null,

			// flow_tree_revision
			'tree_rev_descendant_id' => $uuidPost->getBinary(),
			'tree_rev_id' => $uuidRevision->getBinary(),
			'tree_orig_user_id' => $userId,
			'tree_orig_user_ip' => $userIp,
			'tree_parent_id' => null,
		);
	}

	/**
	 * Returns a PostRevision object.
	 *
	 * You can pass in arguments to override default data.
	 * With no arguments tossed in, a default revision (resembling a newly-
	 * created topic title) will be returned.
	 *
	 * @param array[optional] $row DB row data (only specify override columns)
	 * @param array[optional] $children Array of child PostRevision objects
	 * @param int[optional] $depth Depth of the PostRevision object
	 * @return PostRevision
	 */
	protected function generateObject( array $row = array(), $children = array(), $depth = 0 ) {
		$row = $this->generateRow( $row );

		$revision = PostRevision::fromStorageRow( $row );
		$revision->setChildren( $children );
		$revision->setDepth( $depth );

		return $revision;
	}
}
