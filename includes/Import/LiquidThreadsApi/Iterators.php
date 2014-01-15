<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
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
	 * @var integer|false Id of the current topic, or false if no current topic
	 */
	protected $current = false;

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
	 * @var integer The maximum id received by self::loadMore
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
		$this->topicIdIterator->rewind();
	}

	/**
	 * @return ImportTopic
	 */
	public function current() {
		if ( $this->current === false ) {
			return null;
		}
		return $this->importSource->getTopic( $this->current );
	}

	/**
	 * @return integer
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
			while( $this->topicIdIterator->valid() ) {
				$topicId = $this->topicIdIterator->current();
				$this->topicIdIterator->next();

				if ( $topicId > $lastOffset ) {
					$this->current = $topicId;
					//echo __METHOD__, ": Returning topic $topicId (", $this->key(), ")\n";
					return;
				}
			}
		} while( $this->loadMore() );

		//echo __METHOD__, ": No more topics found\n";
		// nothing found, nothing more to load
		$this->current = false;
	}

	public function rewind() {
		$this->current = 0;
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
		//echo __METHOD__, ": Loading another page\n";
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
	 * @return integer
	 */
	public function key() {
		return $this->replyIndex;
	}

	public function next() {
		while( ++$this->replyIndex < count( $this->threadReplies ) ) {
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
