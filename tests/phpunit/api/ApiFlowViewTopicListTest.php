<?php

namespace Flow\Tests\Api;

use Title;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowViewTopicListTest extends ApiTestCase {
	const TITLE_PREFIX = 'VTL Test ';

	public function testTocOnly() {
		$topicData = array();
		for ( $i = 0; $i < 3; $i++ ) {
			$title = self::TITLE_PREFIX . $i;
			$topicData[$i]['response'] = $this->createTopic( 'result', $title );
			$topicData[$i]['id'] = $topicData[$i]['response']['roots'][0];
			$topicData[$i]['revisionId'] = $topicData[$i]['response']['posts'][$topicData[$i]['id']][0];
			$topicData[$i]['expectedRevision'] = array(
				'content' => array(
					'content' => $title,
					'format' => 'plaintext'
				),
			);
		}

		$flowQaTitle = Title::newFromText( 'Talk:Flow_QA' );

		$expectedCommonResponse = array(
			'flow' => array(
				'view-topiclist' => array(
					'result' => array(
						'topiclist' => array(
							'submitted' => array(
								'savesortby' => false,
								'offset-dir' => 'fwd',
								'offset-id' => null,
								'offset' => null,
								'limit' => 2,
								'render' => false,
								'toconly' => true
							),
							'errors' => array(),
							'type' => 'topiclist',
						),
					),
					'status' => 'ok',
				),
			),
		);

		$expectedEmptyPageResponse = array_merge_recursive( array(
			'flow' => array(
				'view-topiclist' => array(
					'result' => array(
						'topiclist' => array(
							'submitted' => array(
								'sortby' => 'newest',
							),
							'roots' => array(),
							'posts' => array(),
							'revisions' => array(),
							'links' => array(
								'pagination' => array(),
							),
						),
					),
				),
			),
		), $expectedCommonResponse );

		$actualEmptyPageResponse = $this->doApiRequest(
			array(
				'action' => 'flow',
				'page' => 'Talk:Intentionally blank',
				'submodule' => 'view-topiclist',
				'vtllimit' => 2,
				'vtltoconly' => true,
			)
		)[0];

		$this->assertEquals(
			$expectedEmptyPageResponse,
			$actualEmptyPageResponse,
			'TOC-only output for an empty, but occupied, Flow board'
		);

		$expectedNewestResponse = array_merge_recursive( array(
			'flow' => array(
				'view-topiclist' => array(
					'result' => array(
						'topiclist' => array(
							'submitted' => array(
								'sortby' => 'newest',
							),
							'sortby' => '',
							'roots' => array(
								$topicData[2]['id'],
								$topicData[1]['id'],
							),
							'posts' => array(
								$topicData[2]['id'] => $topicData[2]['response']['posts'][$topicData[2]['id']],
								$topicData[1]['id'] => $topicData[1]['response']['posts'][$topicData[1]['id']],
							),
							'revisions' => array(
								$topicData[2]['revisionId'] => $topicData[2]['expectedRevision'],
								$topicData[1]['revisionId'] => $topicData[1]['expectedRevision'],
							),
							'links' => array(
								'pagination' => array(
									'fwd' => array(
										'url' => $flowQaTitle->getLinkURL( array(
											'topiclist_offset-dir' => 'fwd',
											'topiclist_limit' => '2',
											'topiclist_offset-id' => $topicData[1]['id'],
										) ),
										'title' => 'fwd',
									),
								),
							),
						),
					)
				)
			)
		), $expectedCommonResponse );

		$actualNewestResponse = $this->doApiRequest(
			array(
				'action' => 'flow',
				'page' => 'Talk:Flow QA',
				'submodule' => 'view-topiclist',
				'vtllimit' => 2,
				'vtlsortby' => 'newest',
				'vtltoconly' => true,
			)
		)[0];

		$this->assertEquals(
			$expectedNewestResponse,
			$actualNewestResponse,
			'TOC-only output for "newest" order'
		);

		// Make it so update order is chronologically (1, 0, 2)
		// We then expect it to be returned reverse chronologically (2, 0)
		foreach ( array( 1, 0, 2) as $i ) {
			// It would be nice
			$replyResponse = $this->doApiRequest(
				array(
					'action' => 'flow',
					'page' => Title::makeTitle( NS_TOPIC, $topicData[$i]['id'] )->getPrefixedText(),
					'submodule' => 'reply',
					'token' => $this->getEditToken(),
					'repreplyTo' => $topicData[$i]['id'],
					'repcontent' => "Reply to topic $i",
				)
			)[0];

			$responseTopic = $replyResponse['flow']['reply']['result']['topic'];
			$topicRevisionId = $topicData[$i]['revisionId'];
			$newPostId = end( $responseTopic['revisions'][$topicRevisionId]['replies'] );
			$topicData[$i]['updateTimestamp'] = $responseTopic['revisions'][$newPostId]['timestamp'];
		}

		$expectedUpdatedResponse = array_merge_recursive( array(
			'flow' => array(
				'view-topiclist' => array(
					'result' => array(
						'topiclist' => array(
							'submitted' => array(
								'sortby' => 'updated',
							),
							'sortby' => 'updated',
							'roots' => array(
								$topicData[2]['id'],
								$topicData[0]['id'],
							),
							'posts' => array(
								$topicData[2]['id'] => $topicData[2]['response']['posts'][$topicData[2]['id']],
								$topicData[0]['id'] => $topicData[0]['response']['posts'][$topicData[0]['id']],
							),
							'revisions' => array(
								$topicData[2]['revisionId'] => $topicData[2]['expectedRevision'],
								$topicData[0]['revisionId'] => $topicData[0]['expectedRevision'],
							),
							'links' => array(
								'pagination' => array(
									'fwd' => array(
										'url' => $flowQaTitle->getLinkURL( array(
											'topiclist_offset-dir' => 'fwd',
											'topiclist_limit' => '2',
											'topiclist_offset' => $topicData[0]['updateTimestamp'],
											'topiclist_sortby' => 'updated',
										) ),
										'title' => 'fwd',
									),
								),
							),
						),
					)
				)
			)
		), $expectedCommonResponse );

		$actualUpdatedResponse = $this->doApiRequest(
			array(
				'action' => 'flow',
				'page' => 'Talk:Flow QA',
				'submodule' => 'view-topiclist',
				'vtllimit' => 2,
				'vtlsortby' => 'updated',
				'vtltoconly' => true,
			)
		)[0];

		$this->assertEquals(
			$expectedUpdatedResponse,
			$actualUpdatedResponse,
			'TOC-only output for "updated" order'
		);
	}
}
