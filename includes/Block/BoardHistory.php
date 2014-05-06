<?php

namespace Flow\Block;

use Flow\RevisionActionPermissions;
use Flow\Container;
use Flow\Templating;
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

	public function render( Templating $templating, array $options ) {
		throw new FlowException( 'deprecated' );
	}

	public function renderAPI( Templating $templating, array $options ) {
		if ( $this->workflow->isNew() ) {
			return array(
				'type' => $this->getName(),
				'revisions' => array(),
				'links' => array(
				),
			);
			$result->setIndexedTagName( $output, 'board-history' );
			return $output;
		}

		$history = Container::get( 'query.board-history' )->getResults( $this->workflow );
		$formatter = Container::get( 'formatter.revision' );
		$formatter->setIncludeHistoryProperties( true );
		$ctx = \RequestContext::getMain();

		$posts = $revisions = array();
		foreach ( $history as $row ) {
			$serialized = $formatter->formatApi( $row, $ctx );
			$revisions[$serialized['revisionId']] = $serialized;
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
