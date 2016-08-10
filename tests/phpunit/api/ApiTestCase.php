<?php

namespace Flow\Tests\Api;

use ApiTestCase as BaseApiTestCase;
use Flow\Container;
use FlowHooks;
use ObjectCache;
use User;

/**
 * @group Flow
 * @group medium
 */
abstract class ApiTestCase extends BaseApiTestCase {
	protected $tablesUsed = array(
		'flow_ext_ref',
		'flow_revision',
		'flow_subscription',
		'flow_topic_list',
		'flow_tree_node',
		'flow_tree_revision',
		'flow_wiki_ref',
		'flow_workflow',
		'page',
		'revision',
		'text',
	);

	protected function setUp() {
		$this->setMwGlobals( 'wgNamespaceContentModels', array(
			NS_TALK => CONTENT_MODEL_FLOW_BOARD,
			NS_TOPIC => CONTENT_MODEL_FLOW_BOARD,
		) );

		parent::setUp();
		$this->setCurrentUser( self::$users['sysop']->getUser() );
	}

	protected function getEditToken( $user = null, $token = 'edittoken' ) {
		$tokens = $this->getTokenList( $user ?: self::$users['sysop'] );
		return $tokens[$token];
	}

	/**
	 * Set $user in the Flow container
	 * WARNING: This resets your container and
	 *          gets rid of anything you may have mocked.
	 * @param User $user
	 */
	protected function setCurrentUser( User $user ) {
		Container::reset();
		$container = Container::getContainer();
		$container['user'] = $user;
	}

	protected function doApiRequest(
		array $params,
		array $session = null,
		$appendModule = false,
		User $user = null
	) {
		// reset flow state before each request
		FlowHooks::resetFlowExtension();
		return parent::doApiRequest( $params, $session, $appendModule, $user );
	}

	/**
	 * Create a topic on a board using the default user
	 */
	protected function createTopic( $topicTitle = 'Hi there!' ) {
		$data = $this->doApiRequest( array(
			'page' => 'Talk:Flow QA',
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'new-topic',
			'nttopic' => $topicTitle,
			'ntcontent' => '...',
		) );

		$this->assertTrue(
			isset( $data[0]['flow']['new-topic']['committed']['topiclist']['topic-id'] ),
			'Api response must contain new topic id'
		);

		return $data[0]['flow']['new-topic']['committed']['topiclist'];
	}

	protected function expectCacheInvalidate() {
		$mock = $this->mockCache();

		$mock->expects( $this->never() )->method( 'set' );
		$mock->expects( $this->never() )->method( 'add' );
		$mock->expects( $this->atLeastOnce() )->method( 'delete' );

		return $mock;
	}

	protected function mockCache() {
		global $wgFlowCacheTime;
		Container::reset();
		$container = Container::getContainer();

		$mock = $this->getMockBuilder( 'Flow\Data\BufferedCache' )
			->setConstructorArgs( array( ObjectCache::getMainWANInstance(), $container['db.factory'], $wgFlowCacheTime ) )
			->enableProxyingToOriginalMethods()
			->getMock();

		$container->extend( 'flowcache', function ( $cache, $container ) use ( $mock ) {
			return $mock;
		} );

		return $mock;
	}
}
