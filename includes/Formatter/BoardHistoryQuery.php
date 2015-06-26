<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use MWExceptionHandler;
use Flow\Model\AbstractRevision;
use Flow\Data\ManagerGroup;
use Flow\Repository\TreeRepository;
use Flow\FlowActions;

class BoardHistoryQuery extends AbstractQuery {

	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * @param ManagerGroup $storage
	 * @param TreeRepository $treeRepo
	 * @param FlowActions $actions
	 */
	public function __construct(
		ManagerGroup $storage,
		TreeRepository $treeRepo,
		FlowActions $actions )
	{
		parent::__construct( $storage, $treeRepo );
		$this->actions = $actions;
	}

	/**
	 * @param UUID $workflowId
	 * @param int $limit
	 * @param UUID|null $offset
	 * @param string $direction 'rev' or 'fwd'
	 * @return FormatterRow[]
	 */
	public function getResults( UUID $workflowId, $limit = 50, UUID $offset = null, $direction = 'fwd' ) {
		// Load the history
		$history = $this->storage->find(
			'BoardHistoryEntry',
			array( 'topic_list_id' => $workflowId ),
			array(
				'sort' => 'rev_id',
				'order' => 'DESC',
				'limit' => $limit,
				'offset-id' => $offset,
				'offset-dir' => $direction,
				'offset-include' => false,
				'offset-elastic' => false,
			)
		);

		if ( !$history ) {
			return array();
		}

		// fetch any necessary metadata
		$this->loadMetadataBatch( $history );
		// build rows with the extra metadata
		$results = array();
		foreach ( $history as $revision ) {
			try {
				if ( $this->excludeFromHistory( $revision ) ) {
					continue;
				}
				$result = $this->buildResult( $revision, 'rev_id' );
			} catch ( FlowException $e ) {
				$result = false;
				MWExceptionHandler::logException( $e );
			}
			if ( $result ) {
				$results[] = $result;
			}
		}

		return $results;
	}

	/**
	 * @param AbstractRevision $revision
	 * @return mixed|null
	 */
	private function excludeFromHistory( $revision ) {
		return $this->actions->getValue( $revision->getChangeType(), 'exclude_from_history' );
	}
}
