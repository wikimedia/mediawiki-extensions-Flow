<?php

namespace Flow\View;

use Flow\Exception\InvalidInputException;
use Flow\Block\AbstractBlock;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\Model\UUID;
use Flow\Templating;

/**
 * Abstract revision view
 */
abstract class RevisionView {

	/**
	 * @var \Flow\Templating
	 */
	protected $templating;

	/**
	 * @var \Flow\Model\AbstractRevision
	 */
	protected $revision;

	/**
	 * The block the revision view being renderred
	 * @var \Flow\Block\AbstractBlock
	 */
	protected $block;

	/**
	 * @param \Flow\Model\AbstractRevision $revision
	 * @param \Flow\Templating $templating
	 * @param \Flow\Block\AbstractBlock $block
	 */
	public function __construct( AbstractRevision $revision, Templating $templating, AbstractBlock $block ) {
		$this->revision = $revision;
		$this->templating = $templating;
		$this->block = $block;
	}

	/**
	 * Create an revision view instance from a revision id
	 * @param string|\Flow\Model\UUID $revId
	 * @param \Flow\Templating $templating
	 * @param \Flow\Block\AbstractBlock $block
	 * @return Flow\View\Revision|false
	 */
	public static function newFromId( $revId, Templating $templating, AbstractBlock $block ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}

		$revision = $block->getStorage()->get(
			static::$mapper,
			$revId
		);

		if ( $revision ) {
			return new static( $revision, $templating, $block );
		} else {
			return false;
		}
	}

	/**
	 * Render a single revision view
	 * @param boolean $return whether return as string or render inline
	 * @return string
	 */
	abstract public function renderSingleView( $return = false );

	/**
	 * Check if the current revision model is comparable to the provided revision
	 * @param \Flow\Model\AbstractRevision $revision The revision to diff against
	 * @return boolean
	 */
	public function isComparableTo( $revision ) {
		if ( get_class( $this->revision ) === get_class( $revision ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the page header of revision diff view
	 * @param \Flow\Model\AbstractRevision $newRevision
	 * @param \Flow\Model\AbstractRevision $oldRevision
	 * @return string
	 */
	abstract public function getDiffViewHeader( $newRevision, $oldRevision );

	/**
	 * Get the revision diff type
	 * @return string
	 */
	abstract public function getDiffType();

	/**
	 * Get the page header of revision single view
	 * @return string
	 */
	abstract public function getSingleViewHeader();

	/**
	 * Generate revision diff view title for DifferenceEngine
	 * @param \Flow\Model\AbstractRevision $revision
	 * @return string
	 */
	protected function generateDiffViewTitle( $revision ) {
		$message = wfMessage( 'flow-compare-revisions-revision-header' )
			->params( $revision->getRevisionId()->getTimestampObj()->getHumanTimestamp() )
			->params( $this->templating->getUserText( $revision, $this->block->getUser() ) );

		$permalinkUrl = $this->templating->getUrlGenerator()
			->generateBlockUrl(
				$this->block->getWorkflow(),
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
	}

	/**
	 * Render diff view against a specific revision
	 * @param \Flow\Model\AbstractRevision|string $revId The revision id to diff against
	 * @param boolean $return whether return as string or render inline
	 * @return string
	 */
	public function renderDiffViewAgainst( $revId, $return = false ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}

		$revision = $this->block->getStorage()->get(
			static::$mapper,
			$revId
		);

		if ( !$revision ) {
			throw new InvalidInputException( 'Attempt to compare against null revision', 'revision-comparison' );
		}

		if ( !$this->isComparableTo( $revision ) ) {
			throw new InvalidInputException( 'Attempt to compare revisions of different types', 'revision-comparison' );
		}

		// Re-position old and new revisions if necessory
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
				'block' => $this->block,
				'user' => $this->block->getUser(),
				'oldRevision' => $this->generateDiffViewTitle( $oldRev ),
				'newRevision' => $this->generateDiffViewTitle( $newRev ),
				'headerMsg' => $this->getDiffViewHeader( $newRev, $oldRev ),
				'differenceEngine' => $differenceEngine
			), $return
		);
	}

	/**
	 * Render diff view against previous revision
	 * @param boolean $return whether return as string or render inline
	 * @return string|false
	 */
	public function renderDiffViewAgainstPrevious( $return = false ) {
		$parent = $revision->getPrevRevisionId();
		if ( !$parent ) {
			return false;
		}

		return $this->renderDiffViewAgainst( $parent, $return );
	}

	/**
	 * Getter method for revision model
	 * @return \Flow\Model\AbstractRevision
	 */
	public function getRevisionModel() {
		return $this->revision;
	}

	/**
	 * Get the diff link against previous revision
	 * @return string|boolean
	 */
	public function getDiffLinkAgainstPrevious() {
		if ( $this->revision->getPrevRevisionId() ) {
			$params = array(
				$this->block->getName().'_newRevision' => $this->revision->getRevisionId()->getAlphadecimal(),
				$this->block->getName().'_oldRevision' => $this->revision->getPrevRevisionId()->getAlphadecimal()
			);

			return $this->templating->getUrlGenerator()->generateUrl(
				$this->block->getWorkflow(),
				$this->getDiffType(),
				$params
			);
		} else {
			return false;
		}
	}

}

/**
 * Header revision view
 */
class HeaderRevisionView extends RevisionView {

	/**
	 * The data mapper name for header revision
	 * @var string
	 */
	protected static $mapper = 'Header';

