<?php

namespace Flow\Import\LiquidThreadsApi;

use ApiBase;
use ApiMain;
use Exception;
use FauxRequest;
use Flow\Container;
use Flow\Import\ImportException;
use Flow\Import\IImportSource;
use Flow\Import\ApiNullResponseException;
use Http;
use RequestContext;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use UsageException;
use User;

class ImportSource implements IImportSource {
	// Thread types defined by LQT which are returned via api
	const THREAD_TYPE_NORMAL = 0;
	const THREAD_TYPE_MOVED = 1;
	const THREAD_TYPE_DELETED = 2;
	const THREAD_TYPE_HIDDEN = 4;

	/**
	 * @var ApiBackend
	 */
	protected $api;

	/**
	 * @var string
	 */
	protected $pageName;

	/**
	 * @var CachedThreadData
	 */
	protected $threadData;

	/**
	 * @var CachedPageData
	 */
	protected $pageData;

	/**
	 * @param ApiBackend $apiBackend
	 * @param string $pageName
	 */
	public function __construct( ApiBackend $apiBackend, $pageName ) {
		$this->api = $apiBackend;
		$this->pageName = $pageName;

		$this->threadData = new CachedThreadData( $this->api );
		$this->pageData = new CachedPageData( $this->api );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeader() {
		$controller = Container::get( 'occupation_controller' );

		return new ImportHeader( $this->api, $this, $this->pageName, $controller->getTalkpageManager() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTopics() {
		return new TopicIterator( $this, $this->threadData, $this->pageName );
	}

	/**
	 * @param integer $id
	 * @return ImportTopic|null
	 */
	public function getTopic( $id ) {
		$data = $this->threadData->get( $id );
		switch ( $data['type'] ) {
		// Standard thread
		case self::THREAD_TYPE_NORMAL:
			return new ImportTopic( $this, $data );

		// The topic no longer exists at the queried location, but
		// a stub was left behind pointing to it. This modified
		// version of ImportTopic gracefully adjusts the #REDIRECT
		// into a template to keep a similar output to lqt.
		case self::THREAD_TYPE_MOVED:
			return new MovedImportTopic( $this, $data );

		// To get these back from the api we would have to send the `showdeleted`
		// query param.  As we are not requesting them, just ignore for now.
		case self::THREAD_TYPE_DELETED:
			return null;

		// Was assigned but never used by LQT.
		case self::THREAD_TYPE_HIDDEN:
			return null;
		}
	}

	/**
	 * @param integer $id
	 * @return ImportPost
	 */
	public function getPost( $id ) {
		return new ImportPost( $this, $this->threadData->get( $id ) );
	}

	/**
	 * @param integer $id
	 * @return array
	 */
	public function getThreadData( $id ) {
		if ( is_array( $id ) ) {
			return $this->threadData->getMulti( $id );
		} else {
			return $this->threadData->get( $id );
		}
	}

	/**
	 * @param integer[]|integer $pageIds
	 * @return array
	 */
	public function getPageData( $pageIds ) {
		if ( is_array( $pageIds ) ) {
			return $this->pageData->getMulti( $pageIds );
		} else {
			return $this->pageData->get( $pageIds );
		}
	}

	/**
	 * @param string $pageName
	 * @param integer $startId
	 * @return array
	 */
	public function getFromPage( $pageName, $startId = 0 ) {
		return $this->threadData->getFromPage( $pageName, $startId );
	}

	/**
	 * Gets a unique identifier for the wiki being imported
	 * @return string Usually either a string 'local' or an API URL
	 */
	public function getApiKey() {
		return $this->api->getKey();
	}

	/**
	 * Returns a key uniquely representing an object determined by arguments.
	 * Parameters: Zero or more strings that uniquely represent the object
	 * for this ImportSource
	 *
	 * @return string Unique key
	 */
	public function getObjectKey( /* $args */ ) {
		$components = array_merge(
			array( 'lqt-api', $this->getApiKey() ),
			func_get_args()
		);

		return implode( ':', $components );
	}
}

