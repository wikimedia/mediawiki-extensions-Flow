<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Flow\Import\IImportHeader;
use Flow\Import\IImportObject;
use Flow\Import\IImportPost;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;
use Flow\Import\ImportException;
use Flow\Import\IObjectRevision;
use Flow\Import\IRevisionableObject;
use Iterator;
use MWTimestamp;
use Title;
use User;

// Represents a revision the script makes on its own behalf, using a script user
class ScriptedImportRevision implements IObjectRevision {
	/** @var IImportObject **/
	protected $parentObject;

	/** @var User */
	protected $destinationScriptUser;

	/** @var string */
	protected $revisionText;

	/** @var string */
	protected $timestamp;

	/**
	 * Creates a ScriptedImportRevision with the current timestamp, given a script user
	 * and arbitrary text.
	 *
	 * @param IImportObject $parentObject Object this is a revision of
	 * @param User $destinationScriptUser User that performed this scripted edit
	 * @param string $revisionText Text of revision
	 */
	function __construct( IImportObject $parentObject, User $destinationScriptUser, $revisionText ) {
		$this->parent = $parentObject;
		$this->destinationScriptUser = $destinationScriptUser;
		$this->revisionText = $revisionText;
		$this->timestamp = wfTimestampNow();
	}

	public function getText() {
		return $this->revisionText;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function getAuthor() {
		return $this->destinationScriptUser->getName();
	}

	// XXX: This is called but never used, but if it were, including getText and getAuthor in
	// the key might not be desirable, because we don't necessarily want to re-import
	// the revision when these change.
	public function getObjectKey() {
		return $this->parent->getObjectKey() . ':rev:scripted:' . md5( $this->getText() . $this->getAuthor() );
	}
}
