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
		$history = $this->loadBoardHistory();
		$formatter = Container::get( 'formatter.revision' );
		$ctx = \RequestContext::getMain();

		$result = array();
		foreach ( $history as $row ) {
			$result[] = $formatter->formatApi( $row, $ctx );
		}

		return array(
			'_element' => 'board-history',
			0 => array(
				'type' => 'board-history',
				'*' => $result,
			),
		);
	}

	protected function loadBoardHistory() {
		return Container::get( 'board-history.query' )->getResults( $this->workflow );
	}

	public function getName() {
		return 'board-history';
	}

}
