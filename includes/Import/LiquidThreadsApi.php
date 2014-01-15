<?php

namespace Flow\Import;

use Http;
use Iterator;

class LiquidThreadsApiImportSource implements ImportSource {
	protected $apiUrl;
	protected $pageName;
	protected $threadData = array();
	protected $topics = array();
	protected $maxId;

	public function __construct( $apiUrl, $pageName ) {
		$this->apiUrl = $apiUrl;
		$this->pageName = $pageName;
		$this->maxId = 0;
	}

	public function getTopics() {
		return new LiquidThreadsApiTopicIterator( $this );
	}


	public function getTopicSummary( ImportTopic $topic ) {
		return new LiquidThreadsApiTopicSummary( $this, $topic );
	}


	public function getTopicPosts( ImportTopic $topic ) {
		return new LiquidThreadsApiReplyIterator( $this, $topic->getThreadId() );
	}


	public function getPostReplies( ImportPost $post ) {
		return new LiquidThreadsApiReplyIterator( $this, $post->getThreadId() );
	}

	public function getNextTopic( $lastOffset ) {
		$loadedMore = true;

		while( $loadedMore ) {
			foreach( $this->topics as $id => $isTopic ) {
				if ( $id > $lastOffset ) {
					return $this->getThreadData( $id );
				}
			}

			$loadedMore = $this->loadMore();
		}

		return false;
	}

	public function getThreadData( $id ) {
		if ( ! is_array( $id ) ) {
			if ( ! isset( $this->threadData[$id] ) ) {
				die( var_dump( $this ) );
			}
			$this->ensureRetrieved( array( $id ) );
			return $this->threadData[$id];
		} else {
			$output = array();

			$this->ensureRetrieved( $id );

			foreach( $id as $retrId ) {
				$output[$retrId] = $this->getThreadData( $id );
			}

			return $output;
		}
	}

	public function ensureRetrieved( $ids ) {
		$missing = array_diff( $ids, array_keys( $this->threadData ) );

		if ( count( $missing ) > 0 ) {
			$this->retrieveById( $missing );
		}
	}

	protected function loadMore() {
		$prevCount = count( $this->threadData );

		$this->handleThreadData( $this->retrieveNext( $this->maxId ) );

		return ( count( $this->threadData ) > $prevCount );
	}

	protected function handleThreadData( array $importData, $updateMaxId = true ) {
		$importData = $importData['query']['threads'];

		$this->threadData += $importData;

		foreach( $importData as $thread ) {
			if ( $thread['parent'] === null ) {
				$this->topics[$thread['id']] = true;
			}

			if ( $updateMaxId && $thread['id'] > $this->maxId ) {
				$this->maxId = $thread['id'];
			}
		}
	}

	protected function retrieveById( array $ids ) {
		return $this->retrieve( array(
			'thid' => implode( '|', $ids ),
		) );
	}

	protected function retrieveNext( $offset = null ) {
		$params = array(
			'thpage' => $this->pageName,
		);

		if ( $offset ) {
			$params['thstartid'] = $offset;
		}

		return $this->retrieve( $params );
	}

	protected function retrieve( array $conds ) {
		$params = array(
			'action' => 'query',
			'list' => 'threads',
			'thprop' => 'id|subject|page|parent|ancestor|created|modified|author|summaryid|type|rootid|replies',
			'format' => 'json',
		);

		$url = wfAppendQuery( $this->apiUrl, $params );

		$result = Http::get( $url );

		return json_decode( $result, true );
	}
}

class LiquidThreadsApiTopicIterator implements Iterator {
	protected $lastOffset = 0;
	protected $source;

	public function __construct( LiquidThreadsApiImportSource $source ) {
		$this->source = $source;
	}

	public function current ( ) {
		return $this->source->getNextTopic( $this->lastOffset );
	}

	public function key ( ) {
		return $this->current()['id'];
	}

	public function next ( ) {
		$this->lastOffset = $this->key();
	}

	public function rewind ( ) {
		$this->lastOffset = 0;
	}

	public function valid ( ) {
		return is_array( $this->current() );
	}
}

class LiquidThreadsApiReplyIterator implements Iterator {
	protected $source, $threadId, $replyIds, $replyIndex;

	public function __construct( LiquidThreadsApiImportSource $source, $threadId ) {
		$this->source = $source;
		$this->threadId = $threadId;
		$this->replyIds = $this->source->getThreadData( $threadId )['replies'];
		$this->replyIndex = 0;
		$this->source->ensureRetrieved( $this->replyIds );
	}

	public function current ( ) {
		return $this->source->getThreadData( $this->key() );
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
		return $this->replyIndex < count( $this->replyIds );
	}
}

class LiquidThreadsApiImportTopic implements ImportTopic {
	protected $apiResponse;

	public function __construct( $apiResponse ) {
		$this->apiResponse = $apiResponse;
	}

	public function getSubject() {
		return $apiResponse['subject'];
	}


	public function getCreatedTimestamp() {
		return wfTimestamp( $apiResponse['created'] );
	}


	public function getModifiedTimestamp() {
		return wfTimestamp( $apiResponse['modified'] );
	}


	public function getCreator() {
		return User::newFromName( $apiResponse['author']['name'] );
	}
}

class LiquidThreadsApiImportPost implements ImportPost {
	protected $apiResponse;

	public function __construct( $apiResponse ) {
		$this->apiResponse = $apiResponse;
	}

	public function getCreatedTimestamp() {
		return wfTimestamp( $apiResponse['created'] );
	}


	public function getModifiedTimestamp() {
		return wfTimestamp( $apiResponse['modified'] );
	}


	public function getAuthor() {
		return User::newFromName( $apiResponse['author']['name'] );
	}


	public function getText() {
		throw new MWException( "Not implemented" );
	}
}

class LiquidThreadsApiImportSummary implements ImportSummary {
	public function getText() {

	}


	public function getUser() {

	}
}
