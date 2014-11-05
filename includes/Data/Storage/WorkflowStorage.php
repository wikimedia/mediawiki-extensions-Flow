<?php

namespace Flow\Data\Storage;

/**
 * Abstract storage implementation for models extending from AbstractRevision
 */
class WorkflowStorage extends BasicDbStorage {
	/**
	 * {@inheritDoc}
	 */
	protected $obsoleteUpdateColumns = array( 'workflow_user_id', 'workflow_user_ip', 'workflow_user_wiki' );
}
