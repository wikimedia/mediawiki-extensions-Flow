<?php

namespace Flow\Import;

use ArrayIterator;
use Http;

class LiquidThreadsApiImportSource implements ImportSource {
	protected $api;
	protected $pageName;
	protected $threadData;
	protected $pageData;
	protected $maxId;

	public function __construct( $apiUrl, $pageName ) {
		$this->api = new LiquidThreadsApiBackend( $apiUrl );
		$this->pageName = $pageName;
		$this->maxId = 0;

		$this->threadData = new CachedThreadData( $this->api );
		$this->pageData = new CachedPageData( $this->api );
	}

	public function getTopics() {
		return new LiquidThreadsApiTopicIterator( $this->threadData, $this->pageName );
	}


	public function getTopicSummary( ImportTopic $topic ) {
		if ( $topic->hasSummary() ) {
			return new LiquidThreadsApiImportSummary( $topic->getSummaryId(), $this->pageData );
		} else {
			return false;
		}
	}


	public function getTopicPosts( ImportTopic $topic ) {
		$topicPost = new LiquidThreadsApiImportPost(
			$topic->getApiResponse(),
			$this->pageData,
			$this->threadData
		);

		return new ArrayIterator( array( $topicPost ) );
	}


	public function getPostReplies( ImportPost $post ) {
		return new LiquidThreadsApiReplyIterator( $post, $this->threadData, $this->pageData );
	}

	public function getTopic( $id ) {
		$data = $this->getThreadData( $id );
		return new LiquidThreadsApiTopic( $source, $data );
	}

	public function getPost( $id ) {
		$data = $this->getThreadData( $id );
		return new LiquidThreadsApiPost( $source, $data );
	}

	public function getThreadData( $id ) {
		if ( ! is_array( $id ) ) {
			return $this->threadData->get( $id );
		} else {
			return $this->threadData->getMulti( $id );
		}
	}

	public function getPageData( $pageids ) {
		if ( ! is_array( $pageids ) ) {
			return $this->pageData->get( $pageids );
		} else {
			return $this->pageData->getMulti( $pageids );
		}
	}
}

class LiquidThreadsApiBackend {
	public function __construct( $apiUrl ) {
		$this->apiUrl = $apiUrl;
	}

	public function retrieveThreadData( array $conds ) {
		$params = array(
			'action' => 'query',
			'list' => 'threads',
			'thprop' => 'id|subject|page|parent|ancestor|created|modified|author|summaryid|type|rootid|replies',
			'format' => 'json',
		);
		$data = $this->apiCall( $params );

		return $data['query']['threads'];
	}

	public function retrievePageData( array $pageids ) {
		$apiParams = array(
			'action' => 'query',
			'prop' => 'revisions',
			'pageids' => implode( '|', $pageids ),
			'rvprop' => 'timestamp|user|content',
			'format' => 'json',
		);

		$data = $this->apiCall( $apiParams );

		if ( ! isset( $data['query'] ) ) {
			die( var_dump( wfBacktrace(), $pageids ) );
		}

		$pages = $data['query']['pages'];

		return $pages;
	}

	public function apiCall( array $params ) {
		$url = wfAppendQuery( $this->apiUrl, $params );

		$result = Http::get( $url );

		return json_decode( $result, true );
	}
}
