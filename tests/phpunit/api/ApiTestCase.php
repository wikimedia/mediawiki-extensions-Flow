<?php

namespace Flow\Tests\Api;

use ApiTestCase as BaseApiTestCase;
use Flow\Container;
use FlowHooks;
use TestUser;
use User;

/**
 * @group Flow
 * @group medium
 */
abstract class ApiTestCase extends BaseApiTestCase {
	protected function setUp() {
		$this->setMwGlobals( 'wgFlowOccupyPages', array(
			// For testing use; shared with browser tests
			'Talk:Flow QA',

			// Don't do any write operations on this.  It's intentionally left
			// blank for testing read operations on unused (but occupied) pages.
			'Talk:Intentionally blank',
		) );

		Container::reset();
		\Flow\Tests\FlowTestCase::useTestObjectsInContainer( $this );

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
			$user = self::$users['sysop']->user;
		}

		// reset flow state before each request
		FlowHooks::resetFlowExtension();
		Container::reset();
		\Flow\Tests\FlowTestCase::useTestObjectsInContainer( $this );
		$container = Container::getContainer();
		$container['user'] = $user;
		return parent::doApiRequest( $params, $session, $appendModule, $user );
	}

	/**
	 * Create a topic on a board using the default user
	 */
	protected function createTopic( $return = '', $topicTitle = 'Hi there!' ) {
		$data = $this->doApiRequest( array(
			'page' => 'Talk:Flow QA',
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'new-topic',
			'nttopic' => $topicTitle,
			'ntcontent' => '...',
		) );
		$this->assertTrue(
			// @todo we should return the new id much more directly than this
			isset( $data[0]['flow']['new-topic']['result']['topiclist']['roots'][0] ),
			'Api response must contain new topic id'
		);

		if ( $return === 'all' ) {
			return $data;
		} elseif ( $return === 'result' ) {
			return $data[0]['flow']['new-topic']['result']['topiclist'];
		} else {
			return $data[0]['flow']['new-topic']['result']['topiclist']['roots'][0];
		}
	}
}
