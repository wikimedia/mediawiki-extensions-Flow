<?php

namespace Flow\Import\Postprocessor;

use Flow\Import\IImportHeader;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\LiquidThreadsApi\ImportPost;
use Flow\Import\LiquidThreadsApi\ImportTopic;
use Flow\Import\PageImportState;
use Flow\Import\TopicImportState;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\UrlGenerator;
use MediaWiki\Content\WikitextContent;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class LqtRedirector implements Postprocessor {
	/** @var UrlGenerator */
	protected $urlGenerator;
	/**
	 * @var array[]
	 * @phan-var non-empty-array[]
	 */
	protected $redirectsToDo;
	/** @var User */
	protected $user;

	public function __construct( UrlGenerator $urlGenerator, User $user ) {
		$this->urlGenerator = $urlGenerator;
		$this->redirectsToDo = [];
		$this->user = $user;
	}

	public function afterHeaderImported( PageImportState $state, IImportHeader $header ) {
		// not a thing to do, yet
	}

	public function afterPostImported( TopicImportState $state, IImportPost $post, PostRevision $newPost ) {
		if ( $post instanceof ImportPost /* LQT */ ) {
			$this->redirectsToDo[] = [
				$post->getTitle(),
				$state->topicWorkflow->getId(),
				$newPost->getPostId()
			];
		}
	}

	public function afterTopicImported( TopicImportState $state, IImportTopic $topic ) {
		if ( !$topic instanceof ImportTopic /* LQT */ ) {
			return;
		}
		$this->doRedirect(
			$topic->getTitle(),
			$state->topicWorkflow->getId()
		);
		foreach ( $this->redirectsToDo as $args ) {
			// @phan-suppress-next-line PhanParamTooFewUnpack
			$this->doRedirect( ...$args );
		}

		$this->redirectsToDo = [];
	}

	public function importAborted() {
		$this->redirectsToDo = [];
	}

	protected function doRedirect( Title $fromTitle, UUID $toTopic, ?UUID $toPost = null ) {
		if ( $toPost ) {
			$redirectAnchor = $this->urlGenerator->postLink( null, $toTopic, $toPost );
		} else {
			$redirectAnchor = $this->urlGenerator->topicLink( null, $toTopic );
		}

		$redirectTarget = $redirectAnchor->resolveTitle();

		$newContent = new WikitextContent( "#REDIRECT [[" . $redirectTarget->getFullText() . "]]" );
		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $fromTitle );
		$summary = wfMessage( 'flow-lqt-redirect-reason' )->plain();
		$page->doUserEditContent( $newContent, $this->user, $summary, EDIT_FORCE_BOT );

		MediaWikiServices::getInstance()->getWatchedItemStore()->duplicateAllAssociatedEntries(
			$fromTitle, $redirectTarget
		);
	}
}
