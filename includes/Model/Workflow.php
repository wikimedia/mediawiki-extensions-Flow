<?php

namespace Flow\Model;

use Flow\Exception\CrossWikiException;
use Flow\Exception\DataModelException;
use Flow\Exception\FailCommitException;
use Flow\Exception\InvalidInputException;
use MapCacheLRU;
use MWTimestamp;
use Title;
use User;

class Workflow {

	/**
	 * @var MapCacheLRU
	 */
	private static $titleCache;

	/**
	 * @var string[]
	 */
	static private $allowedTypes = array( 'discussion', 'topic' );

	/**
	 * @var UUID
	 */
	protected $id;

	/**
	 * @var string e.g. topic, discussion, etc.
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $wiki;

	/**
	 * @var integer
	 */
	protected $pageId = 0;

	/**
	 * @var integer
	 */
	protected $namespace;

	/**
	 * @var string
	 */
	protected $titleText;

	/**
	 * @var string
	 */
	protected $lastUpdated;

	/**
	 * @var Title
	 */
	protected $title;

	/**
	 * @var Title
	 */
	protected $ownerTitle;

	/**
	 * @var bool|null Indicates if associated page_id exists (null if not yet looked up)
	 */
	protected $exists;

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
		$obj->type = $row['workflow_type'];
		$obj->wiki = $row['workflow_wiki'];
		$obj->pageId = (int) $row['workflow_page_id'];
		$obj->namespace = (int) $row['workflow_namespace'];
		$obj->titleText = $row['workflow_title_text'];
		$obj->lastUpdated = $row['workflow_last_update_timestamp'];

