<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Exception\InvalidInputException;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\RevisionActionPermissions;
use Flow\SpamFilter\Controller as SpamFilterController;
use IContextSource;

interface Block {
	/**
	 * @param IContextSource $context
	 * @param string $action
	 */
	function init( IContextSource $context, $action );

	/**
	 * Perform validation of data model
	 *
	 * @param array $data
	 * @return boolean True if data model is valid
	 */
	function onSubmit( array $data );

	/**
	 * Write updates to storage
	 */
	function commit();

	/**
	 * Render the API output of this Block.
	 * Templating is provided for convenience
	 *
	 * @param array $options
	 * @return array
	 */
	function renderApi( array $options );

	/**
	 * @return string Unique name among all blocks on an object
	 */
	function getName();

	/**
	 * @return UUID
	 */
	function getWorkflowId();
}

