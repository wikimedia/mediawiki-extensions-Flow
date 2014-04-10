<?php

namespace Flow;

use Flow\Block\Block;
use Flow\Block\TopicBlock;
use Flow\Block\HeaderBlock;
use Flow\Data\UserNameBatch;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\Parsoid\Controller as ContentFixer;
use Flow\View\PostActionMenu;
use OutputPage;
use User;
// These don't really belong here
use Html;
use Linker;
use Message;
use Flow\Exception\InvalidDataException;

class Templating {
	/**
	 * @var UserNameBatch
	 */
	protected $usernames;

	/**
	 * @var UrlGenerator
	 */
	public $urlGenerator;

	/**
	 * @var OutputPage
	 */
	protected $output;

	/**
	 * @var RevisionActionPermissions
	 */
	protected $permissions;

	/**
	 * @var string[]
	 */
	protected $namespaces;

	/**
	 * @var array
	 */
	protected $globals;

	/**
	 * @var ContentFixer
	 */
	protected $contentFixer;

	/**
	 * @param UserNameBatch $usernames
	 * @param UrlGenerator $urlGenerator
	 * @param OutputPage $output
	 * @param ContentFixer $contentFixer
	 * @param string[] $namespaces
	 * @param array $globals
	 */
	public function __construct( UserNameBatch $usernames, UrlGenerator $urlGenerator, OutputPage $output, ContentFixer $contentFixer, array $namespaces = array(), array $globals = array() ) {
		$this->usernames = $usernames;
		$this->urlGenerator = $urlGenerator;
		$this->output = $output;
		foreach ( $namespaces as $ns => $path ) {
			$this->addNamespace( $ns, $path );
		}
		$this->globals = $globals;
		$this->contentFixer = $contentFixer;
		// meh ... but the constructor is already huge
		$this->permissions = $globals['permissions'];
	}

	/**
	 * @return OutputPage
	 */
	public function getOutput() {
		return $this->output;
	}

