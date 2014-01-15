<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Flow\Import\ImportException;
use Flow\Import\IImportPost;
use Flow\Import\IImportSource;
use Flow\Import\IImportTopic;
use Http;

class ImportSource implements IImportSource {
	protected $api;
	protected $pageName;
	protected $threadData;
	protected $pageData;
	protected $maxId;

	public function __construct( $apiUrl, $pageName ) {
		$this->api = new ApiBackend( $apiUrl );
		$this->pageName = $pageName;
		$this->maxId = 0;

		$this->threadData = new CachedThreadData( $this->api );
		$this->pageData = new CachedPageData( $this->api );
	}

	public function getTopics() {
		return new TopicIterator( $this->threadData, $this->pageName );
	}


	public function getTopicSummary( IImportTopic $topic ) {
		if ( $topic->hasSummary() ) {
			return new ImportSummary( $topic->getSummaryId(), $this->pageData );
		} else {
			return false;
		}
	}


	public function getTopicPosts( IImportTopic $topic ) {
		$topicPost = new ImportPost(
			$topic->getApiResponse(),
			$this->pageData,
			$this->threadData
		);

		return new ArrayIterator( array( $topicPost ) );
	}


	public function getPostReplies( IImportPost $post ) {
		return new ReplyIterator( $post, $this->threadData, $this->pageData );
	}

	public function getTopic( $id ) {
		$data = $this->getThreadData( $id );
		return new Topic( $source, $data );
	}

	public function getPost( $id ) {
		$data = $this->getThreadData( $id );
		return new Post( $source, $data );
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

class ApiBackend {
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
		$data = $this->apiCall( $params + $conds );

		if ( ! isset( $data['query']['threads'] ) ) {
			throw new ImportException( "Null response from API module" );
		}

		$firstThread = reset( $data['query']['threads'] );
		if ( ! isset( $firstThread['replies'] ) ) {
			throw new ImportException( "Foreign API does not support reply exporting" );
		}

		return $data['query']['threads'];
	}

	public function retrievePageData( array $pageids ) {
		if ( !$pageids ) {
			throw new \MWException( 'At least one page id must be provided' );
		}
		$apiParams = array(
			'action' => 'query',
			'prop' => 'revisions',
			'pageids' => implode( '|', $pageids ),
			'rvprop' => 'timestamp|user|content',
			'format' => 'json',
		);

		$data = $this->apiCall( $apiParams );

		if ( ! isset( $data['query'] ) ) {
			throw new ImportException( "Null response from API module" );
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
