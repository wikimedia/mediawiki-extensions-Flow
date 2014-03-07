<?php

namespace Flow\Block;

use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\RevisionActionPermissions;
use Flow\View\History\History;
use Flow\View\History\HistoryRenderer;
use Flow\Container;
use Flow\Templating;
use Flow\Exception\InvalidDataException;
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
		$templating->getOutput()->addModuleStyles( array( 'ext.flow.history' ) );
		$templating->getOutput()->addModules( array( 'ext.flow.history' ) );
		$tplVars = array(
			'title' => wfMessage( 'flow-board-history', $this->workflow->getArticleTitle() )->escaped(),
			'historyExists' => false,
		);

		$history = $this->loadBoardHistory();

		if ( $history ) {
			$tplVars['historyExists'] = true;
			$tplVars['history'] = new History( $history );
			$tplVars['historyRenderer'] = new HistoryRenderer( $templating, $this );
		}

		$templating->render( "flow:board-history.html.php", $tplVars );
	}

	public function renderAPI( Templating $templating, array $options ) {
		$output = array(
			'type' => 'board-history',
			'*' =>  $this->loadBoardHistory(),
		);

		$output = array(
			'_element' => 'board-history',
			0 => $output,
		);

		return $output;
	}

	protected function loadBoardHistory() {
		$history = $this->storage->find(
			'BoardHistoryEntry',
			array( 'topic_list_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 300 )
		);

		if ( !$history ) {
			return array();
		}

		// get rid of history entries user doesn't have sufficient permissions for
		foreach ( $history as $i => $revision ) {
			/** @var PostRevision|Header $revision */

			// only check against the specific revision, ignoring the most recent
			if ( !$this->permissions->isAllowed( $revision, 'post-history' ) ) {
				unset( $history[$i] );
			}
		}

		return $history;
	}

	public function getName() {
		return 'board-history';
	}

}
