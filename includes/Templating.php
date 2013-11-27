<?php

namespace Flow;

use Flow\Block\Block;
use Flow\Block\TopicBlock;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\View\PostActionMenu;
use OutputPage;
// These dont really belong here
use Html;
use Linker;
use MWTimestamp;
use RequestContext;
use Title;
use User;

class Templating {
	public $urlGenerator;
	protected $output;
	protected $namespaces;
	protected $globals;

	/**
	 * @var array Array of PostRevision::registerRecursive return values
	 * @see Templating::registerParsoidLinks
	 */
	public $parsoidLinksIdentifiers = array();

	/**
	 * @var array Array of processed post Ids
	 * @see Templating::registerParsoidLinks
	 */
	public $parsoidLinksProcessed = array();

	/**
	 * @var array Array of Title objects
	 * @see Templating::registerParsoidLinks
	 */
	public $parsoidLinks = array();

	public function __construct( UrlGenerator $urlGenerator, OutputPage $output, array $namespaces = array(), array $globals = array() ) {
		$this->urlGenerator = $urlGenerator;
		$this->output = $output;
		foreach ( $namespaces as $ns => $path ) {
			$this->addNamespace( $ns, $path );
		}
		$this->globals = $globals;
	}

	public function getOutput() {
		return $this->output;
	}

	public function addNamespace( $ns, $path ) {
		$this->namespaces[$ns] = rtrim( $path, '/' );
	}

	public function addGlobalVariable( $name, $value ) {
		$this->globals[$name] = $value;
	}

	public function render( $file, array $vars = array(), $return = false ) {
		$file = $this->applyNamespacing( $file );

		ob_start();
		$this->_render( $file, $vars + $this->globals );
		$content = ob_get_contents();
		ob_end_clean();

		if ( $return ) {
			return $content;
		} else {
			$this->output->addHTML( $content );
		}
	}

	protected function applyNamespacing( $file ) {
		if ( false === strpos( $file, ':' ) ) {
			return $file;
		}
		list( $ns, $file ) = explode( ':', $file, 2 );
		if ( !isset( $this->namespaces[$ns] ) ) {
			throw new MWException( 'Unknown template namespace' );
		}

		return $this->namespaces[$ns] . '/' . ltrim( $file, '/' );
	}

	protected function _render( $__file__, $__vars__ ) {
		extract( $__vars__ );

		include $__file__;
	}

	// Helper methods for the view
	//
	// Everything below here *DOES* *NOT*  belong in this class.  Its also pointless for us to invent a properly
	// abstracted templating implementation so these can be elsewhere.  Figure out if we can transition to an
	// industry standard templating solution and stop the NIH.

	public function getUrlGenerator() {
		return $this->urlGenerator;
	}

	public function generateUrl( $workflow, $action = 'view', array $query = array() ) {
		return $this->getUrlGenerator()->generateUrl( $workflow, $action, $query );
	}

	public function renderPost( PostRevision $post, Block $block, $return = true ) {
		if ( $post->isTopicTitle() ) {
			throw new \MWException( 'Cannot render topic with ' . __METHOD__ );
		}

		// An ideal world may pull this from the container, but for now this is fine.  This templating
		// class has too many responsibilities to keep receiving all required objects in the constructor.
		$actionMenu = $this->createActionMenu( $post, $block );
		$view = new View\Post(
			$this->globals['user'], // There is no guarantee of this existing
			$post,
			$actionMenu
		);

		if ( !$actionMenu->isAllowed( 'view' ) ) {
			return '';
		}

		return $this->render(
			'flow:post.html.php',
			array(
				'block' => $block,
				'post' => $post,
				'postView' => $view,
			),
			$return
		);
	}

	public function renderTopic( PostRevision $root, TopicBlock $block, $return = true ) {
		$actionMenu = $this->createActionMenu( $root, $block );
		if ( !$actionMenu->isAllowed( 'view' ) ) {
			return '';
		}
		return $this->render( "flow:topic.html.php", array(
			'block' => $block,
			'topic' => $block->getWorkflow(),
			'root' => $root,
			'postActionMenu' => $actionMenu,
		), $return );
	}