	/**
	 * {@inheritDoc}
	 */
	public function renderSingleView( $return = false ) {
		$prefix = $this->templating->render(
			'flow:revision-permalink-warning.html.php',
			array(
				'block' => $this->block,
				'revision' => $this->revision,
				'message' => $this->getSingleViewHeader()
			),
			$return
		);

		return $prefix . $this->templating->renderHeader(
			$this->revision,
			$this->block,
			$this->block->getUser(),
			'flow:header.html.php',
			$return
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isComparableTo( $revision ) {
		if ( parent::isComparableTo( $revision ) ) {
			return $this->revision->getWorkflowId()->equals( $revision->getWorkflowId() );
		} else {
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiffViewHeader( $newRevision, $oldRevision ) {
		$boardLinkTitle = $this->block->getWorkflow()->getArticleTitle();
		$boardLink = $this->templating->getUrlGenerator()
			->buildUrl(
				$boardLinkTitle,
				'view'
			);
		$historyLink = $this->templating->getUrlGenerator()
			->generateUrl(
				$this->block->getWorkflow(),
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

	/**
	 * {@inheritDoc}
	 */
	public function getDiffType() {
		return 'compare-header-revisions';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSingleViewHeader() {
		$historyLink = $this->templating->getUrlGenerator()->generateUrl(
			$this->block->getWorkflow(),
			'board-history'
		);
		$compareLink = $this->getDiffLinkAgainstPrevious( $this->block );

		$msgKey = $compareLink ? 'flow-revision-permalink-warning-header' : 'flow-revision-permalink-warning-header-first';
		$message = wfMessage( $msgKey )
			->params( $this->revision->getRevisionId()->getTimestampObj()->getHumanTimestamp() )
			->params(
				$historyLink
			);

		if ( $compareLink ) {
			$message->params( $compareLink );
		}
		return $message;
	}
}

/**
 * Post revision view
 */
class PostRevisionView extends RevisionView {

	/**
	 * The data mapper name for post revision
	 * @var string
	 */
	protected static $mapper = 'PostRevision';

	/**
	 * {@inheritDoc}
	 */
	public function renderSingleView( $return = false ) {
		$this->revision->setChildren( array() );

		$prefix = $this->templating->render(
			'flow:revision-permalink-warning.html.php',
			array(
				'block' => $this->block,
				'revision' => $this->revision,
				'message' => $this->getSingleViewHeader()
			),
			$return
		);

		if ( $this->revision->isTopicTitle() ) {
			return $prefix . $this->templating->renderTopic(
				$this->revision,
				$this->block,
				$return
			);
		} else {
			return $prefix . $this->templating->renderPost(
				$this->revision,
				$this->block,
				$return
			);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function isComparableTo( $revision ) {
		if ( parent::isComparableTo( $revision ) ) {
			return $this->revision->getPostId()->equals( $revision->getPostId() );
		} else {
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiffViewHeader( $newRevision, $oldRevision ) {
		$postFragment = '#flow-post-' . $newRevision->getPostId()->getAlphadecimal();
		$boardLinkTitle = clone $this->block->getWorkflow()->getArticleTitle();
		$boardLinkTitle->setFragment( $postFragment );
		$boardLink = $this->templating->getUrlGenerator()
			->buildUrl(
				$boardLinkTitle,
				'view'
			);
		list( $topicLinkTitle, $topicLinkQuery ) = $this->templating->getUrlGenerator()
			->generateUrlData(
				$this->block->getWorkflow(),
				'view',
				array(
					$this->block->getName().'_postId' => $newRevision->getPostId()->getAlphadecimal()
				)
			);

		$topicLinkTitle = clone $topicLinkTitle;
		$topicLinkTitle->setFragment( $postFragment );

		$historyLink = $this->templating->getUrlGenerator()
			->generateUrl(
				$this->block->getWorkflow(),
				'post-history',
				array(
					$this->block->getName().'_postId' => $newRevision->getPostId()->getAlphadecimal()
				)
			);
		$headerMsg = wfMessage( 'flow-compare-revisions-header-post' )
			->params(
				$this->block->getWorkflow()->getArticleTitle(),
				$this->templating->getContent( $this->block->loadTopicTitle(), 'wikitext' ),
				$this->templating->getUsernames()->get( wfWikiId(), $newRevision->getCreatorId() ),
				$boardLink,
				$topicLinkTitle->getFullUrl( $topicLinkQuery ),
				$historyLink
			);
		return $headerMsg;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiffType() {
		return 'compare-post-revisions';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSingleViewHeader() {
		$historyLink = $this->templating->getUrlGenerator()->generateUrl(
			$this->block->getWorkflow(),
			'post-history',
			array(
				$this->block->getName().'_postId' => $this->revision->getPostId()->getAlphadecimal(),
			)
		);

		$compareLink = $this->getDiffLinkAgainstPrevious();

		$msgKey = $compareLink ? 'flow-revision-permalink-warning-post' : 'flow-revision-permalink-warning-post-first';
		$message = wfMessage( $msgKey )
			->params( $this->revision->getRevisionId()->getTimestampObj()->getHumanTimestamp() )
			->params(
				$this->block->getWorkflow()->getArticleTitle(),
				$this->templating->getContent( $this->block->loadTopicTitle(), 'wikitext' ),
				$historyLink
			);

		if ( $compareLink ) {
			$message->params( $compareLink );
		}
		return $message;
	}
}
