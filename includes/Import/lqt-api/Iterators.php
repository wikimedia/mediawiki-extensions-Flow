<?php

namespace Flow\Import;

use Iterator;

class LiquidThreadsApiTopicIterator implements Iterator {
	protected $lastOffset = 0;
	protected $maxLoadedId = 0;
	protected $source;
	protected $current = false;
	protected $pageName;

	public function __construct( CachedThreadData $source, $pageName ) {
		$this->source = $source;
		$this->pageName = $pageName;
		$this->rewind();
	}

	public function current ( ) {
		return new LiquidThreadsApiImportTopic( $this->current );
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
			foreach( $this->source->getTopics() as $topicId ) {
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
			$this->current = $this->source->get( $topicId );
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
		$prevCount = $this->source->getSize();

		$params = array(
			'thpage' => $this->pageName,
		);

		if ( $this->maxLoadedId ) {
			$params['thstartid'] = $this->maxLoadedId;
		}

		$output = $this->source->getFromParams( $params );
		$this->maxLoadedId = max( $this->maxLoadedId, max( array_keys( $output ) ) );

		return ( $this->source->getSize() > $prevCount );
	}
}

class LiquidThreadsApiReplyIterator implements Iterator {
	protected $thread, $threadReplies, $threadData, $pageData, $replyIndex;

	public function __construct( LiquidThreadsApiThread $thread, CachedThreadData $threadData, CachedPageData $pageData ) {
		$this->thread = $thread;
		$this->threadData = $threadData;
		$this->pageData = $pageData;
		$this->replyIndex = 0;
		$this->threadReplies = array_values( $this->thread->getReplies() );
	}

	public function current ( ) {
		$replyId = $this->threadReplies[$this->replyIndex]['id'];
		$replyData = $this->threadData->get( $replyId );
		return new LiquidThreadsApiImportPost( $replyData, $this->pageData, $this->threadData );
	}

	public function key ( ) {
		return $this->replyIndex;
	}

	public function next ( ) {
		$this->replyIndex++;
	}

	public function rewind ( ) {
		$this->lastOffset = 0;
	}

	public function valid ( ) {
		return $this->replyIndex < count( $this->threadReplies );
	}
}
