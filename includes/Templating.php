<?php

namespace Flow;

use Flow\Repository\UserNameBatch;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Parsoid\Controller as ContentFixer;
use OutputPage;
// These don't really belong here
use Linker;
use Message;

/**
 * This class is slowly being deprecated. It used to house a minimalist
 * php templating system, it is now just a few of the helpers that were
 * reused in the new api responses and other parts of Flow.
 */
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
	 * @var ContentFixer
	 */
	protected $contentFixer;

	/**
	 * @param UserNameBatch $usernames
	 * @param UrlGenerator $urlGenerator
	 * @param OutputPage $output
	 * @param ContentFixer $contentFixer
	 * @param RevisionActionPermissions $permissions
	 */
	public function __construct(
		UserNameBatch $usernames,
		UrlGenerator $urlGenerator,
		OutputPage $output,
		ContentFixer $contentFixer,
		RevisionActionPermissions $permissions
	) {
		$this->usernames = $usernames;
		$this->urlGenerator = $urlGenerator;
		$this->output = $output;
		$this->contentFixer = $contentFixer;
		$this->permissions = $permissions;
	}

	/**
	 * @return OutputPage
	 */
	public function getOutput() {
		return $this->output;
	}

	public function getUrlGenerator() {
		return $this->urlGenerator;
	}

	public function generateUrl( $workflow, $action = 'view', array $query = array() ) {
		return $this->getUrlGenerator()->generateUrl( $workflow, $action, $query );
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
			static $cache;
			$userid = $revision->getUserId();
			if ( isset( $cache[$userid] ) ) {
				return $cache[$userid];
			}
			$username = $this->usernames->get( wfWikiId(), $revision->getUserId(), $revision->getUserIp() );
			return $cache[$userid] = Linker::userLink( $userid, $username ) . Linker::userToolLinks( $userid, $username );
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
	 * Usually the revisions's content can just be displayed. In the event
	 * of moderation, however, that info should not be exposed.
	 *
	 * If a specific i18n message is available for a certain moderation level,
	 * that message will be returned (well, unless the user actually has the
	 * required permissions to view the full content). Otherwise, in normal
	 * cases, the full content will be returned.
	 *
	 * The content-type of the return value varys on the $format parameter.
	 * Further processing in the final output stage must escape all formats
	 * other than the default 'html'.
	 *
	 * @param AbstractRevision $revision Revision to display content for
	 * @param string[optional] $format Format to output content in (html|wikitext)
	 * @return string HTML if requested, otherwise plain text
	 */
	public function getContent( AbstractRevision $revision, $format = 'html' ) {
		$allowed = $this->permissions->isAllowed( $revision, 'view' );
		// Post's require view access to the topic title as well
		if ( $allowed && $revision instanceof PostRevision && !$revision->isTopicTitle() ) {
			$allowed = $this->permissions->isAllowed(
				$revision->getRootPost(),
				'view'
			);
		}

		if ( $allowed ) {
			// html format
			if ( $format === 'html' ) {
				// Parsoid doesn't render redlinks & doesn't strip bad images
				try {
					$content = $this->contentFixer->getContent( $revision );
				} catch ( \Exception $e ) {
					wfDebugLog( 'Flow', __METHOD__ . ': Failed fix content for rev_id = ' . $revision->getRevisionId()->getAlphadecimal() );
					\MWExceptionHandler::logException( $e );

					$content = wfMessage( 'flow-stub-post-content' )->parse();
				}
			// all other formats
			} else {
				$content = $revision->getContent( $format );
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
			if ( !$message->exists() ) {
				wfDebugLog( 'Flow', __METHOD__ . ': Failed to locate message for moderated content: ' . $message->getKey() );

				$message = wfMessage( 'flow-error-other' );
			}

			if ( $format === 'html' ) {
				return $message->escaped();
			} else {
				return $message->text();
			}
		}
	}

	public function getModeratedRevision( AbstractRevision $revision ) {
		if ( $revision->isModerated() ) {
			return $revision;
		} else {
			try {
				return Container::get( 'collection.cache' )->getLastRevisionFor( $revision );
			} catch ( FlowException $e ) {
				wfDebugLog( 'Flow', "Failed loading last revision for revid " . $revision->getRevisionId()->getAlphadecimal() . " with collection id " . $revision->getCollectionId()->getAlphadecimal() );
				throw $e;
			}
		}
	}
}
