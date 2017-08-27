<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Flow\Import\IImportObject;
use Iterator;

class TopicIterator implements Iterator {
	/**
	 * @var ImportSource
	 */
	protected $importSource;

	/**
	 * @var CachedThreadData Access point for api data
	 */
	protected $threadData;

	/**
	 * @var int|false|null Lqt id of the current topic, false if no current topic, null if unknown.
	 */
	protected $current = false;

	/**
	 * @var ImportTopic The current topic.
	 */
	protected $currentTopic = null;

	/**
	 * @var string Name of the remote page the topics exist on
	 */
	protected $pageName;

	/**
	 * @var Iterator A list of topic ids.  Iterator used to simplify maintaining
	 *  an explicit position within the list.
	 */
	protected $topicIdIterator;

	/**
	 * @var int The maximum id received by self::loadMore
	 */
	protected $maxId;

	/**
	 * @param ImportSource $source
	 * @param CachedThreadData $threadData
	 * @param string $pageName
	 */
	public function __construct( ImportSource $source, CachedThreadData $threadData, $pageName ) {
		$this->importSource = $source;
		$this->threadData = $threadData;
		$this->pageName = $pageName;
		$this->topicIdIterator = new ArrayIterator( $threadData->getTopics() );
		$this->rewind();
	}

	/**
	 * @return ImportTopic
	 */
	public function current() {
		if ( $this->current === false ) {
			return null;
		}
		return $this->currentTopic;
	}

	/**
	 * @return int
	 */
	public function key() {
		return $this->current;
	}

	public function next() {
		if ( !$this->valid() ) {
			return;
		}

		$lastOffset = $this->key();
		do {
			while ( $this->topicIdIterator->valid() ) {
				$topicId = $this->topicIdIterator->current();
				$this->topicIdIterator->next();

				// this topic id has been seen before.
				if ( $topicId <= $lastOffset ) {
					continue;
				}

				// hidden and deleted threads come back as null
				$topic = $this->importSource->getTopic( $topicId );
				if ( $topic === null ) {
					continue;
				}

				$this->current = $topicId;
				$this->currentTopic = $topic;
				return;
			}
		} while ( $this->loadMore() );

		// nothing found, nothing more to load
		$this->current = false;
	}

	public function rewind() {
		$this->current = null;
		$this->topicIdIterator->rewind();
		$this->next();
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return $this->current !== false;
	}

	/**
	 * @return bool True when more topics were loaded
	 */
	protected function loadMore() {
		try {
			// + 1 to not return the existing max topic
			$output = $this->threadData->getFromPage( $this->pageName, $this->maxId + 1 );
		} catch ( ApiNotFoundException $e ) {
			// No more results, end loop
			return false;
		}

		$this->maxId = max( array_keys( $output ) );
		$this->topicIdIterator = new ArrayIterator( $this->threadData->getTopics() );
		$this->topicIdIterator->rewind();

		// Keep looping until we get a not found error
		return true;
	}
}

class ReplyIterator implements Iterator {
	/** @var ImportPost **/
	protected $post;
	/** @var array Array of thread IDs **/
	protected $threadReplies;
	/** @var int **/
	protected $replyIndex;
	/** @var ImportPost|null */
	protected $current;

	public function __construct( ImportPost $post ) {
		$this->post = $post;
		$this->replyIndex = 0;

		$apiResponse = $post->getApiResponse();
		$this->threadReplies = array_values( $apiResponse['replies'] );
	}

	/**
	 * @return ImportPost|null
	 */
	public function current() {
		return $this->current;
	}

	/**
	 * @return int
	 */
	public function key() {
		return $this->replyIndex;
	}

	public function next() {
		while ( ++$this->replyIndex < count( $this->threadReplies ) ) {
			try {
				$replyId = $this->threadReplies[$this->replyIndex]['id'];
				$this->current = $this->post->getSource()->getPost( $replyId );
				return;
			} catch ( ApiNotFoundException $e ) {
				// while loop fall-through handles our error case
			}
		}

		// Nothing found, set current to null
		$this->current = null;
	}

	public function rewind() {
		$this->replyIndex = -1;
		$this->next();
	}

	public function valid() {
		return $this->current !== null;
	}
}

/**
 * Iterates over the revisions of a foreign page to produce
 * revisions of a Flow object.
 */
class RevisionIterator implements Iterator {
	/** @var array **/
	protected $pageData;

	/** @var int **/
	protected $pointer;

	/** @var IImportObject **/
	protected $parent;

	public function __construct( array $pageData, IImportObject $parent, $factory ) {
		$this->pageData = $pageData;
		$this->pointer = 0;
		$this->parent = $parent;
		$this->factory = $factory;
	}

	protected function getRevisionCount() {
		if ( isset( $this->pageData['revisions'] ) ) {
			return count( $this->pageData['revisions'] );
		} else {
			return 0;
		}
	}

	public function valid() {
		return $this->pointer < $this->getRevisionCount();
	}

	public function next() {
		++$this->pointer;
	}

	public function key() {
		return $this->pointer;
	}

	public function rewind() {
		$this->pointer = 0;
	}

	public function current() {
		return call_user_func(
			$this->factory,
			$this->pageData['revisions'][$this->pointer],
			$this->parent
		);
	}
}
