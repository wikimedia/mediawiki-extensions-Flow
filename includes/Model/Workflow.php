<?php

namespace Flow\Model;

use MWTimestamp;
use Title;
use User;

class Workflow {
	protected $id;
	// @var boolean false before writing to storage
	protected $isNew;
	protected $wiki;
	protected $pageId;
	protected $namespace;
	protected $titleText;
	protected $userId;
	protected $userIp;
	// lock state is a list of state updates, the final state
	// is the active state.
	protected $lockState;
	protected $definitionId;
	protected $lastModified;

	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $obj === null ) {
			$obj = new self;
		} elseif ( !$obj instanceof self ) {
			throw new \MWException( 'Wrong obj type: ' . get_class( $obj ) );
		}
		$obj->id = UUID::create( $row['workflow_id'] );
		$obj->isNew = false;
		$obj->wiki = $row['workflow_wiki'];
		$obj->pageId = $row['workflow_page_id'];
		$obj->namespace = (int) $row['workflow_namespace'];
		$obj->titleText = $row['workflow_title_text'];
		$obj->userId = $row['workflow_user_id'];
		if ( array_key_exists( 'workflow_user_ip', $row ) ) {
			$obj->userIp = $row['workflow_user_ip'];
		} elseif ( isset( $row['workflow_user_text'] ) && $obj->userId === 0 ) {
			$obj->userIp = $row['workflow_user_text'];
		}
		$obj->lockState = $row['workflow_lock_state'];
		$obj->definitionId = UUID::create( $row['workflow_definition_id'] );
		$obj->lastModified = $row['workflow_last_update_timestamp'];
		return $obj;
	}

	static public function toStorageRow( Workflow $obj ) {
		return array(
			'workflow_id' => $obj->id->getBinary(),
			'workflow_wiki' => $obj->wiki,
			'workflow_page_id' => $obj->pageId,
			'workflow_namespace' => $obj->namespace,
			'workflow_title_text' => $obj->titleText,
			'workflow_user_id' => $obj->userId,
			'workflow_user_ip' => $obj->userIp,
			'workflow_lock_state' => $obj->lockState,
			'workflow_definition_id' => $obj->definitionId->getBinary(),
			'workflow_last_update_timestamp' => $obj->lastModified,
		);
	}

	static public function create( Definition $definition, User $user, Title $title ) {
		if ( $title->isLocal() ) {
			$wiki = wfWikiId();
		} else {
			$wiki = $title->getTransWikiID();
		}
		if ( $definition->getWiki() !== $wiki ) {
				throw new \MWException( 'Title and Definition are from separate wikis' );
		}

		$obj = new self;
		// Probably unnecessary to create id up front?
		// simpler in prototype to give everything an id up front?
		$obj->id = UUID::create();
		$obj->isNew = true; // has not been persisted
		$obj->wiki = $definition->getWiki();
		$obj->pageId = $title->getArticleID();
		$obj->namespace = $title->getNamespace();
		$obj->titleText = $title->getDBkey();
		list( $obj->userId, $obj->userIp ) = AbstractRevision::userFields( $user );
		$obj->lockState = 0;
		$obj->definitionId = $definition->getId();
		$obj->updateLastModified();

		return $obj;
	}

	public function getArticleTitle() {
		if ( $this->wiki !== wfWikiId() ) {
			throw new \MWException( 'Interwiki not implemented' );
		}
		return Title::makeTitleSafe( $this->namespace, $this->titleText );
	}

	public function getId() { return $this->id; }
	// new as of this request, unknown if it is saved yet
	public function isNew() { return (bool) $this->isNew; }
	public function getDefinitionId() { return $this->definitionId; }
	public function getUserId() { return $this->userId; }
	public function getUserIp() { return $this->userIp; }
	public function getLastModified() { return $this->lastModified; }
	public function getLastModifiedObj() { return new MWTimestamp( $this->lastModified ); }

	public function updateLastModified() {
		$this->lastModified = wfTimestampNow();
	}

	public function getNamespaceName() {
		global $wgContLang;

		return $wgContLang->getNsText( $this->namespace );
	}

	public function getTitleFullText() {
		$ns = $this->getNamespaceName();
		if ( $ns ) {
			return $ns . ':' . $this->titleText;
		} else {
			return $this->titleText;
		}
	}

	// these are exceptions currently to make debugging easier
	// it should return false later on to allow wider use.
	public function matchesTitle( Title $title ) {
		// Needs to be a non-strict comparrison
		if ( $title->getNamespace() != $this->namespace ) {
			throw new \MWException( 'namespace' );
		}
		if ( $title->getDBkey() !== $this->titleText ) {
			throw new \MWException( 'title' );
		}
		if ( $title->isLocal() ) {
			return $this->wiki === wfWikiId();
		} else {
			return $this->wiki === $title->getTransWikiID();
		}
	}

	public function lock( User $user ) {
		$this->lockState[] = array(
			'user' => $user->getId(),
			'state' => self::STATE_LOCKED,
			'timestamp' => wfTimestampNow(),
		);
	}

	// meh, states need to be user definable
	public function isLocked() {
		if ( !$this->lockState ) {
			return false;
		}
		$state = end( $this->lockState );
		return $state['state'] === self::LOCKED;
	}
}

