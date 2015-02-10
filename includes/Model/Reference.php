<?php

namespace Flow\Model;

use Flow\Exception\InvalidReferenceException;
use Title;

abstract class Reference {
	const TYPE_LINK = 'link';

	/**
	 * @var UUID
	 */
	protected $workflowId;

	/**
	 * @var Title
	 */
	protected $srcTitle;

	/**
	 * @var String
	 */
	protected $objectType;

	/**
	 * @var UUID
	 */
	protected $objectId;

	/**
	 * @var string
	 */
	protected $type;

	protected $validTypes = array( self::TYPE_LINK );

	/**
	 * Standard constructor. Called from subclasses only
	 *
	 * @param UUID   $srcWorkflow Source Workflow's ID
	 * @param Title  $srcTitle    Title of the Workflow from which this reference comes.
	 * @param String $objectType  Output of getRevisionType for the AbstractRevision that this reference comes from.
	 * @param UUID   $objectId    Unique identifier for the revisioned object containing the reference.
	 * @param string $type        The type of reference
	 * @throws InvalidReferenceException
	 */
	protected function __construct( UUID $srcWorkflow, Title $srcTitle, $objectType, UUID $objectId, $type ) {
		$this->workflowId = $srcWorkflow;
		$this->objectType = $objectType;
		$this->objectId = $objectId;
		$this->type = $type;
		$this->srcTitle = $srcTitle;

		if ( !in_array( $type, $this->validTypes ) ) {
			throw new InvalidReferenceException(
				"Invalid type $type specified for reference " . get_class( $this )
			);
		}
	}

	/**
	 * Gives the UUID of the source Workflow
	 *
	 * @return UUID
	 */
	public function getWorkflowId() {
		return $this->workflowId;
	}

	/**
	 * Gives the Title from which this Reference comes.
	 *
	 * @return Title
	 */
	public function getSrcTitle() {
		return $this->srcTitle;
	}

	/**
	 * Gives the object type of the source object.
	 */
	public function getObjectType() {
		return $this->objectType;
	}

	/**
	 * Gives the UUID of the source object
	 *
	 * @return UUID
	 */
	public function getObjectId() {
		return $this->objectId;
	}

	/**
	 * Gives the type of Reference
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns the storage row for this Reference.
	 * For this abstract reference, only partial.
	 *
	 * @return array
	 */
	public function getStorageRow() {
		return array(
			'ref_src_workflow_id' => $this->workflowId->getAlphadecimal(),
			'ref_src_namespace' => $this->srcTitle->getNamespace(),
			'ref_src_title' => $this->srcTitle->getDBkey(),
			'ref_src_object_type' => $this->objectType,
			'ref_src_object_id' => $this->objectId->getAlphadecimal(),
			'ref_type' => $this->type,
		);
	}

	/**
	 * @return string Unique string identifier for the target of this reference.
	 */
	abstract public function getTargetIdentifier();

	public function getIdentifier() {
		return $this->getType() . ':' . $this->getTargetIdentifier();
	}

	public function getUniqueIdentifier() {
		return 	$this->getSrcTitle() . '|' .
				$this->getObjectType() . '|' .
				$this->getObjectId()->getAlphadecimal() . '|' .
				$this->getIdentifier();
	}
}
