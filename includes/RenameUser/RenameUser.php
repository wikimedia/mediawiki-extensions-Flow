<?php

namespace Flow\RenameUser;

use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Model\UUID;

class RenameUser {
	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * Class name map, used to resolve revisions to the correct objects using
	 * the value of rev_type.
	 *
	 * @var array
	 */
	protected $revisionClassMap = array(
		// rev_type => class name
		'post' => 'PostRevision',
		'header' => 'Header',
	);

	/**
	 * @param DbFactory $dbFactory
	 * @param ManagerGroup $storage
	 */
	public function __construct( DbFactory $dbFactory, ManagerGroup $storage ) {
		$this->dbFactory = $dbFactory;
		$this->storage = $storage;
	}

	/**
	 * @param int $userId User id
	 * @param string $old Old user name
	 * @param string $new New user name
	 * @return bool
	 */
	public function workflow( $userId, $old, $new ) {
		$storage = $this->storage->getStorage( 'Workflow' );
		$db = $this->dbFactory->getDB( DB_SLAVE );

		$workflows = $db->select(
			'flow_workflow',
			'workflow_id',
			array(
				'workflow_user_id' => $userId,
				'workflow_user_text' => $old,
				'workflow_wiki' => wfWikiID()
			)
		);

		foreach ( $workflows as $workflow ) {
			$workflow = $storage->get( UUID::create( $workflow->workflow_id ) );
			if ( !$workflow ) {
				return false;
			}

			$workflow->setUserText( $new );
			$storage->put( $workflow );
		}

		return true;
	}

	/**
	 * @param int $userId User id
	 * @param string $old Old user name
	 * @param string $new New user name
	 * @return bool
	 */
	function revisionUser( $userId, $old, $new ) {
		$db = $this->dbFactory->getDB( DB_SLAVE );

		$revisions = $db->select(
			'flow_revision',
			array(
				'rev_id',
				'rev_type'
			),
			array(
				'rev_user_id' => $userId,
				'rev_user_text' => $old
//				'workflow_wiki' => wfWikiID() // @todo: how to check for wiki here?
			)
		);

		foreach ( $revisions as $revision ) {
			$className = $this->revisionClassMap[$revision->rev_type];
			$storage = $this->storage->getStorage( $className );

			$revision = $storage->get( UUID::create( $revision->rev_id ) );
			if ( !$revision ) {
				return false;
			}

			$revision->setUserText( $new );
			$storage->put( $revision );
		}

		return true;
	}

	/**
	 * @param int $userId User id
	 * @param string $old Old user name
	 * @param string $new New user name
	 * @return bool
	 */
	function revisionModeratedByUser( $userId, $old, $new ) {
		$db = $this->dbFactory->getDB( DB_SLAVE );

		$revisions = $db->select(
			'flow_revision',
			array(
				'rev_id',
				'rev_type'
			),
			array(
				'rev_mod_user_id' => $userId,
				'rev_mod_user_text' => $old
//				'workflow_wiki' => wfWikiID() // @todo: how to check for wiki here?
			)
		);

		foreach ( $revisions as $revision ) {
			$className = $this->revisionClassMap[$revision->rev_type];
			$storage = $this->storage->getStorage( $className );

			$revision = $storage->get( UUID::create( $revision->rev_id ) );
			if ( !$revision ) {
				return false;
			}

			$revision->setModeratedByUserText( $new );
			$storage->put( $revision );
		}

		return true;
	}

	/**
	 * @param int $userId User id
	 * @param string $old Old user name
	 * @param string $new New user name
	 * @return bool
	 */
	function revisionLastEditUser( $userId, $old, $new ) {
		$db = $this->dbFactory->getDB( DB_SLAVE );

		$revisions = $db->select(
			'flow_revision',
			array(
				'rev_id',
				'rev_type'
			),
			array(
				'rev_edit_user_id' => $userId,
				'rev_edit_user_text' => $old
//				'workflow_wiki' => wfWikiID() // @todo: how to check for wiki here?
			)
		);

		foreach ( $revisions as $revision ) {
			$className = $this->revisionClassMap[$revision->rev_type];
			$storage = $this->storage->getStorage( $className );

			$revision = $storage->get( UUID::create( $revision->rev_id ) );
			if ( !$revision ) {
				return false;
			}

			$revision->setLastEditUserText( $new );
			$storage->put( $revision );
		}

		return true;
	}

	/**
	 * @param int $userId User id
	 * @param string $old Old user name
	 * @param string $new New user name
	 * @return bool
	 */
	public function treeRevision( $userId, $old, $new ) {
		$storage = $this->storage->getStorage( 'PostRevision' );
		$db = $this->dbFactory->getDB( DB_SLAVE );

		$revisions = $db->select(
			'flow_tree_revision',
			'tree_rev_id',
			array(
				'tree_orig_user_id' => $userId,
				'tree_orig_user_text' => $old
//				'workflow_wiki' => wfWikiID() // @todo: how to check for wiki here?
			)
		);

		foreach ( $revisions as $revision ) {
			$revision = $storage->get( UUID::create( $revision->tree_rev_id ) );
			if ( !$revision ) {
				return false;
			}

			$revision->setOrigUserText( $new );
			$storage->put( $revision );
		}

		return true;
	}
}
