<?php

namespace Flow\Tests;

use Flow\SpamFilter\AbuseFilter;
use Flow\Model\PostRevision;
use Title;
use User;

/**
 * @group Database
 * @group Flow
 */
class AbuseFilterTest extends PostRevisionTestCase {
	/**
	 * @var AbuseFilter
	 */
	protected $spamFilter;

	/**
	 * @var array
	 */
	protected $tablesUsed = array( 'abuse_filter', 'abuse_filter_action', 'abuse_filter_history', 'abuse_filter_log' );

	protected $filters = array(
		// no CSS screen hijack
		'(new_wikitext rlike "position\s*:\s*(fixed|absolute)|style\s*=\s*\"[a-z0-9:;\s]*&|z-index\s*:\s*\d|\|([4-9]\d{3}|\d{5,})px")' => 'disallow',
	);

	public function spamProvider() {
		return array(
			array(
				// default new topic title revision - no spam
				$this->generateObject(),
				null,
				true
			),
			array(
				// revision with spam
				// https://www.mediawiki.org/w/index.php?title=Talk:Sandbox&workflow=050bbdd07b64a1c028b2782bcb087b42#flow-post-050bbdd07b70a1c028b2782bcb087b42
				$this->generateObject( array( 'rev_content' => '<div style="background: yellow; position: fixed; top: 0; left: 0; width: 3000px; height: 3000px; z-index: 1111;">test</div>', 'rev_flags' => 'html' ) ),
				null,
				false
			),
		);
	}

	/**
	 * @dataProvider spamProvider
	 */
	public function testSpam( PostRevision $newRevision, PostRevision $oldRevision = null, $expected ) {
		$title = Title::newFromText( 'UTPage' );

		$status = $this->spamFilter->validate( $newRevision, $oldRevision, $title );
		$this->assertEquals( $expected, $status->isOK() );
	}

	protected function setUp() {
		parent::setUp();

		global $wgFlowAbuseFilterGroup,
			$wgFlowAbuseFilterEmergencyDisableThreshold,
			$wgFlowAbuseFilterEmergencyDisableCount,
			$wgFlowAbuseFilterEmergencyDisableAge;

		$user = User::newFromName( 'UTSysop' );

		$this->spamFilter = new AbuseFilter( $user, $wgFlowAbuseFilterGroup );
		if ( !$this->spamFilter->enabled() ) {
			$this->markTestSkipped( 'AbuseFilter not enabled' );
		}

		$this->spamFilter->setup( array(
			'threshold' => $wgFlowAbuseFilterEmergencyDisableThreshold,
			'count' => $wgFlowAbuseFilterEmergencyDisableCount,
			'age' => $wgFlowAbuseFilterEmergencyDisableAge,
		) );

		foreach ( $this->filters as $pattern => $action ) {
			$this->createFilter( $pattern, $action );
		}
	}

	protected function tearDown() {
		foreach ( $this->tablesUsed as $table ) {
			$this->db->delete( $table, '*', __METHOD__ );
		}
	}

	/**
	 * Inserts a filter into stub database.
	 *
	 * @param string $pattern
	 * @param string[optional] $action
	 */
	protected function createFilter( $pattern, $action = 'disallow' ) {
		global $wgFlowAbuseFilterGroup;
		$user = User::newFromName( 'UTSysop' );

		$this->db->replace(
			'abuse_filter',
			array( 'af_id' ),
			array(
//				'af_id',
				'af_pattern' => $pattern,
				'af_user' => $user->getId(),
				'af_user_text' => $user->getName(),
				'af_timestamp' => wfTimestampNow(),
				'af_enabled' => 1,
				'af_comments' => null,
				'af_public_comments' => 'Test filter',
				'af_hidden' => 0,
				'af_hit_count' => 0,
				'af_throttled' => 0,
				'af_deleted' => 0,
				'af_actions' => $action,
				'af_group' => $wgFlowAbuseFilterGroup,
			),
			__METHOD__
		);

		$this->db->replace(
			'abuse_filter_action',
			array( 'afa_filter' ),
			array(
				'afa_filter' => $this->db->insertId(),
				'afa_consequence' => $action,
				'afa_parameters' => '',
			),
			__METHOD__
		);
	}
}
