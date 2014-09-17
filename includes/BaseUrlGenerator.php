<?php

namespace Flow;

use Flow\Data\ObjectManager;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\FlowException;
use Title;

/**
 * BaseUrlGenerator contains the logic for putting together the bits of url
 * data, without generating any specific urls.
 */
abstract class BaseUrlGenerator {
	/**
	 * @var OccupationController
	 */
	protected $occupationController;

	/**
	 * @var ObjectManager Workflow storage
	 */
	protected $storage;

	/**
	 * @var Workflow[] Cached array of already loaded workflows
	 */
	protected $workflows = array();

	/**
	 * @param OccupationController $occupationController
	 */
	public function __construct( OccupationController $occupationController ) {
		$this->occupationController = $occupationController;
	}

	/**
	 * @param Workflow $workflow
	 */
	public function withWorkflow( Workflow $workflow ) {
		$this->workflows[$workflow->getId()->getAlphadecimal()] = $workflow;
	}

	/**
	 * @param Workflow|UUID $workflow
	 * @return Workflow
	 * @throws InvalidDataException
	 * @throws InvalidInputException
	 */
	protected function resolveWorkflow( $workflow ) {
		if ( $workflow instanceof UUID ) {
			$alpha = $workflow->getAlphadecimal();
			if ( !isset( $this->workflows[$alpha] ) ) {
				throw new InvalidInputException( 'Unloaded workflow: ' . $alpha , 'invalid-workflow' );
			}
			$workflow = $this->workflows[$alpha];
		} elseif ( !$workflow instanceof Workflow ) {
			// otherwise calling a method on $workflow will fatal error
			throw new InvalidDataException( '$workflow is not UUID or Workflow instance' );
		}
		return $workflow;
	}

	/**
	 * @param Title|null $title
	 * @param UUID|null $workflowId
	 * @return Title
	 * @throws FlowException
	 */
	protected function resolveTitle( Title $title = null, UUID $workflowId = null ) {
		if ( $title !== null ) {
			return $title;
		}
		if ( $workflowId === null ) {
			throw new FlowException( 'No title or workflow given' );
		}

		$alpha = $workflowId->getAlphadecimal();
		if ( !isset( $this->workflows[$alpha] ) ) {
			throw new InvalidInputException( 'Unloaded workflow:' . $alpha, 'invalid-workflow' );
		}

		return $this->workflows[$alpha]->getArticleTitle();
	}
}