	/**
	 * @return RevisionActionPermissions
	 */
	public function getActionPermissions() {
		return $this->permissions;
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
			return '';
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

	public function renderPost( PostRevision $post, TopicBlock $block, $return = true ) {
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
			$this->urlGenerator,
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
				'postActionMenu' => $actionMenu,
				'moderatedByUser' => $this->usernames->get(
					wfWikiId(),
					$post->getModeratedByUserId(),
					$post->getModeratedByUserIp()
				),
				'userLink' => $this->getUserLinks( $post )
			),
			$return
		);
	}

	public function renderTopic( PostRevision $root, TopicBlock $block, $return = true ) {
		$actionMenu = $this->createActionMenu( $root, $block );
		$view = new View\Post(
			$this->globals['user'], // There is no guarantee of this existing
			$root,
			$actionMenu,
			$this->urlGenerator,
			$this->usernames
		);
		if ( !$actionMenu->isAllowed( 'view' ) ) {
			return '';
		}

		return $this->render( "flow:topic.html.php", array(
			'block' => $block,
			'topic' => $block->getWorkflow(),
			'root' => $root,
			'postActionMenu' => $actionMenu,
			'postView' => $view
		), $return );
	}

	public function renderHeader( Header $header = null, HeaderBlock $block, User $user, $template = '', $return = true ) {
		if ( !$template ) {
			$template = 'flow:header.html.php';
		}
		return $this->render( $template, array(
			'block' => $block,
			'workflow' => $block->getWorkflow(),
			'header' => $header,
			'user' => $user,
		), $return );
	}

	// An ideal world may pull this from the container, but for now this is fine.  This templating
	// class has too many responsibilities to keep receiving all required objects in the constructor.
	protected function createActionMenu( PostRevision $post, Block $block ) {
		$container = Container::getContainer();

		return new PostActionMenu(
			$this->urlGenerator,
			$container['flow_actions'],
			$this->permissions,
			$block,
			$post,
			$this->globals['editToken']
		);
	}

	/**
	 * @param Block $block
	 * @param string $direction
	 * @param string $offset
	 * @param integer $limit
	 * @return string Html
	 */
	public function getPagingLink( Block $block, $direction, $offset, $limit ) {
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
						$block->getName().'_offset-id' => $offset,
						$block->getName().'_offset-dir' => $direction,
						$block->getName().'_limit' => $limit,
					)
				),
			),
			wfMessage( 'flow-paging-'.$direction )->text()
		);

		$output = \Html::rawElement(
			'div',
			array(
				'class' => 'flow-paging flow-paging-'.$direction,
				'data-offset' => $offset,
				'data-direction' => $direction,
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

		if ( $userText instanceof Message ) {
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
	 * @return string Participant list (escaped HTML)
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
		)->escaped();
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
	 * @return string
	 */
	public function getUserText( AbstractRevision $revision ) {
		// if this specific revision is moderated, its usertext can always be
		// displayed, since it will be the moderator user
		if ( $revision->isModerated() || $this->permissions->isAllowed( $revision, 'view' ) ) {
			return $this->usernames->get( wfWikiId(), $revision->getUserId(), $revision->getUserIp() );
		} else {
			$revision = $this->getModeratedRevision( $revision );
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
	 * @return string                            HTML
	 */
	public function getUserLinks( AbstractRevision $revision ) {
		// if this specific revision is moderated, its usertext can always be
		// displayed, since it will be the moderator user
		if ( $revision->isModerated() || $this->permissions->isAllowed( $revision, 'view' ) ) {
			$userid = $revision->getUserId();
			$username = $this->usernames->get( wfWikiId(), $revision->getUserId(), $revision->getUserIp() );
			return Linker::userLink( $userid, $username ) . Linker::userToolLinks( $userid, $username );
		} else {
			$revision = $this->getModeratedRevision( $revision );
			$state = $revision->getModerationState();
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

	public function getUsernames() {
		return $this->usernames;
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
	 * @return string
	 */
	public function getCreatorText( PostRevision $revision ) {
		if ( $this->permissions->isAllowed( $revision, 'view' ) ) {
			return $this->usernames->get(
				wfWikiId(),
				$revision->getCreatorId(),
				$revision->getCreatorIp()
			);
		} else {
			$revision = $this->getModeratedRevision( $revision );
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
	 * @return string HTML
	 */
	public function getContent( AbstractRevision $revision, $format = 'html' ) {
		if ( $this->permissions->isAllowed( $revision, 'view' ) ) {
			$content = $revision->getContent( $format );

			if ( $format === 'html' ) {
				// Parsoid doesn't render redlinks & doesn't strip bad images
				try {
					$content = $this->contentFixer->getContent( $revision );
				} catch ( \Exception $e ) {
					wfDebugLog( 'Flow', __METHOD__ . ': Failed fix content for rev_id = ' . $revision->getRevisionId()->getAlphadecimal() );
					\MWExceptionHandler::logException( $e );
				}
			}

			return $content;
		} else {
			$revision = $this->getModeratedRevision( $revision );
			$username = $this->usernames->get(
				wfWikiId(),
				$revision->getModeratedByUserId(),
				$revision->getModeratedByUserIp()
			);

			// get revision type to make more precise message
			$state = $revision->getModerationState();
			$type = $revision->getRevisionType();
			if ( $revision instanceof PostRevision && $revision->isTopicTitle() ) {
				$type = 'title';
			}

			// Messages: flow-hide-post-content, flow-delete-post-content, flow-suppress-post-content
			//           flow-hide-title-content, flow-delete-title-content, flow-suppress-title-content
			$message = wfMessage( "flow-$state-$type-content", $username )->rawParams( $this->getUserLinks( $revision ) );
			if ( $message->exists() ) {
				return $message->escaped();
			} else {
				//wfWarn( __METHOD__ . ': Failed to locate message for moderated content: ' . $message->getKey() );

				return wfMessage( 'flow-error-other' )->escaped();
			}
		}
	}

	public function getModeratedContent( AbstractRevision $revision ) {
		$state = $revision->getModerationState();
		if ( !$revision->isModerated() ) {
			return '';
		}
		$revision = $this->getModeratedRevision( $revision );
		$username = $this->usernames->get(
			wfWikiId(),
			$revision->getModeratedByUserId(),
			$revision->getModeratedByUserIp()
		);

		// get revision type to make more precise message
		$type = $revision->getRevisionType();
		if ( $revision instanceof PostRevision && $revision->isTopicTitle() ) {
			$type = 'title';
		}

		// Messages: flow-hide-post-content, flow-delete-post-content, flow-suppress-post-content
		//           flow-hide-title-content, flow-delete-title-content, flow-suppress-title-content
		$message = wfMessage( "flow-$state-$type-content", $username )->rawParams( $this->getUserLinks( $revision ) );

		if ( $message->exists() ) {
			return $message->escaped();
		} else {
			wfWarn( __METHOD__ . ': Failed to locate message for moderated content: ' . $message->getKey() );

			return wfMessage( 'flow-error-other' )->escaped();
		}
	}

	public function registerParsoidLinks( PostRevision $revision ) {
		if ( $revision instanceof PostRevision ) {
			$this->contentFixer->registerRecursive( $revision );
		} elseif ( $revision instanceof Header ) {
			// @todo
		}
	}

	public function getModeratedRevision( AbstractRevision $revision ) {
		if ( $revision->isModerated() ) {
			return $revision;
		} else {
			return Container::get( 'collection.cache' )->getLastRevisionFor( $revision );
		}
	}
}
