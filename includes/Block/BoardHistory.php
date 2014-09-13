<?php

namespace Flow\Block;

use Flow\RevisionActionPermissions;
use Flow\Container;
use Flow\Exception\DataModelException;

class BoardHistoryBlock extends AbstractBlock {

	/**
	 * @var RevisionActionPermissions $permissions Allows or denies actions to be performed
	 */
	protected $permissions;

	protected $supportedGetActions = array( 'history' );

	// @Todo - fill in the template names
	protected $templates = array(
		'history' => '',
	);

	public function init( $action, $user ) {
		parent::init( $action, $user );
		$this->permissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );
	}

	/**
	 * Board history is read-only block which should not invoke write action
	 */
	public function validate() {
		throw new DataModelException( __CLASS__ . ' should not invoke validate()', 'process-data' );
	}

	/**
	 * Board history is read-only block which should not invoke write action
	 */
	public function commit() {
		throw new DataModelException( __CLASS__ . ' should not invoke commit()', 'process-data' );
	}

	public function renderAPI( array $options ) {
		if ( $this->workflow->isNew() ) {
			return array(
				'type' => $this->getName(),
				'revisions' => array(),
				'links' => array(
				),
			);
		}

		$history = Container::get( 'query.board-history' )->getResults( $this->workflow );
		$formatter = Container::get( 'formatter.revision' );
		$formatter->setIncludeHistoryProperties( true );
		$ctx = \RequestContext::getMain();

		$revisions = array();
		foreach ( $history as $row ) {
			$serialized = $formatter->formatApi( $row, $ctx );
			if ( $serialized ) {
				$revisions[$serialized['revisionId']] = $serialized;
			}
		}

		return array(
			'type' => $this->getName(),
			'revisions' => $revisions,
			'links' => array(
			),
		);
	}

	public function getName() {
		return 'board-history';
	}
}