		return $obj;
	}

	/**
	 * @param Workflow $obj
	 * @return array
	 * @throws FailCommitException
	 */
	static public function toStorageRow( Workflow $obj ) {
		if ( $obj->pageId === 0 ) {
			/*
			 * We try to defer creating a new page as long as possible, which
			 * means that a new board page won't have been created by the time
			 * Workflow object was created: new workflows will have a 0 pageId.
			 * This method is called when the workflow is about to be inserted.
			 * By now, the page has been inserted & we should store the real
			 * page_id this workflow is associated with.
			 */

			// store ID of newly created page & reset exists status
			$title = $obj->getOwnerTitle();
			$obj->pageId = $title->getArticleID( Title::GAID_FOR_UPDATE );
			$obj->exists = null;

			if ( $obj->pageId === 0 ) {
				throw new FailCommitException( 'No page for workflow: ' . serialize( $obj ) );
			}
		}

		return array(
			'workflow_id' => $obj->id->getAlphadecimal(),
			'workflow_type' => $obj->type,
			'workflow_wiki' => $obj->wiki,
			'workflow_page_id' => $obj->pageId,
			'workflow_namespace' => $obj->namespace,
			'workflow_title_text' => $obj->titleText,
			'workflow_lock_state' => 0, // unused
			'workflow_last_update_timestamp' => $obj->lastUpdated,
			// not used, but set it to empty string so it doesn't fail in strict mode
			'workflow_name' => '',
		);
	}

	/**
	 * @param string $type
	 * @param Title $title
	 * @return Workflow
	 * @throws DataModelException
	 */
	static public function create( $type, Title $title ) {
		// temporary limitation until we implement something more concrete
		if ( !in_array( $type, self::$allowedTypes ) ) {
			throw new DataModelException( 'Invalid workflow type provided: ' . $type, 'process-data' );
		}
		if ( $title->isLocal() ) {
			$wiki = wfWikiId();
		} else {
			$wiki = $title->getTransWikiID();
		}

		$obj = new self;
		$obj->id = UUID::create();
		$obj->type = $type;
		$obj->wiki = $wiki;

		// for new pages, article id will be 0; it'll be fetched again in toStorageRow
		$obj->pageId = $title->getArticleID();
		$obj->namespace = $title->getNamespace();
		$obj->titleText = $title->getDBkey();
		$obj->updateLastUpdated( $obj->id );

		// we just created a new workflow; wipe out any cached data for the
		// associated title
		if ( self::$titleCache !== null ) {
			$key = implode( '|', array( $obj->wiki, $obj->namespace, $obj->titleText ) );
			self::$titleCache->clear( array( $key ) );
		}

		return $obj;
	}

	/**
	 * Update the workflow after a change to title or ID (such as page move or
	 * restoration).
	 *
	 * @param int $oldPageId The page_id the workflow is currently located at
	 * @param Title $newPage The page the workflow is moving to
	 * @throws DataModelException
	 */
	public function updateFromPageId( $oldPageId, Title $newPage ) {
		if ( $oldPageId !== $this->pageId ) {
			throw new DataModelException( 'Must update from same page id. ' . $this->pageId . ' !== ' . $oldPageId );
		}

		$this->pageId = $newPage->getArticleID();
		$this->namespace = $newPage->getNamespace();
		$this->titleText = $newPage->getDBkey();
	}

	/**
	 * Return the title this workflow responds at
	 *
	 * @return Title
	 * @throws CrossWikiException
	 */
	public function getArticleTitle() {
		if ( $this->title ) {
			return $this->title;
		}
		// evil hax
		if ( $this->type === 'topic' ) {
			$namespace = NS_TOPIC;
			$titleText = $this->id->getAlphadecimal();
		} else {
			$namespace = $this->namespace;
			$titleText = $this->titleText;
		}
		return $this->title = self::getFromTitleCache( $this->wiki, $namespace, $titleText );
	}

	/**
	 * Return the title this workflow was created at
	 *
	 * @return Title
	 * @throws CrossWikiException
	 */
	public function getOwnerTitle() {
		if ( $this->ownerTitle ) {
			return $this->ownerTitle;
		}
		return $this->ownerTitle = self::getFromTitleCache( $this->wiki, $this->namespace, $this->titleText );
	}

	/**
	 * Can't use the title cache in Title class, it only operates on default namespace
	 *
	 * @param string $wiki
	 * @param int $namespace
	 * @param string $titleText
	 * @return Title
	 * @throws CrossWikiException
	 * @throws InvalidInputException
	 */
	public static function getFromTitleCache( $wiki, $namespace, $titleText ) {
		if ( self::$titleCache === null ) {
			self::$titleCache = new MapCacheLRU( 50 );
		}

		$key = implode( '|', array( $wiki, $namespace, $titleText ) );
		$title = self::$titleCache->get( $key );
		if ( $title === null ) {
			$title = Title::makeTitleSafe( $namespace, $titleText );
			if ( $title ) {
				self::$titleCache->set( $key, $title );
			} else {
				throw new InvalidInputException( "Fail to create title from namespace $namespace and title text '$titleText'", 'invalid-input' );
			}
		}

		return $title;
	}

	/**
	 * @return UUID
	 */
	public function getId() { return $this->id; }

	/**
	 * @return string
	 */
	public function getType() { return $this->type; }

	/**
	 * Get the wiki ID, e.g. eswiki
	 *
	 * @return string
	 */
	public function getWiki() { return $this->wiki; }

	/**
	 * @return bool
	 */
	public function isDeleted() {
		if ( $this->exists === null ) {
			$this->exists = Title::newFromID( $this->pageId ) !== null;
		}

		// a board that does not yet exist (because workflow has not yet
		// been stored) is not deleted, it just doesn't exist yet
		return !$this->isNew() && !$this->exists;
	}

	/**
	 * Returns true if the workflow is new as of this request.
	 *
	 * @return boolean
	 */
	public function isNew() {
		return $this->pageId === 0;
	}

	/**
	 * @return string
	 */
	public function getLastUpdated() { return $this->lastUpdated; }

	/**
	 * @return \MWTimestamp
	 */
	public function getLastUpdatedObj() { return new MWTimestamp( $this->lastUpdated ); }

	public function updateLastUpdated( UUID $latestRevisionId ) {
		$this->lastUpdated = $latestRevisionId->getTimestamp();
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
		return $this->getArticleTitle()->equals( $title );
	}

	/**
	 * @param string $permission
	 * @param User $user
	 * @return bool
	 */
	public function userCan( $permission, $user ) {
		$title = $this->getArticleTitle();
		$allowed = $title->userCan( $permission, $user );
		if ( $allowed && $this->type === 'topic' ) {
			$allowed = $this->getOwnerTitle()->userCan( $permission, $user );
		}

		return $allowed;
	}

	/**
	 * Pass-through to Title::getUserPermissionsErrors
	 * with title, and owning title if needed.
	 * @param string $permission
	 * @param User $user
	 * @return array Array of arrays of the arguments to wfMessage to explain permissions problems.
	 */
	public function getPermissionErrors( $permission, $user ) {
		$title = $this->type === 'topic' ? $this->getOwnerTitle() : $this->getArticleTitle();

		$errors = $title->getUserPermissionsErrors( $permission, $user );
		if ( count( $errors ) ) {
			return $errors;
		}

		return array();
	}
}
