<?php

namespace Flow\View;

use Flow\Exception\InvalidInputException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\Model\UUID;
use Flow\Templating;
use Flow\Container;

abstract class RevisionView {

	protected $templating;

	protected $revision;

	protected $mapper;

	public function __construct( AbstractRevision $revision, Templating $templating, $mapper ) {
		$this->revision = $revision;
		$this->templating = $templating;
		$this->mapper = $mapper;
	}

	/**
	 * @return Flow\View\Revision|false
	 */
	public static function newFromId( $revId, $templating, $mapper ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );	
		}

		$revision = Container::get( 'storage' )->get(
			$mapper,
			$revId
		);

		if ( $revision ) {
			return new static( $revision, $templating, $mapper );
		} else {
			return false;	
		}
		
	}

	protected function getRevisionHeader( $block, $user ) {
		$templating = $this->templating;

		return function( $revision ) use ( $templating, $block, $user ) {
			$timestamp = $templating->render(
				'flow:timestamp.html.php',
				array(
					'timestamp' => $revision->getRevisionId()->getTimestampObj(),
					'tag' => 'span',
				),
				true
			);
			$message = wfMessage( 'flow-compare-revisions-revision-header' )
				->rawParams( $timestamp )
				->params( $templating->getUserText( $revision, $user ) );
		
			$permalinkUrl = $templating->getUrlGenerator()
				->generateBlockUrl(
					$block->getWorkflow(),
					$revision,
					true
				);
		
			$link = \Html::rawElement( 'a',
				array(
					'class' => 'flow-diff-revision-link',
					'href' => $permalinkUrl,
				),
				$message->parse()
			);
		
			return $link;
		};	
	}

	public function compareAgainst( $revId, $block, $user, $return = false ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );	
		}

		$revision = Container::get( 'storage' )->get(
			$this->mapper,
			$revId
		);

		if ( !$revision ) {
			throw new InvalidInputException( 'Attempt to compare against null revision', 'revision-comparison' );
		}

		if ( !$this->comparable( $revision ) ) {
			throw new InvalidInputException( 'Attempt to compare revisions of different headers', 'revision-comparison' );
		}

		// Position old and new revisions
		if (
			$this->revision->getRevisionId()->getTimestamp() >
			$revision->getRevisionId()->getTimestamp()
		) {
			$oldRev = $revision;
			$newRev = $this->revision;
		} else {
			$oldRev = $this->revision;
			$newRev = $revision;
		}

		$oldContent = $this->templating->getContent( $oldRev, 'wikitext' );
		$newContent = $this->templating->getContent( $newRev, 'wikitext' );
		
		$differenceEngine = new \DifferenceEngine();
		
		$differenceEngine->setContent(
			new \TextContent( $oldContent ),
			new \TextContent( $newContent )
		);

		$differenceEngine->showDiffStyle();

		$this->templating->getOutput()->addModules( 'ext.flow.history' );

		return $this->templating->render(
			'flow:compare-revisions.html.php',
			array(
				'block' => $block,
				'user' => $user,
				'oldRevision' => $oldRev,
				'newRevision' => $newRev,
				'getRevisionHeader' => $this->getRevisionHeader( $block, $user ),
				'headerMsg' => $this->getHeaderMessage( $block, $newRev, $oldRev ),
				'differenceEngine' => $differenceEngine
			), $return
		);
	}

	public function compareAgainstPrevious( $block, $user, $return = false ) {
		$parent = $revision->getPrevRevisionId();
		if ( !$parent ) {
			return false;
		}

		return $this->compareAgainst( $parent, $block, $user, $return );
	}

	public function getRevisionModel() {
		return $this->revision;	
	}

	abstract public function render( $block, $user, $return = false );

	abstract public function comparable( $revision );

	abstract public function getHeaderMessage( $block, $newRevision, $oldRevision );

}

class HeaderRevisionView extends RevisionView {

	public function render( $block, $user, $return = false ) {
		$prefix = $this->templating->render(
			'flow:revision-permalink-warning.html.php',
			array(
				'block' => $block,
				'revision' => $this->revision,
			),
			$return
		);

		return $prefix . $this->templating->renderHeader(
			$this->revision,
			$block,
			$user,
			'flow:header.html.php',
			$return
		);
	}

	public function comparable( $revision ) {
		return $this->revision->getWorkflowId()->equals( $revision->getWorkflowId() );
	}

	public function getHeaderMessage( $block, $newRevision, $oldRevision ) {
		$boardLinkTitle = $block->getWorkflow()->getArticleTitle();
		$boardLink = $this->templating->getUrlGenerator()
			->buildUrl(
				$boardLinkTitle,
				'view'
			);
		$historyLink = $this->templating->getUrlGenerator()
			->generateUrl(
				$block->getWorkflow(),
				'board-history'
			);
		$headerMsg = wfMessage( 'flow-compare-revisions-header-header' )
			->params(
				$boardLinkTitle,
				$this->templating->getUsernames()->get( wfWikiId(), $newRevision->getUserId(), $newRevision->getUserIp() ),
				$boardLink,
				$historyLink
			);
		return $headerMsg;
	}
}

class PostRevisionView extends RevisionView {

	public function render( $block, $user, $return = false ) {
		$this->revision->setChildren( array() );

		$prefix = $this->templating->render(
			'flow:revision-permalink-warning.html.php',
			array(
				'block' => $block,
				'revision' => $this->revision,
			),
			$return
		);

		if ( $this->revision->isTopicTitle() ) {
			return $prefix . $this->templating->renderTopic(
				$this->revision,
				$block,
				$return
			);
		} else {
			return $prefix . $this->templating->renderPost(
				$this->revision,
				$blcok,
				$return
			);
		}
	}

	public function comparable( $revision ) {
		return $this->revision->getPostId()->equals( $revision->getPostId() );
	}

	public function getHeaderMessage( $block, $newRevision, $oldRevision ) {
		$postFragment = '#flow-post-' . $newRevision->getPostId()->getAlphadecimal();
		$boardLinkTitle = clone $block->getWorkflow()->getArticleTitle();
		$boardLinkTitle->setFragment( $postFragment );
		$boardLink = $this->templating->getUrlGenerator()
			->buildUrl(
				$boardLinkTitle,
				'view'
			);
		list( $topicLinkTitle, $topicLinkQuery ) = $this->templating->getUrlGenerator()
			->generateUrlData(
				$block->getWorkflow(),
				'view',
				array(
					$block->getName().'_postId' => $newRevision->getPostId()->getAlphadecimal()
				)
			);

		$topicLinkTitle = clone $topicLinkTitle;
		$topicLinkTitle->setFragment( $postFragment );

		$historyLink = $this->templating->getUrlGenerator()
			->generateUrl(
				$block->getWorkflow(),
				'post-history',
				array(
					$block->getName().'_postId' => $newRevision->getPostId()->getAlphadecimal()
				)
			);
		$headerMsg = wfMessage( 'flow-compare-revisions-header-post' )
			->params(
				$block->getWorkflow()->getArticleTitle(),
				$this->templating->getContent( $block->loadTopicTitle(), 'wikitext' ),
				$this->templating->getUsernames()->get( wfWikiId(), $newRevision->getCreatorId() ),
				$boardLink,
				$topicLinkTitle->getFullUrl( $topicLinkQuery ),
				$historyLink
			);
		return $headerMsg;
	}
}
