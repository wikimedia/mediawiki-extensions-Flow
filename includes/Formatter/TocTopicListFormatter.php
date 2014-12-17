<?php

namespace Flow\Formatter;

use Flow\Data\Pager\PagerPage;
use Flow\Model\Workflow;
use Flow\Templating;

// The output of this is a strict subset of TopicListFormatter.
// Anything accessible from the output of this should be accessible with the same path
// from the output of TopicListFormatter.  However, this output is much more minimal.
class TocTopicListFormatter extends BaseTopicListFormatter {
	/**
	 * @var Templating
	 */
	protected $templating;

	public function __construct( Templating $templating ) {
		$this->templating = $templating;
	}

	/**
	 * Formats the response
	 *
	 * @param Workflow $listWorkflow Workflow corresponding to board/list of topics
	 * @param array $topicRootRevisionsByWorkflowId Associative array mapping topic ID (in alphadecimal form)
	 *  to PostRevision for the topic root.
	 * @param array $workflowsByWorkflowId Associative array mapping topic ID (in alphadecimal form) to
	 *  workflow
	 * @param PagerPage $page page from query, to support pagination
	 *
	 * @return array Array formatted for response
	 */
	public function formatApi( Workflow $listWorkflow, $topicRootRevisionsByWorkflowId, $workflowsByWorkflowId, PagerPage $page ) {
		$result = $this->buildEmptyResult( $listWorkflow );

		foreach ( $topicRootRevisionsByWorkflowId as $topicId => $postRevision ) {
			$result['roots'][] = $topicId;
			$revisionId = $postRevision->getRevisionId()->getAlphadecimal();
			$result['posts'][$topicId] = array( $revisionId );

			$contentFormat = 'plaintext';

			$workflow = $workflowsByWorkflowId[$topicId];

			$result['revisions'][$revisionId] = array(
				// Keep this as a minimal subset of
				// RevisionFormatter->formatApi, and keep the same content
				// format for topic titles as specified in that class for
				// topic titles.

				'content' => array(
					'content' => $this->templating->getContent(
						$postRevision,
						$contentFormat
					),
					'format' => $contentFormat,
				),
				'last_updated' => $workflow->getLastModifiedObj()->getTimestamp() * 1000,
			);
		}

		$pagingOption = $page->getPagingLinksOptions();
		$result['links']['pagination'] = $this->buildPaginationLinks(
			$listWorkflow,
			$pagingOption
		);

		return $result;
	}
}
