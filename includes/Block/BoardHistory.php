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
		$output = $templating->getOutput();
		$output->addModuleStyles( array( 'ext.flow.history' ) );
		$output->addModules( array( 'ext.flow.history' ) );

		$title = wfMessage( 'flow-board-history', $this->workflow->getArticleTitle() )->escaped();
		$output->setHtmlTitle( $title );
		$output->setPageTitle( $title );

		if ( $this->workflow->isNew() ) {
			$output->addWikiMsg( 'flow-board-history-empty' );
			return;
		}

		// @todo To turn this into a reasonable json api we need the query
		// results to be more directly serializable.
		$lines = array();
		$history = $this->loadBoardHistory();
		$formatter = Container::get( 'board-history.formatter' );
		$ctx = \RequestContext::getMain();
		foreach ( $history as $row ) {
			$res = $formatter->format( $row, $ctx );
			if ( $res !== false ) {
				$lines[] = $res;
			}
		}

		$templating->render( "flow:board-history.html.php", array(
			'lines' => $lines,
			'historyExists' => count( $lines ) > 0
		) );
	}

	public function renderAPI( Templating $templating, array $options ) {
		if ( $this->workflow->isNew() ) {
			return array(
				'type' => $this->getName(),
				'revisions' => array(),
				'links' => array(
				),
			);
		}

		$history = $this->loadBoardHistory();
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

	protected function loadBoardHistory() {
		return Container::get( 'query.board-history' )->getResults( $this->workflow );
	}

	public function getName() {
		return 'board-history';
	}

}
