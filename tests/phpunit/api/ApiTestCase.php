<?php

namespace Flow\Tests\Api;

use ApiTestCase as BaseApiTestCase;
use Flow\Container;
use FlowHooks;
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

		Container::reset();
		parent::setUp();
	}

	protected function getEditToken( $user = null, $token = 'edittoken' ) {
		$tokens = $this->getTokenList( $user ?: self::$users['sysop'] );
		return $tokens[$token];
	}

	/**
	 * Ensures Flow is reset before passing control on
	 * to parent::doApiRequest. Defaults all requests to
	 * the sysop user if not specified.
	 */
	protected function doApiRequest(
		array $params,
		array $session = null,
		$appendModule = false,
		User $user = null
	) {
		if ( $user === null ) {
			$user = self::$users['sysop']->getUser();
		}

		// reset flow state before each request
		FlowHooks::resetFlowExtension();
		Container::reset();
		$container = Container::getContainer();
		$container['user'] = $user;
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
}