	// An ideal world may pull this from the container, but for now this is fine.  This templating
	// class has too many responsibilities to keep receiving all required objects in the constructor.
	protected function createActionMenu( PostRevision $post, Block $block ) {
		$container = Container::getContainer();

		return new PostActionMenu(
			$this->urlGenerator,
			$container['flow_actions'],
			new PostActionPermissions( $container['flow_actions'], $this->globals['user'] ),
			$block,
			$post,
			$this->globals['editToken']
		);
	}

	public function getPagingLink( $block, $direction, $offset, $limit ) {
		$output = '';

		// Use the message/class flow-paging-fwd or flow-paging-rev
		//  depending on direction
		$output .= \Html::element(
			'a',
			array(
				'href' => $this->generateUrl(
					$block->getWorkflowId(),
					'view',
					array(
						$block->getName().'[offset-id]' => $offset,
						$block->getName().'[offset-dir]' => $direction,
						$block->getName().'[limit]' => $limit,
					)
				),
			),
			wfMessage( 'flow-paging-'.$direction )->parse()
		);

		$output = \Html::rawElement(
			'div',
			array(
				'class' => 'flow-paging flow-paging-'.$direction,
				'data-offset' => $offset,
				'data-direction' => $direction,
				'data-limit' => $limit,
			),
			$output
		);

		return $output;
	}

	public function userToolLinks( $userId, $userText ) {
		static $cache = array();
		if ( isset( $cache[$userId][$userText] ) ) {
			return $cache[$userId][$userText];
		}

		if ( $userText instanceof MWMessage ) {
			// username was moderated away, we dont know who this is
			$res = '';
		} else {
			$res = Linker::userLink( $userId, $userText ) . Linker::userToolLinks( $userId, $userText );
		}
		return $cache[$userId][$userText] = $res;
	}

	/**
	 * Returns a message that displays information on the participants of a topic.
	 *
	 * @param PostRevision $post
	 * @param int[optional] $registered The identifier that was returned when
	 * registering the callback via PostRevision::registerRecursive()
	 * @return string
	 */
	public function printParticipants( PostRevision $post, $registered = null ) {
		$participants = $post->getRecursiveResult( $registered );
		$participantCount = count( $participants );

		$originalPoster = array_shift( $participants );
		$mostRecentPoster = array_pop( $participants );
		$secondMostRecentPoster = array_pop( $participants );

		return wfMessage(
			'flow-topic-participants',
			$participantCount,
			max( 0, $participantCount - 3 ),
			$originalPoster,
			$mostRecentPoster,
			$secondMostRecentPoster
		)->parse();
	}

	/**
	 * Formats a revision's usertext for displaying. Usually, the revision's
	 * usertext can just be displayed. In the event of moderation, however, that
	 * info should not be exposed.
	 *
	 * If a specific i18n message is available for a certain moderation level,
	 * that message will be returned (well, unless the user actually has the
	 * required permissions to view the full username). Otherwise, in normal
	 * cases, the full username will be returned.
	 *
	 * @param AbstractRevision $revision Revision to display usertext for
	 * @param User[optional] $permissionsUser User to display usertext to
	 * @return string
	 */
	public function getUserText( AbstractRevision $revision, User $permissionsUser = null ) {
		$state = $revision->getModerationState();
		$username = $revision->getUserText();

		// Messages: flow-hide-usertext, flow-delete-usertext, flow-suppress-usertext
		$message = wfMessage( "flow-$state-usertext", $username );

		if ( !$revision->isAllowed( $permissionsUser ) && $message->exists() ) {
			return $message->text();
		} else {
			return $username;
		}
	}

