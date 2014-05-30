<?php

namespace Flow\View;

use Flow\Block\AbstractBlock;
use Flow\Block\HeaderBlock;
use Flow\Block\TopicBlock;
use Flow\Block\TopicSummaryBlock;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\PermissionException;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Templating;
use Message;
use User;

/**
 * Static methods can't be abstract, but an abstract class extending an interface
 * can have unimplemented but required functions. As such this interface is basically
 * a hack so that RevisionView can guarantee static::createRevision() is always a defined
 * in child classes.
 */
interface RevisionCreatable {
	/**
	 * @param UUID|string $revId
	 * @param ManagerGroup $storage
	 * @return AbstractRevision|null
	 */
	static function createRevision( $revId, ManagerGroup $storage );
}

/**
 * Abstract revision view
 */
abstract class RevisionView implements RevisionCreatable {

	/**
	 * @var Templating
	 */
	protected $templating;

	/**
	 * The current session user viewing the revision
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * @param Templating $templating
	 * @param User $user
	 */
	public function __construct( Templating $templating, User $user ) {
		$this->templating = $templating;
		$this->user = $user;
	}

	/**
	 * Render a single revision view
	 *
	 * @param boolean $return whether return as string or render inline
	 * @return string
	 */
	abstract public function renderSingleView( $return = false );

	/**
	 * Check if the current revision model is comparable to the provided revision
	 *
	 * @param AbstractRevision $revision The revision to diff against
	 * @return boolean
	 */
	public function isComparableTo( AbstractRevision $revision ) {
		if ( $this->getRevision()->getRevisionType() == $revision->getRevisionType() ) {
			return $this->getRevision()->getCollectionId()->equals( $revision->getCollectionId() );
		} else {
			return false;
		}
	}

	/**
	 * Get the page header of revision diff view
	 *
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision $oldRevision
	 * @return Message
	 */
	abstract public function getDiffViewHeader( $newRevision, $oldRevision );

	/**
	 * Get the revision diff type
	 *
	 * @return string
	 */
	abstract public function getDiffType();

	/**
	 * Get the page header of revision single view
	 *
	 * @return string
	 */
	abstract public function getSingleViewHeader();

	/**
	 * Getter method for revision model
	 *
	 * @return AbstractRevision
	 */
	abstract public function getRevision();

	/**
	 * Getter method for block object
	 *
	 * @return AbstractBlock
	 *
	 */
	abstract public function getBlock();

	/**
	 * Generate revision diff view title for DifferenceEngine
	 *
	 * @param AbstractRevision $revision
	 * @return string
	 * @throws FlowException When the type of $revision is not recognized
	 */
	protected function generateDiffViewTitle( AbstractRevision $revision ) {
		$block = $this->getBlock();
		$message = wfMessage( 'flow-compare-revisions-revision-header' )
			->params( $revision->getRevisionId()->getTimestampObj()->getHumanTimestamp() )
			->params( $this->templating->getUserText( $revision, $this->user ) );

		if ( $revision instanceof PostRevision ) {
			$linkMethod = 'diffPostLink';
		} elseif ( $revision instanceof Header ) {
			$linkMethod = 'diffHeaderLink';
		} elseif ( $revision instanceof PostSummary ) {
			$linkMethod = 'diffSummaryLink';
		} else {
			throw new FlowException( 'Unknown revision type: ' . get_class( $revision ) );
		}

		$permalinkUrl = $this->templating->getUrlGenerator()
				->$linkMethod(
					$block->getWorkflow()->getArticleTitle(),
					$block->getWorkflow()->getId(),
					$revision->getRevisionId()
				)
				->getFullURL();

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
	 *
	 * @param AbstractRevision|string $revId The revision id to diff against
	 * @param boolean $return whether return as string or render inline
	 * @throws InvalidInputException
	 * @return string
	 */
	public function renderDiffViewAgainst( $revId, $return = false ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}

		$block = $this->getBlock();

		$revision = static::createRevision( $revId, $block->getStorage() );

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

		// Pass in permission as parameter?
		$permission = Container::get( 'permissions' );
		// Todo - Check the permission before invoking this function?
		if ( !$permission->isAllowed( $oldRev, 'view' ) || !$permission->isAllowed( $newRev, 'view' ) ) {
			throw new PermissionException( 'Insufficient permission to compare revisions', 'insufficient-permission' );
		}

		$oldContent = $oldRev->getContent( 'wikitext' );
		$newContent = $newRev->getContent( 'wikitext' );

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
	 *
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
	 *
	 * @return string|boolean
	 */
	public function getDiffLinkAgainstPrevious() {
		$revision = $this->getRevision();
		if ( $revision->getPrevRevisionId() ) {
			$workflow = $this->getBlock()->getWorkflow();
			if ( $revision instanceof PostRevision ) {
				return $this->templating->getUrlGenerator()->diffPostLink(
					$workflow->getArticleTitle(),
					$workflow->getId(),
					$revision->getRevisionId()
				)->getFullURL();
			} elseif ( $revision instanceof Header ) {
				return $this->templating->getUrlGenerator()->diffHeaderLink(
					$workflow->getArticleTitle(),
					$workflow->getId(),
					$revision->getRevisionId()
				)->getFullURL();
			} else {
				wfDebugLog( 'Flow', __METHOD__ . ': Unknown revision type: ' . get_class( $revision ) );
			}
		}

		return false;
	}

}

/**
 * Header revision view
 */
class HeaderRevisionView extends RevisionView {

