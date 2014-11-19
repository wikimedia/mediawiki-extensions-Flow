<?php

namespace Flow\Import\Postprocessing;

use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\LiquidThreadsApi\ImportPost;
use Flow\Import\LiquidThreadsApi\ImportTopic;
use Flow\Model\UUID;
use Flow\UrlGenerator;
use Title;
use WatchedItem;
use WikiPage;
use WikitextContent;

class LqtRedirector implements Postprocessor {
	/** @var UrlGenerator **/
	protected $urlGenerator;
	/** @var array **/
	protected $redirectsToDo;

	public function __construct( UrlGenerator $urlGenerator ) {
		$this->urlGenerator = $urlGenerator;
		$this->redirectsToDo = array();
	}

	public function afterTopicImported( IImportTopic $topic, UUID $newTopicId ) {
		if ( $topic instanceof ImportTopic /* LQT */ ) {
			$this->redirectsToDo[] = array( $topic->getTitle(), $newTopicId );
		}
	}

	function afterPostImported( IImportPost $post, UUID $topicId, UUID $newPostId ) {
		if ( $post instanceof ImportPost /* LQT */ ) {
			$this->redirectsToDo[] = array( $post->getTitle(), $topicId, $newPostId );
		}
	}

	function afterTalkpageImported() {
		foreach( $this->redirectsToDo as $args ) {
			call_user_func_array( array( $this, 'doRedirect' ), $args );
		}

		$this->redirectsToDo = array();
	}

	protected function doRedirect( Title $fromTitle, UUID $toTopic, UUID $toPost = null ) {
		if ( $toPost ) {
			$redirectAnchor = $this->urlGenerator->postLink( null, $toTopic, $toPost );
		} else {
			$redirectAnchor = $this->urlGenerator->topicLink( null, $toTopic );
		}

		$redirectTarget = $redirectAnchor->resolveTitle();

		// XXX: Not sure which user this is done by?
		$newContent = new WikiTextContent( "#REDIRECT [[".$redirectTarget->getFullText()."]]" );
		$page = WikiPage::factory( $fromTitle );
		$summary = wfMessage( 'flow-lqt-redirect-reason' )->plain();
		$page->doEditContent( $newContent, $summary, EDIT_FORCE_BOT );

		WatchedItem::duplicateEntries( $fromTitle, $redirectTarget );
	}
}
