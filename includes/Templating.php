<?php

namespace Flow;

use Flow\Block\Block;
use Flow\Block\TopicBlock;
use Flow\Data\UserNameBatch;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\View\PostActionMenu;
use OutputPage;
// These dont really belong here
use Html;
use Linker;
use LinkBatch;
use MWTimestamp;
use RequestContext;
use User;
use Flow\Exception\InvalidDataException;

class Templating {
	/**
	 * @var UrlGenerator
	 */
	public $urlGenerator;

	/**
	 * @var OutputPage
	 */
	protected $output;

	/**
	 * @var array
	 */
	protected $namespaces;

	/**
	 * @var array
	 */
	protected $globals;

	/**
	 * @var Redlinks
	 */
	protected $redlinks;

	/**
	 * @param UrlGenerator $urlGenerator
	 * @param OutputPage $output
	 * @param array $namespaces
	 * @param array $globals
	 */
	public function __construct( UserNameBatch $usernames, UrlGenerator $urlGenerator, OutputPage $output, Redlinker $redlinks, array $namespaces = array(), array $globals = array() ) {
		$this->usernames = $usernames;
		$this->urlGenerator = $urlGenerator;
		$this->output = $output;
		foreach ( $namespaces as $ns => $path ) {
			$this->addNamespace( $ns, $path );
		}
		$this->globals = $globals;
		$this->redlinks = $redlinks;
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
			throw new InvalidDataException( 'Unknown template namespace', 'fail-load-data' );
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
			throw new InvalidDataException( 'Cannot render topic with ' . __METHOD__, 'fail-load-data' );
		}

		// An ideal world may pull this from the container, but for now this is fine.  This templating
		// class has too many responsibilities to keep receiving all required objects in the constructor.
		$actionMenu = $this->createActionMenu( $post, $block );
		$view = new View\Post(
			$this->globals['user'], // There is no guarantee of this existing
			$post,
			$actionMenu,
			$this->usernames
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
			$this->getActionPermissions( $this->globals['user'] ),
			$block,
			$post,
			$this->globals['editToken']
		);
	}

	// An ideal world may pull this from the container, but for now this is fine.  This templating
	// class has too many responsibilities to keep receiving all required objects in the constructor.
	public function getActionPermissions( User $user = null ) {
		// if no user defined, assume anonymous user
		if ( !$user instanceof User ) {
			$user = new User;
		}

		return new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );
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
			$originalPoster ? $this->usernames->get( wfWikiId(), $originalPoster[0], $originalPoster[1] ) : '',
			$mostRecentPoster ? $this->usernames->get( wfWikiId(), $mostRecentPoster[0], $mostRecentPoster[1] ) : '',
			$secondMostRecentPoster ? $this->usernames->get( wfWikiId(), $secondMostRecentPoster[0], $secondMostRecentPoster[1] ) : ''
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
		$permissions = $this->getActionPermissions( $permissionsUser );
		if ( $permissions->isAllowed( $revision, 'view' ) ) {
			return $this->usernames->get( wfWikiId(), $revision->getUserId(), $revision->getUserIp() );
		} else {
			$username = $this->usernames->get(
				wfWikiId(),
				$revision->getModeratedByUserId(),
				$revision->getModeratedByUserIp()
			);
			$state = $revision->getModerationState();
			// Messages: flow-hide-usertext, flow-delete-usertext, flow-suppress-usertext
			$message = wfMessage( "flow-$state-usertext", $username );
			if ( $message->exists() ) {
				return $message->text();
			} else {
				wfWarn( __METHOD__ . ': Failed to locate message for moderated content: ' . $message->getKey() );
				return wfMessage( 'flow-error-other' )->text();
			}
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
		$permissions = $this->getActionPermissions( $permissionsUser );
		if ( $permissions->isAllowed( $revision, 'view' ) ) {
			$userid = $revision->getUserId();
			$username = $this->usernames->get( wfWikiId(), $revision->getUserId(), $revision->getUserIp() );
			return Linker::userLink( $userid, $username ) . Linker::userToolLinks( $userid, $username );
		} else {
			$state = $revision->getModerationState();
			$userid = $revision->getModeratedByUserId();
			$username = $this->usernames->get(
				wfWikiId(),
				$revision->getModeratedByUserId(),
				$revision->getModeratedByUserIp()
			);

			// Messages: flow-hide-usertext, flow-delete-usertext, flow-suppress-usertext
			$message = wfMessage( "flow-$state-usertext", $username );
			if ( $message->exists() ) {
				return $message->escaped();
			} else {
				wfWarn( __METHOD__ . ': Failed to locate message for moderated content: ' . $message->getKey() );
				return wfMessage( 'flow-error-other' )->escaped();
			}
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
		$permissions = $this->getActionPermissions( $permissionsUser );
		if ( $permissions->isAllowed( $revision, 'view' ) ) {
			return $this->usernames->get(
				wfWikiId(),
				$revision->getCreatorId(),
				$revision->getCreatorIp()
			);
		} else {
			$state = $revision->getModerationState();
			$username = $this->usernames->get(
				wfWikiId(),
				$revision->getModeratedByUserId(),
				$revision->getModeratedByUserIp()
			);
			// Messages: flow-hide-usertext, flow-delete-usertext, flow-suppress-usertext
			$message = wfMessage( "flow-$state-usertext", $username );

			if ( $message->exists() ) {
				return $message->text();
			} else {
				wfWarn( __METHOD__ . ': Failed to locate message for moderated content: ' . $message->getKey() );
				return wfMessage( 'flow-error-other' )->text();
			}
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
	 * @return string HTML
	 */
	public function getContent( AbstractRevision $revision, $format = 'html', User $permissionsUser = null ) {
		$permissions = $this->getActionPermissions( $permissionsUser );
		if ( $permissions->isAllowed( $revision, 'view' ) ) {
			$content = $revision->getContent( $format );

			if ( $format === 'html' ) {
				// Parsoid doesn't render redlinks
				try {
					$content = $this->redlinks->apply( $content );
				} catch ( \Exception $e ) {
					wfDebugLog( __CLASS__, __METHOD__ . ': Failed applying redlinks for rev_id = ' . $revision->getRevisionId()->getHex() );
					\MWExceptionHandler::logException( $e );
				}

			}

			return $content;
		} else {
			$username = $this->usernames->get(
				wfWikiId(),
				$revision->getModeratedByUserId(),
				$revision->getModeratedByUserIp()
			);

			// get revision type to make more precise message
			$state = $revision->getModerationState();
			$type = $revision->getRevisionType();
			if ( $type == 'post' && $revision->isTopicTitle() ) {
				$type = 'title';
			}

			// Messages: flow-hide-post-content, flow-delete-post-content, flow-suppress-post-content
			//           flow-hide-title-content, flow-delete-title-content, flow-suppress-title-content
			$message = wfMessage( "flow-$state-$type-content", $user, $this->getUserLinks( $revision, $user ) );
			if ( $message->exists() ) {
				return $message->escaped();
			} else {
				wfWarn( __METHOD__ . ': Failed to locate message for moderated content: ' . $message->getKey() );

				return wfMessage( 'flow-error-other' )->escaped();
			}
		}
	}

	public function registerParsoidLinks( AbstractRevision $revision ) {
		$this->redlinks->registerPost( $revision );
	}
}
