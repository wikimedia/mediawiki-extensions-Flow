<?php

namespace Flow\Tests;

use FlowHooks;
use Flow\Model\UUID;
use RecentChange;

class HookTest extends \MediaWikiTestCase {
	static public function onIRCLineURLProvider() {
		// specific uuid's dont mean anything, just repeatability
		$workflowAlpha = 'rs2l7n89pmch81qy';
		$postAlpha = 'rs2l7n8ctv7rwf6i';
		$topicAlpha = 'rs2l7n8dra7r9a22';
		$revisionAlpha = 'rs2l7n89ab7rdd0f';
		$prevRevisionAlpha = 'rs2l7k73abd02ee2';

		$basicPost = array(
			'block' => 'topic',
			'revision_type' => 'PostRevision',
			'revision' => $revisionAlpha,
			'prev_revision' => $prevRevisionAlpha,
			'workflow' => $workflowAlpha,
			'post' => $postAlpha,
			'topic' => $topicAlpha,
		);

		$basicHeader = array(
			'block' => 'header',
			'revision_type' => 'Header',
			'revision' => $revisionAlpha,
			'prev_revision' => $prevRevisionAlpha,
			'workflow' => $workflowAlpha,
			'content' => 'foo bar baz...',
		);

		return array(
			array(
				// test message
				'Freshly created topic',
				// flow-workflow-change attribute within rc_params
				$basicPost + array(
					'action' => 'new-post',
				),
				// expected url
				'?title=Main_Page&workflow=rs2l7n89pmch81qy&action=history',
				// expected query
				''
			),

			array(
				'Reply to topic',
				$basicPost + array(
					'action' => 'reply',
				),
				'?title=Main_Page&workflow=rs2l7n89pmch81qy&action=history',
				'',
			),

			array(
				'Edit topic title',
				$basicPost + array(
					'action' => 'edit-title',
				),
				'?title=Main_Page&workflow=rs2l7n89pmch81qy&topic_newRevision=rs2l7n89ab7rdd0f&action=compare-post-revisions',
				'',
			),

			array(
				'Edit post',
				$basicPost + array(
					'action' => 'edit-post',
				),
				'?title=Main_Page&workflow=rs2l7n89pmch81qy&topic_newRevision=rs2l7n89ab7rdd0f&action=compare-post-revisions',
				'',
			),

			array(
				'Edit board header',
				$basicHeader + array(
					'action' => 'edit-header',
				),
				'?title=Main_Page&workflow=rs2l7n89pmch81qy&header_newRevision=rs2l7n89ab7rdd0f&action=compare-header-revisions',
				'',
			),

			array(
				'Moderate a post',
				$basicPost + array(
					'action' => 'delete-post',
				),
				'?title=Main_Page&workflow=rs2l7n89pmch81qy&topic_postId=rs2l7n8ctv7rwf6i&action=history',
				'',
			),

			array(
				'Moderate a topic',
				$basicPost + array(
					'action' => 'hide-topic',
				),
				'?title=Main_Page&workflow=rs2l7n89pmch81qy&topic_postId=rs2l7n8ctv7rwf6i&action=history',
				'',
			),
		);
	}

	/**
	 * @dataProvider onIRCLineUrlProvider
	 */
	public function testOnIRCLineUrl( $message, array $change, $expectedUrl, $expectedQuery ) {
		$rc = new RecentChange;
		$rc->mAttribs = array(
			'rc_namespace' => 0,
			'rc_title' => 'Main Page',
			'rc_source' => \Flow\Data\RecentChanges::SRC_FLOW,
			'rc_params' => serialize( array(
				'flow-workflow-change' => $change
			) ),
		);
		$url = 'unset';
		$query = 'unset';
		$this->assertTrue( FlowHooks::onIRCLineURL( $url, $query, $rc ) );

		$this->assertStringEndsWith( $expectedUrl, $url, $message );
		$this->assertEquals( $expectedQuery, $query, $message );
	}
}
