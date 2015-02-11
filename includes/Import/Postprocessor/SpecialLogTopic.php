<?php

namespace Flow\Import\Postprocessor;

use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Import\IImportHeader;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\PageImportState;
use Flow\Import\TopicImportState;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use ManualLogEntry;
use User;

/**
 * Records topic imports to Special:Log.
 */
class SpecialLogTopic implements PostProcessor {
	/**
	 * @var bool[string] Map from topic alphadecimal uuid to boolean true indicating topics
	 *  with new posts since last commit.
     */
	protected $imported = array();

	/**
	 * @var User The user to attribute logs to
	 */
	protected $user;

	public function __construct( User $user ) {
		$this->user = $user;
	}

	public function afterHeaderImported( PageImportState $state, IImportHeader $topic ) {
		// nothing to do
	}

	public function afterPostImported( TopicImportState $state, IImportPost $post, UUID $newPostId ) {
		$alpha = $state->topicWorkflow->getId()->getAlphadecimal();
		$this->imported[$alpha] = true;
	}

	public function afterTopicImported( TopicImportState $state, IImportTopic $topic ) {
		$alpha = $state->topicWorkflow->getId()->getAlphadecimal();
		// If no posts were imported within this topic, then nothing to report
		if ( isset( $this->imported[$alpha] ) ) {
			$logEntry = new ManualLogEntry( "import", $topic->getLogType() );
			$logEntry->setTarget( $state->topicWorkflow->getOwnerTitle() );
			$logEntry->setPerformer( $this->user );
			$logEntry->setParameters( array(
				'topic' => $state->topicWorkflow->getArticleTitle()->getPrefixedText(),
			) + $topic->getLogParameters() );
			$logEntry->setComment( "@todo create i18n message for reason" );
			$logEntry->insert();
		}

		$this->imported = array();
	}

	public function importAborted() {
		// drop anything queue'd up
		$this->imported = array();
	}
}