	/**
	 * Returns pretty-printed user links + user tool links for history and
	 * RecentChanges pages.
	 *
	 * Moderation-aware.
	 * 
	 * @param  AbstractRevision $revision        Revision to display
	 * @param  User             $permissionsUser The User to check permissions for
	 * @return string                            HTML
	 */
	public function getUserLinks( AbstractRevision $revision, User $permissionsUser = null ) {
		$state = $revision->getModerationState();
		$userid = $revision->getUserId();
		$username = $revision->getUserText();

		// Messages: flow-hide-usertext, flow-delete-usertext, flow-suppress-usertext
		$message = wfMessage( "flow-$state-usertext", $username );

		if ( !$revision->isAllowed( $permissionsUser ) && $message->exists() ) {
			return $message->text();
		} else {
			return Linker::userLink( $userid, $username ) . Linker::userToolLinks( $userid, $username );
		}
	}

	/**
	 * Formats a post's creator name for displaying. Usually, the post's creator
	 * name can just be displayed. In the event of moderation, however, that
	 * info should not be exposed.
	 *
	 * If a specific i18n message is available for a certain moderation level,
	 * that message will be returned (well, unless the user actually has the
	 * required permissions to view the full username). Otherwise, in normal
	 * cases, the full creator name will be returned.
	 *
	 * @param PostRevision $revision Revision to display creator name for
	 * @param User[optional] $permissionsUser User to display creator name to
	 * @return string
	 */
	public function getCreatorText( PostRevision $revision, User $permissionsUser = null ) {
		$state = $revision->getModerationState();
		$username = $revision->getCreatorNameRaw();

		// Messages: flow-hide-usertext, flow-delete-usertext, flow-suppress-usertext
		$message = wfMessage( "flow-$state-usertext", $username );

		if ( !$revision->isAllowed( $permissionsUser ) && $message->exists() ) {
			return $message->text();
		} else {
			return $username;
		}
	}

	/**
	 * Formats a revision's content for displaying. Usually, the revisions's
	 * content can just be displayed. In the event of moderation, however, that
	 * info should not be exposed.
	 *
	 * If a specific i18n message is available for a certain moderation level,
	 * that message will be returned (well, unless the user actually has the
	 * required permissions to view the full content). Otherwise, in normal
	 * cases, the full content will be returned.
	 *
	 * @param AbstractRevision $revision Revision to display content for
	 * @param string[optional] $format Format to output content in (html|wikitext)
	 * @param User[optional] $permissionsUser User to display content to
	 * @return string
	 */
	public function getContent( AbstractRevision $revision, $format = 'html', User $permissionsUser = null ) {
		$state = $revision->getModerationState();
		$user = $revision->getModeratedByUserText();

		// Messages: flow-hide-content, flow-delete-content, flow-suppress-content
		$message = wfMessage( "flow-$state-content", $user );

		if ( !$revision->isAllowed( $permissionsUser ) && $message->exists() ) {
			return $message->text();
		} else {
			$content = $revision->getContent( $format );

			if ( $format === 'html' ) {
				// Parsoid doesn't render redlinks
				$content = $this->applyRedlinks( $content );
			}

			return $content;
		}
	}

	/**
	 * Parsoid ignores red links. With good reason: redlinks should only be
	 * applied when rendering the content, not when it's created.
	 *
	 * This method will parse a given content, fetch all of its links & let MW's
	 * Linker class build the link HTML (which will take redlinks into account.)
	 * It will then substitute original link HTML for the one Linker generated.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function applyRedlinks( $content ) {
		/*
		 * In order to efficiently replace redlinks, multiple recursive
		 * functions (may) have been registered that fetch an array of linked-to
		 * titles. Since we'll now need to apply redlinks, it's time to execute
		 * all these callbacks & batch-load all of the titles they come up with.
		 */
		foreach ( $this->parsoidLinksIdentifiers as $identifier => $post ) {
			$post->getRecursiveResult( $identifier );
			unset( $this->parsoidLinksIdentifiers[$identifier] );
		}
		if ( $this->parsoidLinks ) {
			$batch = new \LinkBatch( $this->parsoidLinks );
			$batch->execute();
			$this->parsoidLinks = array();
		}

