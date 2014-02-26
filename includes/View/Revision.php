<?php

namespace Flow\View;

use Flow\Block\HeaderBlock;
use Flow\Block\TopicBlock;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Exception\InvalidInputException;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Templating;
use User;

/**
 * Abstract revision view
 */
abstract class RevisionView {

	/**
	 * @var \Flow\Templating
	 */
	protected $templating;

	/**
	 * The current session user viewing the revision
	 *
	 * @var \User
	 */
	protected $user;

	/**
	 * @param \Flow\Templating $templating
	 * @param User $user
	 */
	public function __construct( Templating $templating, User $user ) {
		$this->templating = $templating;
		$this->user = $user;
	}

	/**
	 * Create a revision model instance from a revision id
	 * @param string|\Flow\Model\UUID $revId
	 * @param \Flow\Data\ManagerGroup $storage
	 * @return \Flow\Model\Header|\Flow\Model\PostRevision|null
	 */
	protected static function createRevision( $revId, ManagerGroup $storage ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}

		return $storage->get( static::$mapper, $revId );
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
		if ( get_class( $this->getRevision() ) === get_class( $revision ) ) {
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
	 * Getter method for revision model
	 * @return \Flow\Model\AbstractRevision
	 */
	abstract public function getRevision();

	/**
	 * Getter method for block object
	 * @return \Flow\Model\AbstractBlock
	 *
	 */
	abstract public function getBlock();

	/**
	 * Generate revision diff view title for DifferenceEngine
	 * @param \Flow\Model\AbstractRevision $revision
	 * @return string
	 */
	protected function generateDiffViewTitle( AbstractRevision $revision ) {
		$block = $this->getBlock();
		$message = wfMessage( 'flow-compare-revisions-revision-header' )
			->params( $revision->getRevisionId()->getTimestampObj()->getHumanTimestamp() )
			->params( $this->templating->getUserText( $revision, $this->user ) );

		$permalinkUrl = $this->templating->getUrlGenerator()
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
	}

	/**
	 * Render diff view against a specific revision
	 * @param \Flow\Model\AbstractRevision|string $revId The revision id to diff against
	 * @param boolean $return whether return as string or render inline
	 * @throws \Flow\Exception\InvalidInputException
	 * @return string
	 */
	public function renderDiffViewAgainst( $revId, $return = false ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}

		$block = $this->getBlock();

		$revision = $block->getStorage()->get(
			static::$mapper,
			$revId
		);

		if ( !$revision ) {
			throw new InvalidInputException( 'Attempt to compare against null revision', 'revision-comparison' );
		}

		if ( !$this->isComparableTo( $revision ) ) {
			throw new InvalidInputException( 'Attempt to compare revisions of different types', 'revision-comparison' );
		}

		$currentRevision = $this->getRevision();
		// Re-position old and new revisions if necessary
		if (
			$currentRevision->getRevisionId()->getTimestamp() >
			$revision->getRevisionId()->getTimestamp()
		) {
			$oldRev = $revision;
			$newRev = $currentRevision;
		} else {
			$oldRev = $currentRevision;
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
				'user' => $this->user,
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
		$parent = $this->getRevision()->getPrevRevisionId();
		if ( !$parent ) {
			return false;
		}

		return $this->renderDiffViewAgainst( $parent, $return );
	}

	/**
	 * Get the diff link against previous revision
	 * @return string|boolean
	 */
	public function getDiffLinkAgainstPrevious() {
		$revision = $this->getRevision();
		$block = $this->getBlock();
		if ( $revision->getPrevRevisionId() ) {
			$params = array(
				$block->getName().'_newRevision' => $revision->getRevisionId()->getAlphadecimal(),
				$block->getName().'_oldRevision' => $revision->getPrevRevisionId()->getAlphadecimal()
			);

			return $this->templating->getUrlGenerator()->generateUrl(
				$block->getWorkflow(),
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
	 * @var \Flow\Model\Header
	 */
	protected $revision;

	/**
	 * The block the revision view being rendered in
	 * @var \Flow\Block\HeaderBlock
	 */
	protected $block;

	/**
	 * @param \Flow\Model\Header
	 * @param \Flow\Templating $templating
	 * @param \Flow\Block\HeaderBlock $block
	 * @param User $user
	 */
	public function __construct( Header $revision, Templating $templating, HeaderBlock $block, User $user ) {
		parent::__construct( $templating, $user );
		$this->revision = $revision;
		$this->block = $block;
	}

	/**
	 * Create a revision view instance from a revision id
	 * @param string|\Flow\Model\UUID $revId
	 * @param \Flow\Templating $templating
	 * @param \Flow\Block\HeaderBlock $block
	 * @param User $user
	 * @return \Flow\View\HeaderRevisionView|false
	 */
	public static function newFromId( $revId, Templating $templating, HeaderBlock $block, User $user ) {
		$revision = self::createRevision( $revId, $block->getStorage() );

		if ( $revision ) {
			return new self( $revision, $templating, $block, $user );
		} else {
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRevision() {
		return $this->revision;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBlock() {
		return $this->block;
	}

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
			$this->user,
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
	 * @var \Flow\Model\PostRevision
	 */
	protected $revision;

	/**
	 * The block the revision view being rendered in
	 * @var \Flow\Block\TopicBlock
	 */
	protected $block;

	/**
	 * @param \Flow\Model\PostRevision
	 * @param \Flow\Templating $templating
	 * @param \Flow\Block\TopicBlock $block
	 * @param User $user
	 */
	public function __construct( PostRevision $revision, Templating $templating, TopicBlock $block, User $user ) {
		parent::__construct( $templating, $user );
		$this->block = $block;
		$this->revision = $revision;
	}

	/**
	 * Create a revision view instance from a revision id
	 * @param string|\Flow\Model\UUID $revId
	 * @param \Flow\Templating $templating
	 * @param \Flow\Block\TopicBlock $block
	 * @param User $user
	 * @return \Flow\View\PostRevisionView|false
	 */
	public static function newFromId( $revId, Templating $templating, TopicBlock $block, User $user ) {
		$revision = self::createRevision( $revId, $block->getStorage() );

		if ( $revision ) {
			$rootPath = Container::get( 'repository.tree' )->findRootPath( $revision->getPostId() );
			$revision->setDepth( count( $rootPath ) - 1 );
			return new self( $revision, $templating, $block, $user );
		} else {
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRevision() {
		return $this->revision;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBlock() {
		return $this->block;
	}

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
