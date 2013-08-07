<?php

namespace Flow\Model;

use Title;
use User;

class Workflow {
	protected $id;
	protected $isNew;
	protected $wiki;
	protected $pageId;
	protected $namespace;
	protected $titleText;
	protected $userId;
	protected $userText;
	// lock state is a list of state updates, the final state
	// is the active state.
	protected $lockState;
	protected $definitionId;

	static public function fromStorageRow( array $row ) {
		$obj = new self;
		$obj->id = new UUID( $row['workflow_id'] );
		$obj->wiki = $row['workflow_wiki'];
		$obj->pageId = new UUID( $row['workflow_page_id'] );
		$obj->namespace = (int) $row['workflow_namespace'];
		$obj->titleText = $row['workflow_title_text'];
		$obj->userId = $row['workflow_user_id'];
		$obj->userText = $row['workflow_user_text'];
		$obj->lockState = $row['workflow_lock_state'];
		$obj->definitionId = new UUID( $row['workflow_definition_id'] );
		return $obj;
	}

	static public function toStorageRow( Workflow $obj ) {
		if ( ! $obj->definitionId instanceof UUID ) {
			die( var_dump( $obj->definitionId, $obj ) );
		}
		return array(
			'workflow_id' => $obj->id->getBinary(),
			'workflow_wiki' => $obj->wiki,
			'workflow_page_id' => $obj->pageId,
			'workflow_namespace' => $obj->namespace,
			'workflow_title_text' => $obj->titleText,
			'workflow_user_id' => $obj->userId,
			'workflow_user_text' => $obj->userText,
			'workflow_lock_state' => $obj->lockState,
			'workflow_definition_id' => $obj->definitionId->getBinary(),
		);
	}

	static public function create( Definition $definition, User $user, Title $title ) {
		if ( $title->isLocal() ) {
			if ( $definition->getWiki() !== wfWikiId() ) {
					throw new \Exception( 'Title and Definition are from separate wikis' );
			}
		} else {
			if ( $definition->getWiki() !== $title->getTransWikiID() ) {
				throw new \Exception( 'Title and Definition are from separate wikis' );
			}
		}

		$obj = new self;
		// Probably unnecessary to create id up front?
		// simpler in prototype to give everything an id up front?
		$obj->id = new UUID();
		$obj->isNew = true; // new as of this request
		$obj->wiki = $definition->getWiki();
		$obj->pageId = $title->getArticleID();
		$obj->namespace = $title->getNamespace();
		$obj->titleText = $title->getDBkey();
		$obj->userId = $user->getId();
		$obj->userText = $user->getName();
		$obj->lockState = 0;
		$obj->definitionId = $definition->getId();

		if ( ! $obj->definitionId instanceof UUID ) {
			die( var_dump( $obj, $definition ) );
		}
		return $obj;
	}

	public function getTitle() {
		$article = $this->getArticleTitle();
		return Title::newFromText( "Flow/" . $article->getPrefixedDBkey(), NS_SPECIAL );
	}

	public function getArticleTitle() {
		if ( $this->wiki !== wfWikiId() ) {
			throw new \MWException( 'Interwiki not implemented' );
		}
		return Title::newFromText( $this->titleText, $this->namespace );
	}

	public function getId() { return $this->id; }
	// new as of this request, unknown if it is saved yet
	public function isNew() { return (bool) $this->isNew; }
	public function getDefinitionId() { return $this->definitionId; }
	public function getUserId() { return $this->userId; }
	public function getUserText() { return $this->userText; }

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

	public function matchesTitle( Title $title ) {
		if ( $title->getArticleID() === 0 ) {
			return false; // non-existant page
		}
		if ( $title->getNamespace() !== $this->namespace ) {
			var_dump( $title->getNamespace());
			var_dump( $this->namespace );
			die();
			throw new \Exception( 'namespace' );
		}
		if ( $title->getDBkey() !== $this->titleText ) {
			throw new \Exception( 'title' );
		}
		if ( $title->isLocal() ) {
			return $this->wiki === wfWikiId();
		} else {
			return $this->wiki === $title->getTransWikiID();
		}
	}

	public function lock( User $user ) {
		if ( !$user->isAllowed( 'flow-delete-workflow' ) ) {
			return false;
		}
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

