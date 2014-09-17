<?php

namespace Flow;

use BagOStuff;
use Flow\Content\BoardContent;
use Flow\Data\ObjectManager;
use Flow\Model\Workflow;
use Status;
use Title;
use User;
use WikiPage;

class PageActivator {
	/**
	 * @var ObjectManager
	 */
	protected $om;

	/**
	 * @var BagOStuff Used as a way to pass state to the BoardContentHandler, the
	 *  state informs the handler to allow changing the content type of the page to
	 *  flow-board.
	 */
	protected $occupation;

	/**
	 * @param ObjectManager $om For the Workflow model
	 * @param BagOStuff $occupation Used to share state with the BoardContentHandler
	 */
	public function __construct( ObjectManager $om, BagOStuff $occupation ) {
		$this->om = $om;
		$this->occupation = $occupation;
	}

	/**
	 * @param Workflow|Title $input
	 * @param User $user The user to perform activation with
	 * @param string $comment
	 * @return Status
	 */
	public function activate( $input, $comment = '', User $user = null ) {
		if ( $input instanceof Workflow ) {
			$title = $input->getOwnerTitle();
			$workflow = $input;
		} elseif ( $input instanceof Title ) {
			$title = $input;
			$found = $this->om->find( array(
				'workflow_wiki' => wfWikiId(),
				'workflow_namespace' => $title->getNamespace(),
				'workflow_title_text' => $title->getDbKey(),
				'workflow_type' => 'discussion',
			) );
			if ( $found ) {
				// potentially the page was previously activated, but the 
				// top revision is not currently a flow board.
				$workflow = reset( $found );
			} else {
				$workflow = Workflow::create( 'discussion', $title );
			}
		} else {
			throw new FlowException( 'Expected either Workflow or Title' );
		}

		if ( strlen( $comment ) === 0 ) {
			$comment = '/* Taken over by Flow */';
		}

		$this->occupation->set( (string)$title, true );
		$status = WikiPage::factory( $title )->doEditContent(
			/* content */ new BoardContent( 'flow-board', $workflow ),
			/* comment */ $comment,
			/* flags */ 0,
			/* baseRevId */ false,
			/* user */ $user ?: $this->getTalkpageManager()
		);

		if ( $status->isGood() && $workflow->isNew() ) {
			$this->om->put( $workflow );
		}

		return $status;
	}

	/**
	 * @return User
	 */
	public function getTalkpageManager() {

	}
}
