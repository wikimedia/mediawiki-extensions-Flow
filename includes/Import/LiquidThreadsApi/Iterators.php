<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use EmptyIterator;
use Iterator;

class TopicIterator implements Iterator {
	protected $lastOffset = 0;
	protected $maxLoadedId = 0;
	/** @var CachedThreadData **/
	protected $threadData;
	/** @var array Associative array containing API data for the current topic **/
	protected $current = false;
	/** @var string **/
	protected $pageName;
	/** @var Iterator **/
	protected $topicIdIterator;

	public function __construct( CachedThreadData $threadData, $pageName ) {
		$this->threadData = $threadData;
		$this->pageName = $pageName;
		$this->topicIdIterator = new EmptyIterator;

		$this->rewind();
	}

	public function current ( ) {
		return new ImportTopic( $this->current );
	}

	public function key ( ) {
		return $this->current['id'];
	}

	public function next ( ) {
		if ( $this->valid() ) {
			$this->lastOffset = $this->key();
		}

		$continue = true;
		$topicId = false;
		$found = false;
		while( $continue ) {
			foreach( $this->topicIdIterator as $topicId ) {
				if ( $topicId <= $this->lastOffset ) {
					continue;
				} else {
					$found = true;
					break;
				}
			}

			$continue = !$found && $this->loadMore();
		}

		if ( !$found ) {
			$this->current = false;
		} else {
			$this->current = $this->threadData->get( $topicId );
		}
	}

	public function rewind ( ) {
		$this->lastOffset = 0;
		$this->current = false;
		$this->next();
	}

	public function valid ( ) {
		return is_array( $this->current );
	}

	protected function loadMore() {
		$prevCount = $this->threadData->getSize();

		$params = array(
			'thpage' => $this->pageName,
		);

		if ( $this->maxLoadedId ) {
			$params['thstartid'] = $this->maxLoadedId;
		}

		$output = $this->threadData->getFromParams( $params );
		$this->maxLoadedId = max( $this->maxLoadedId, max( array_keys( $output ) ) );

		$this->topicIdIterator = new ArrayIterator( $this->threadData->getTopics() );

		return ( $this->threadData->getSize() > $prevCount );
	}
}

class ReplyIterator implements Iterator {
	/** @var ImportThread **/
	protected $thread;
	/** @var array Array of thread IDs **/
	protected $threadReplies;
	/** @var CachedThreadData **/
	protected $threadData;
	/** @var CachedPageData **/
	protected $pageData;
	/** @var int **/
	protected $replyIndex;

	public function __construct(
		ImportThread $thread,
		CachedThreadData $threadData,
		CachedPageData $pageData
	) {
		$this->thread = $thread;
		$this->threadData = $threadData;
		$this->pageData = $pageData;
		$this->replyIndex = 0;
		$this->threadReplies = array_values( $this->thread->getReplies() );
	}

	public function current ( ) {
		$replyId = $this->threadReplies[$this->replyIndex]['id'];
		$replyData = $this->threadData->get( $replyId );
		return new ImportPost( $replyData, $this->pageData, $this->threadData );
	}

	public function key ( ) {
		return $this->replyIndex;
	}

	public function next ( ) {
		$this->replyIndex++;
	}

	public function rewind ( ) {
		$this->replyIndex = 0;
	}

	public function valid ( ) {
		return $this->replyIndex < count( $this->threadReplies );
	}
}