	/**
	 * @var Header
	 */
	protected $revision;

	/**
	 * The block the revision view being rendered in
	 *
	 * @var HeaderBlock
	 */
	protected $block;

	/**
	 * @param Header $revision
	 * @param Templating $templating
	 * @param HeaderBlock $block
	 * @param User $user
	 */
	public function __construct( Header $revision, Templating $templating, HeaderBlock $block, User $user ) {
		parent::__construct( $templating, $user );
		$this->revision = $revision;
		$this->block = $block;
	}

	/**
	 * Create a revision view instance from a revision id
	 *
	 * @param string|UUID $revId
	 * @param Templating $templating
	 * @param HeaderBlock $block
	 * @param User $user
	 * @return HeaderRevisionView|false
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
	 * @param UUID|string $revId
	 * @param ManagerGroup $storage
	 * @return Header|null
	 */
	public static function createRevision( $revId, ManagerGroup $storage ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}

		return $storage->get( 'Header', $revId );
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
	 * @param Header $newRevision
	 * @param Header $oldRevision
	 * @return Message
	 */
	public function getDiffViewHeader( $newRevision, $oldRevision ) {
		$workflow = $this->block->getWorkflow();
		$boardLinkTitle = $workflow->getArticleTitle();
		$boardLink = $boardLinkTitle->getFullUrl();
		$historyLink = $this->templating->getUrlGenerator()
			->workflowHistoryLink( $boardLinkTitle, $workflow->getId() )
			->getFullUrl();

		$headerMsg = wfMessage( 'flow-compare-revisions-header-header' )
			->params(
				$boardLinkTitle,
				$this->templating->getUsernames()->get( $newRevision->getUserWiki(), $newRevision->getUserId(), $newRevision->getUserIp() ),
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
		$workflow = $this->block->getWorkflow();
		$historyLink = $this->templating->getUrlGenerator()
			->workflowHistoryLink(
				$workflow->getArticleTitle(),
				$workflow->getId()
			)
			->getFullURL();
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
	 * @var PostRevision
	 */
	protected $revision;

	/**
	 * The block the revision view being rendered in
	 *
	 * @var TopicBlock
	 */
	protected $block;

	/**
	 * @param PostRevision $revision
	 * @param Templating $templating
	 * @param TopicBlock $block
	 * @param User $user
	 */
	public function __construct( PostRevision $revision, Templating $templating, TopicBlock $block, User $user ) {
		parent::__construct( $templating, $user );
		$this->block = $block;
		$this->revision = $revision;
	}

	/**
	 * Create a revision view instance from a revision id
	 *
	 * @param string|UUID $revId
	 * @param Templating $templating
	 * @param TopicBlock $block
	 * @param User $user
	 * @return PostRevisionView|false
	 */
	public static function newFromId( $revId, Templating $templating, TopicBlock $block, User $user ) {
		$revision = self::createRevision( $revId, $block->getStorage() );

		if ( $revision ) {
			/** @var TreeRepository $treeRepo */
			$treeRepo = Container::get( 'repository.tree' );
			$rootPath = $treeRepo->findRootPath( $revision->getPostId() );
			$revision->setDepth( count( $rootPath ) - 1 );
			return new self( $revision, $templating, $block, $user );
		} else {
			return false;
		}
	}

	/**
	 * @param UUID|string $revId
	 * @param ManagerGroup $storage
	 * @return PostRevision|null
	 */
	public static function createRevision( $revId, ManagerGroup $storage ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}

		return $storage->get( 'PostRevision', $revId );
	}

	/**

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
	 * @param PostRevision $newRevision
	 * @param PostRevision $oldRevision
	 * @return Message
	 */
	public function getDiffViewHeader( $newRevision, $oldRevision ) {
		$urlGenerator = $this->templating->getUrlGenerator();
		$workflow = $this->block->getWorkflow();
		$title = $workflow->getArticleTitle();

		$boardLink = $urlGenerator->boardLink( $title );
		$postLink = $urlGenerator->postLink(
			$title,
			$workflow->getId(),
			$newRevision->getPostId()
		);
		$historyLink = $urlGenerator->postHistoryLink(
			$title,
			$workflow->getId(),
			$newRevision->getPostId()
		);

		$headerMsg = wfMessage( 'flow-compare-revisions-header-post' )
			->params(
				$this->block->getWorkflow()->getArticleTitle()
			)->rawParams(
				$this->templating->getContent( $this->block->loadTopicTitle(), 'wikitext' )
			)->params(
				$this->templating->getUsernames()->get( $newRevision->getCreatorWiki(), $newRevision->getCreatorId(), $newRevision->getCreatorIp() ),
				$boardLink->getFullUrl(),
				$postLink->getFullUrl(),
				$historyLink->getFullUrl()
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
		$workflow = $this->block->getWorkflow();
		$historyLink = $this->templating->getUrlGenerator()
			->postHistoryLink(
				$workflow->getArticleTitle(),
				$workflow->getId(),
				$this->revision->getPostId()
			)
			->getFullURL();

		$compareLink = $this->getDiffLinkAgainstPrevious();

		$msgKey = $compareLink ? 'flow-revision-permalink-warning-post' : 'flow-revision-permalink-warning-post-first';
		$message = wfMessage( $msgKey )
			->params( $this->revision->getRevisionId()->getTimestampObj()->getHumanTimestamp() )
			->params(
				$this->block->getWorkflow()->getArticleTitle()
			)->rawParams(
				$this->templating->getContent( $this->block->loadTopicTitle(), 'wikitext' )
			)->params(
				$historyLink
			);

		if ( $compareLink ) {
			$message->params( $compareLink );
		}
		return $message;
	}
}

/**
 * PostSummary revision view
 *
 * @Todo - This is supposed to support post summary in general but assuming topic
 * for now. When topicsummary block support a post summary, corresponding changes
 * should be made in here as well
 */
class PostSummaryRevisionView extends RevisionView {

	/**
	 * @var PostSummary
	 */
	protected $revision;

	/**
	 * The block the revision view being rendered in
	 *
	 * @var TopicSummaryBlock
	 */
	protected $block;

	/**
	 * @param PostSummary $revision
	 * @param Templating $templating
	 * @param HeaderBlock $block
	 * @param User $user
	 */
	public function __construct( PostSummary $revision, Templating $templating, TopicSummaryBlock $block, User $user ) {
		parent::__construct( $templating, $user );
		$this->revision = $revision;
		$this->block = $block;
	}

	/**
	 * Create a revision view instance from a revision id
	 *
	 * @param string|UUID $revId
	 * @param Templating $templating
	 * @param HeaderBlock $block
	 * @param User $user
	 * @return PostSummaryRevisionView|false
	 */
	public static function newFromId( $revId, Templating $templating, TopicSummaryBlock $block, User $user ) {
		$revision = self::createRevision( $revId, $block->getStorage() );

		if ( $revision ) {
			return new self( $revision, $templating, $block, $user );
		} else {
			return false;
		}
	}

	/**
	 * @param UUID|string $revId
	 * @param ManagerGroup $storage
	 * @return PostSummary|null
	 */
	public static function createRevision( $revId, ManagerGroup $storage ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}

		return $storage->get( 'PostSummary', $revId );
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

		return $prefix . $this->templating->render(
			'flow:postsummary.html.php',
			array(
				'revision' => $this->revision,
				'block' => $this->block,
				'user' => $this->user
			),
			$return
		);
	}

	/**
	 * @param PostSummary $newRevision
	 * @param PostSummary $oldRevision
	 * @return Message
	 */
	public function getDiffViewHeader( $newRevision, $oldRevision ) {
		$boardLinkTitle = clone $this->block->getWorkflow()->getArticleTitle();
		$boardLink = $boardLinkTitle->getFullUrl();

		/** @var Title $topicLinkTitle */
		/** @var string $topicLinkQuery */
		list( $topicLinkTitle, $topicLinkQuery ) = $this->templating->getUrlGenerator()
			->generateUrlData(
				$this->block->getWorkflow(),
				'view',
				array(
					$this->block->getName().'_postId' => $newRevision->getSummaryTargetId()->getAlphadecimal()
				)
			);

		$topicLinkTitle = clone $topicLinkTitle;

		$historyLink = $this->templating->getUrlGenerator()
			->generateUrl(
				$this->block->getWorkflow(),
				'history',
				array(
					$this->block->getName().'_postId' => $newRevision->getSummaryTargetId()->getAlphadecimal()
				)
			);
		$headerMsg = wfMessage( 'flow-compare-revisions-header-postsummary' )
			->params(
				$this->block->getWorkflow()->getArticleTitle(),
				$this->templating->getContent( $this->block->findTopicTitle(), 'wikitext' ),
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
		return 'compare-postsummary-revisions';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSingleViewHeader() {
		$historyLink = $this->templating->getUrlGenerator()->generateUrl(
			$this->block->getWorkflow(),
			'history',
			array(
				$this->block->getName().'_postId' => $this->revision->getSummaryTargetId()->getAlphadecimal(),
			)
		);

		$compareLink = $this->getDiffLinkAgainstPrevious();

		$msgKey = $compareLink ? 'flow-revision-permalink-warning-postsummary' : 'flow-revision-permalink-warning-postsummary-first';
		$message = wfMessage( $msgKey )
			->params( $this->revision->getRevisionId()->getTimestampObj()->getHumanTimestamp() )
			->params(
				$this->block->getWorkflow()->getArticleTitle(),
				$this->templating->getContent( $this->block->findTopicTitle(), 'wikitext' ),
				$historyLink
			);

		if ( $compareLink ) {
			$message->params( $compareLink );
		}
		return $message;
	}
}
