<?php

namespace Flow\Model;

use MWTimestamp;
use Title;
use User;
use Flow\Exception\DataModelException;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidInputException;

class Workflow {

	/**
	 * @var UUID
	 */
	protected $id;

	/**
	 * @var boolean false before writing to storage
	 */
	protected $isNew;

	/**
	 * @var string
	 */
	protected $wiki;

	/**
	 * @var integer
	 */
	protected $pageId;

	/**
	 * @var integer
	 */
	protected $namespace;

	/**
	 * @var string
	 */
	protected $titleText;

	/**
	 * @var integer
	 */
	protected $userId;

	/**
	 * @var string|null
	 */
	protected $userIp;

	/**
	 * lock state is a list of state updates, the final state
	 * is the active state. It is unused and must be reviewed
	 * before any use
	 *
	 * @var array
	 */
	protected $lockState;

	/**
	 * @var UUID
	 */
	protected $definitionId;

	/**
	 * @var string
	 */
	protected $lastModified;

	const STATE_LOCKED = 'locked';

	/**
	 * @param array $row
	 * @param Workflow|null $obj
	 * @return Workflow
	 * @throws DataModelException
	 */
	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $obj === null ) {
			$obj = new self;
		} elseif ( !$obj instanceof self ) {
			throw new DataModelException( 'Wrong obj type: ' . get_class( $obj ), 'process-data' );
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
		// BC for workflow_user_text field
		} elseif ( isset( $row['workflow_user_text'] ) && $obj->userId === 0 ) {
			$obj->userIp = $row['workflow_user_text'];
		}
		$obj->lockState = $row['workflow_lock_state'];
		$obj->definitionId = UUID::create( $row['workflow_definition_id'] );
		$obj->lastModified = $row['workflow_last_update_timestamp'];
		return $obj;
	}

	/**
	 * @param Workflow $obj
	 * @return array
	 */
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

	/**
	 * @param Definition $definition
	 * @param User $user
	 * @param Title $title
	 * @return Workflow
	 * @throws DataModelException
	 */
	static public function create( Definition $definition, User $user, Title $title ) {
		if ( $title->isLocal() ) {
			$wiki = wfWikiId();
		} else {
			$wiki = $title->getTransWikiID();
		}
		if ( $definition->getWiki() !== $wiki ) {
			throw new DataModelException( 'Title and Definition are from separate wikis', 'process-data' );
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

	/**
	 * @return Title
	 * @throws FlowException
	 */
	public function getArticleTitle() {
		if ( $this->wiki !== wfWikiId() ) {
			throw new FlowException( 'Interwiki to ' . $this->wiki . ' not implemented ', 'default' );
		}
		return Title::makeTitleSafe( $this->namespace, $this->titleText );
	}

	/**
	 * @return UUID
	 */
	public function getId() { return $this->id; }

	/**
	 * Returns true if the workflow is new as of this request (regardless of
	 * whether or not is it already saved yet - that's unknown).
	 *
	 * @return boolean
	 */
	public function isNew() { return (bool) $this->isNew; }

	/**
	 * @return UUID
	 */
	public function getDefinitionId() { return $this->definitionId; }

	/**
	 * @return integer
	 */
	public function getUserId() { return $this->userId; }

	/**
	 * @return string|null
	 */
	public function getUserIp() { return $this->userIp; }

	/**
	 * @return string
	 */
	public function getLastModified() { return $this->lastModified; }

	/**
	 * @return \MWTimestamp
	 */
	public function getLastModifiedObj() { return new MWTimestamp( $this->lastModified ); }

	public function updateLastModified() {
		$this->lastModified = wfTimestampNow();
	}

	/**
	 * @return string
	 */
	public function getNamespaceName() {
		global $wgContLang;

		return $wgContLang->getNsText( $this->namespace );
	}

	/**
	 * @return string
	 */
	public function getTitleFullText() {
		$ns = $this->getNamespaceName();
		if ( $ns ) {
			return $ns . ':' . $this->titleText;
		} else {
			return $this->titleText;
		}
	}

	/**
	 * these are exceptions currently to make debugging easier
	 * it should return false later on to allow wider use.
	 *
	 * @param Title $title
	 * @return boolean
	 * @throws InvalidInputException
	 * @throws InvalidInputException
	 */
	public function matchesTitle( Title $title ) {
		// Needs to be a non-strict comparrison
		if ( $title->getNamespace() != $this->namespace ) {
			throw new InvalidInputException( 'namespace', 'invalid-input' );
		}
		if ( $title->getDBkey() !== $this->titleText ) {
			throw new InvalidInputException( 'title', 'invalid-input' );
		}
		if ( $title->isLocal() ) {
			return $this->wiki === wfWikiId();
		} else {
			return $this->wiki === $title->getTransWikiID();
		}
	}

	/**
	 * Unused, review before use
	 *
	 * @param User $user
	 */
	public function lock( User $user ) {
		$this->lockState[] = array(
			'id' => UUID::create(),
			'user' => $user->getId(),
			'state' => self::STATE_LOCKED,
		);
	}

	/**
	 * @return boolean
	 */
	public function isLocked() {
		if ( !$this->lockState ) {
			return false;
		}
		$state = end( $this->lockState );
		return $state['state'] === self::STATE_LOCKED;
	}
}

