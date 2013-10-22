<?php

namespace Flow;

use Flow\Block\Block;
use Flow\Block\TopicBlock;
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
		global $wgFlowTokenSalt;

		if ( $post->isTopicTitle() ) {
			throw new \MWException( 'Cannot render topic with ' . __METHOD__ );
		}

		// An ideal world may pull this from the container, but for now this is fine.  This templating
		// class has too many responsibilities to keep receiving all required objects in the constructor.
		$view = new View\Post(
			$this->globals['user'], // There is no guarantee of this
			$post,
			$this->createActionMenu( $post, $block )
		);

		$actionMenu = $this->createActionMenu( $post, $block );
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
		global $wgUser, $wgFlowTokenSalt;

		// @todo: I don't like container being pulled in here, improve this some day
		$container = Container::getContainer();

		return new PostActionMenu(
			$this->urlGenerator,
			$container['flow_actions'],
			new PostActionPermissions( $container['flow_actions'], $wgUser ),
			$block,
			$post,
			$wgUser->getEditToken( $wgFlowTokenSalt )
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
		global $wgLang;
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

		$participant1 = false;
		$participant2 = false;
		$haveAnon = false;

		while(
			( $participant1 === false || $participant2 === false ) &&
			count( $participants )
		) {
			$participant = array_shift( $participants );

			if ( // Special conditions for anonymous users
				$participant->isAnon() &&
				$haveAnon && // Try not to show two anonymous users
				count( $participants ) // Unless we have no other option
			) {
				continue;
			} elseif ( $participant->isAnon() ) {
				$haveAnon = true;
			}

			$text = self::getUserText( $participant );

			if ( !$text || !$participant ) {
				continue;
			}

			if ( $participant1 === false ) {
				$participant1 = $text;
			} else {
				$participant2 = $text;
			}
		}

		return wfMessage(
			'flow-topic-participants',
			$participantCount,
			max( 0, $participantCount - 2 ),
			$participant1,
			$participant2
		)->parse();
	}

	/**
	 * Gets a Flow-formatted plaintext human-readable identifier for a user.
	 * Usually the user's name, but it can also return "an anonymous user",
	 * or information about an item's moderation state.
	 *
	 * @param  User             $user    The User object to get a description of.
	 * @param  AbstractRevision $rev     An AbstractRevision object to retrieve moderation state from.
	 * @param  bool             $showIPs Whether or not to show IP addresses for anonymous users
	 * @return String                    A human-readable identifier for the given User.
	 */
	static public function getUserText( $user, $rev = null, $showIPs = false ) {
		if ( $user === false && $rev instanceof AbstractRevision ) {
			$state = $rev->getModerationState();

			if ( $rev->getModeratedByUserId() ) {
				$user = User::newFromId( $rev->getModeratedByUserId() );
			} else {
				$user = User::newFromName( $rev->getModeratedByUserName() );
			}

			$moderatedAt = new MWTimestamp( $rev->getModerationTimestamp );

			return wfMessage(
					AbstractRevision::$perms[$state]['content'],
					self::getUserText( $user ),
					$moderatedAt->getHumanTimestamp()
			);
		} elseif ( $user === false ) {
			return wfMessage( 'flow-user-moderated' );
		} elseif ( $user->isAnon() && !$showIPs ) {
			return wfMessage( 'flow-user-anonymous' );
		} else {
			return $user->getName();
		}
	}
}
