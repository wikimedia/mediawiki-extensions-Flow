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

	public function current(): ?ImportTopic {
		if ( $this->current === false ) {
			return null;
		}
		return $this->currentTopic;
	}

	public function key(): mixed {
		return $this->current;
	}

	public function next(): void {
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
				$lastOffset = $topicId;

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

	public function rewind(): void {
		$this->current = null;
		$this->topicIdIterator->rewind();
		$this->next();
	}

	public function valid(): bool {
		return $this->current !== false;
	}

	/**
	 * @return bool True when more topics were loaded
	 */
	protected function loadMore() {
		try {
			// + 1 to not return the existing max topic
			$output = $this->threadData->getFromPage( $this->pageName, $this->maxId + 1 );
		} catch ( ApiNotFoundException ) {
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
