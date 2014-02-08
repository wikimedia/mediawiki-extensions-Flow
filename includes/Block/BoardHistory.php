<?php

namespace Flow\Block;

use Flow\RevisionActionPermissions;
use Flow\View\History\History;
use Flow\View\History\HistoryRenderer;
use Flow\Container;
use Flow\Templating;
use Flow\Exception\InvalidDataException;

class BoardHistoryBlock extends AbstractBlock {

	/**
	 * @var RevisionActionPermissions $permissions Allows or denies actions to be performed
	 */
	protected $permissions;

	protected $supportedGetActions = array( 'board-history' );

	public function init( $action, $user ) {
		parent::init( $action, $user );
		$this->permissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );
	}

	/**
	 * Nothing to validate
	 */
	public function validate() {}

	/**
	 * Nothing to commit
	 */
	public function commit() {}

	public function render( Templating $templating, array $options ) {
		$templating->getOutput()->addModuleStyles( array( 'ext.flow.history' ) );
		$templating->getOutput()->addModules( array( 'ext.flow.history' ) );
		$tplVars = array(
			'title' => wfMessage( 'flow-board-history', $this->workflow->getArticleTitle() )->escaped(),
			'historyExists' => false,
		);

		$history = $this->filterBoardHistory( $this->loadBoardHistory() );

		if ( $history ) {
			$tplVars['historyExists'] = true;
			$tplVars['history'] = new History( $history );
			$tplVars['historyRenderer'] = new HistoryRenderer( $templating, $this );
		}

		$templating->render( "flow:board-history.html.php", $tplVars );
	}

	/**
	 * @param $history an array of flow revisions ( Header or PostRevision )
	 */
	protected function filterBoardHistory( array $history ) {
		// get rid of history entries user doesn't have sufficient permissions for
		$query = $needed = array();
		foreach ( $history as $i => $revision ) {
			switch( $revision->getRevisionType() ) {
				case 'header':
					// headers can't be moderated
					break;
				case 'post':
					if ( $revision->isTopicTitle() ) {
						$needed[$revision->getPostId()->getAlphadecimal()] = $i;
						$query[] = array( 'tree_rev_descendant_id' => $revision->getPostId() );
					} else {
						// comments should not be in board history
						unset( $history[$i] );
					}
					break;
			}
		}

		if ( !$needed ) {
			return $history;
		}

		// check permissions against most recent revision
		$found = $this->storage->findMulti(
			'PostRevision',
			$query,
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);
		foreach ( $found as $newest ) {
			$newest = reset( $newest );
			$id = $newest->getPostId()->getAlphadecimal();

			if ( isset( $needed[$id] ) ) {
				$i = $needed[$id];
				unset( $needed[$id] );

				if ( !$this->permissions->isAllowed( $newest, 'board-history' ) ) {
					unset( $history[$i] );
				}
			}
		}

		// not found
		foreach ( $needed as $i ) {
			unset( $history[$i] );
		}

		return $history;
	}

	public function renderAPI( Templating $templating, array $options ) {
		$output = array(
			'type' => 'board-history',
			'*' =>  $this->filterBoardHistory( $this->loadBoardHistory() ),
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
			throw new InvalidDataException( 'Unable to load topic list history for ' . $this->workflow->getId()->getAlphadecimal(), 'fail-load-history' );
		}

		// get rid of history entries user doesn't have sufficient permissions for
		foreach ( $history as $i => $revision ) {
			// only check against the specific revision, ignoring the most recent
			if ( !$this->permissions->isRevisionAllowed( $revision, 'post-history' ) ) {
				unset( $history[$i] );
			}
		}

		return $history;
	}

	public function getName() {
		return 'board-history';
	}

}