		/*
		 * Workaround because DOMDocument can't guess charset.
		 * Content should be utf-8. Alternative "workarounds" would be to
		 * provide the charset in $response, as either:
		 * * <?xml encoding="utf-8" ?>
		 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		 */
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );

		$dom = ParsoidUtils::createDOM( $content );

		// find links in DOM
		$xpath = new \DOMXPath( $dom );
		$linkNodes = $xpath->query( '//a[@rel="mw:WikiLink"][@data-parsoid]' );
		foreach ( $linkNodes as $linkNode ) {
			$parsoid = $linkNode->getAttribute( 'data-parsoid' );
			$parsoid = json_decode( $parsoid, true );

			if ( isset( $parsoid['sa']['href'] ) ) {
				// gather existing link attributes
				$attributes = array();
				foreach ( $linkNode->attributes as $attribute ) {
					$attributes[$attribute->name] = $attribute->value;
				}

				// let MW build link HTML based on Parsoid data
				$title = Title::newFromText( $parsoid['sa']['href'] );
				$linkHTML = Linker::link( $title, $linkNode->nodeValue, $attributes );

				// create new DOM from this MW-built link
				$linkDom = ParsoidUtils::createDOM( $linkHTML );

				// import MW-built link node into content DOM
				$replacementNode = $linkDom->getElementsByTagName( 'a' )->item( 0 );
				$replacementNode = $dom->importNode( $replacementNode, true );

				// replace Parsoid link with MW-built link
				$linkNode->parentNode->replaceChild( $replacementNode, $linkNode );
			}
		}

		return $dom->saveHTML();
	}

	/**
	 * Registers callback function to find content links in Parsoid html.
	 * The goal is to batch-load and add to LinkCache as much links as possible.
	 */
	public function registerParsoidLinks( PostRevision $post ) {
		/*
		 * This can be registered on multiple posts (e.g. multiple topics) to
		 * batch-load as much as possible; all of the identifiers have to be
		 * saved and will be processed as soon as they first are needed.
		 */
		$identifier = $post->registerRecursive( array( $this, 'registerParsoidLinksCallback' ), array(), 'parsoidlinks' );
		$this->parsoidLinksIdentifiers[$identifier] = $post;
	}

	/**
	 * DON'T CALL THIS METHOD!
	 * This is for internal use only: it's a callback function to
	 * PostRevision::registerRecursive, which can be registered via
	 * Templating::registerParsoidLinks.
	 *
	 * Returns an array of linked pages in Parsoid.
	 *
	 * @param PostRevision $post
	 * @param array $result
	 * @return array Return array in the format of [result, continue]
	 */
	public function registerParsoidLinksCallback( PostRevision $post, $result ) {
		// topic titles don't contain html
		if ( $post->isTopicTitle() ) {
			return array( array(), true );
		}
		$content = $post->getContent( 'html' );

		// make sure a post is not checked more than once
		$revisionId = $post->getRevisionId()->getHex();
		if ( isset( $this->parsoidLinksProcessed[$revisionId] ) ) {
			return array( array(), false );
		}
		$this->parsoidLinksProcessed[$revisionId] = true;

		// find links in DOM
		$dom = ParsoidUtils::createDOM( $content );
		$xpath = new \DOMXPath( $dom );
		$linkNodes = $xpath->query( '//a[@rel="mw:WikiLink"][@data-parsoid]' );

		foreach ( $linkNodes as $linkNode ) {
			$parsoid = $linkNode->getAttribute( 'data-parsoid' );
			$parsoid = json_decode( $parsoid, true );

			if ( isset( $parsoid['sa']['href'] ) ) {
				// real results will be stored in Templating::parsoidLinks
				$link = $parsoid['sa']['href'];
				$this->parsoidLinks[$link] = Title::newFromText( $link );
			}
		}

		/*
		 * $result will not be used; we'll register this callback multiple
		 * times and will want to gather overlapping results, so they'll
		 * be stored at Templating::parsoidLinks
		 */
		return array( array(), true );
	}
}
