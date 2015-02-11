<?php

namespace Flow\Import\Postprocessor;

use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
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
	 * @var array A set of topics queue'd up to be logged once
	 *  the current db transaction completes.
	 */
	protected $queue = array();

	/**
	 * @var array Map from topic alphadecimal uuid to a list of post UUID's imported
	 *  in this run.
         */
	protected $imported = array();

	/**
	 * @var User The user to attribute logs to
	 */
	protected $user;

	public function __construct( User $user ) {
		$this->user = $user;
	}

	public function afterTopicImported( TopicImportState $state, IImportTopic $topic ) {
		$alpha = $state->topicWorkflow->getId()->getAlphadecimal();
		// If no posts were imported within this topic, then nothing to report
		if ( !isset( $this->imported[$alpha] ) ) {
			return;
		}

		// queue up topics to be inserted to special:log
		$this->queue[] = array(
			$state->topicWorkflow,
			$topic,
		);

		// reset the import state
		unset( $this->imported[$alpha] );
	}

	public function afterPostImported( TopicImportState $state, IImportPost $post, UUID $newPostId ) {
		$alpha = $state->topicWorkflow->getId()->getAlphadecimal();
		$this->imported[$alpha] = true;
	}

	public function afterTalkpageImported() {
		// this method is slightly mislabeled. It is called
		// just after every commit which is done once for
		// the board header and once for each contained topic.
		foreach ( $this->queue as $args ) {
			$this->logTopicImport( $args[0], $args[1] );
		}
		$this->queue = array();
	}

	public function talkpageImportAborted() {
		// drop anything queue'd up
		$this->queue = array();
		$this->imported = array();
	}

	protected function logTopicImport( Workflow $workflow, IImportTopic $topic ) {
		$logEntry = new ManualLogEntry( "import", $topic->getLogType() );
		$logEntry->setTarget( $workflow->getOwnerTitle() );
		$logEntry->setPerformer( $this->user );
		$logEntry->setParameters( array(
			'topic' => $workflow->getArticleTitle()->getPrefixedText(),
		) + $topic->getLogParameters() );
		$logEntry->setComment( "@todo create i18n message for reason" );
		$logEntry->insert();
	}
}
