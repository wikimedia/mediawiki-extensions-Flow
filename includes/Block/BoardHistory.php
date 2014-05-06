<?php

namespace Flow\Block;

use ApiResult;
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

	public function renderAPI( Templating $templating, ApiResult $result, array $options ) {
		if ( $this->workflow->isNew() ) {
<<<<<<< HEAD   (76e1f2 Merge "Revision single and diff view" into frontend-rewrite)
			return array(
				'type' => $this->getName(),
				'revisions' => array(),
				'links' => array(
=======
			$output = array(
				0 => array(
					'type' => 'board-history',
					'empty' => '',
>>>>>>> BRANCH (73a9af Merge "Catch and specially handle InvalidArgumentException")
				),
			);
			$result->setIndexedTagName( $output, 'board-history' );
			return $output;
		}

		$history = Container::get( 'query.board-history' )->getResults( $this->workflow );
		$formatter = Container::get( 'formatter.revision' );
		$formatter->setIncludeHistoryProperties( true );
		$ctx = \RequestContext::getMain();

<<<<<<< HEAD   (76e1f2 Merge "Revision single and diff view" into frontend-rewrite)
		$posts = $revisions = array();
=======
		$formatted = array();
>>>>>>> BRANCH (73a9af Merge "Catch and specially handle InvalidArgumentException")
		foreach ( $history as $row ) {
<<<<<<< HEAD   (76e1f2 Merge "Revision single and diff view" into frontend-rewrite)
			$serialized = $formatter->formatApi( $row, $ctx );
			$revisions[$serialized['revisionId']] = $serialized;
=======
			$formatted[] = $formatter->formatApi( $row, $ctx );
>>>>>>> BRANCH (73a9af Merge "Catch and specially handle InvalidArgumentException")
		}

<<<<<<< HEAD   (76e1f2 Merge "Revision single and diff view" into frontend-rewrite)
		return array(
			'type' => $this->getName(),
			'revisions' => $revisions,
			'links' => array(
=======
		$output = array(
			0 => array(
				'type' => 'board-history',
				'*' => $formatted,
>>>>>>> BRANCH (73a9af Merge "Catch and specially handle InvalidArgumentException")
			),
		);
<<<<<<< HEAD   (76e1f2 Merge "Revision single and diff view" into frontend-rewrite)
=======
		$result->setIndexedTagName( $output, 'board-history' );
		return $output;
	}

	protected function loadBoardHistory() {
		return Container::get( 'board-history.query' )->getResults( $this->workflow );
>>>>>>> BRANCH (73a9af Merge "Catch and specially handle InvalidArgumentException")
	}

	public function getName() {
		return 'board-history';
	}
}
