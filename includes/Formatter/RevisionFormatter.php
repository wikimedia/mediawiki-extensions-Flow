<?php

namespace Flow\Formatter;

use Flow\Collection\PostCollection;
use Flow\Repository\UserNameBatch;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\Anchor;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use Flow\UrlGenerator;
use GenderCache;
use IContextSource;
use Message;

/**
 * This implements a serializer for converting revision objects
 * into an array of localized and sanitized data ready for user
 * consumption.
 *
 * The formatApi method is the primary method of interacting with
 * this serializer. The results of formatApi can be passed on to
 * html formatting or emitted directly as an api response.
 *
 * For performance considerations of special purpose formatters like
 * CheckUser methods that build pieces of the api response are also
 * public.
 *
 * @todo can't output as api yet, Message instances are returned
 *  for the various strings.
 *
 * @todo this needs a better name, RevisionSerializer? not sure yet
 */
class RevisionFormatter {

	/**
	 * @var RevisionActionPermissions
	 */
	protected $permissions;

	/**
	 * @var Templating
	 */
	protected $templating;

	/**
	 * @var UrlGenerator;
	 */
	protected $urlGenerator;

	/**
	 * @var bool
	 */
	protected $includeProperties = false;

	/**
	 * @var bool
	 */
	protected $includeContent = true;

	/**
	 * @var string[]
	 */
	protected $allowedContentFormats = array( 'html', 'wikitext' );

	/**
	 * @var string Default content format for revision output
	 */
	protected $contentFormat = 'html';

	/**
	 * @var array Map from alphadeicmal revision id to content format ovverride
	 */
	protected $revisionContentFormat = array();

	/**
	 * @var int
	 */
	protected $maxThreadingDepth;

	/**
	 * @var Message[]
	 */
	protected $messages = array();

	/**
	 * @var array
	 */
	protected $userLinks = array();

	/**
	 * @var UserNameBatch
	 */
	protected $usernames;

	/**
	 * @var GenderCache
	 */
	protected $genderCache;

	/**
	 * @param RevisionActionPermissions $permissions
	 * @param Templating $templating
	 * @param UserNameBatch $usernames
	 * @param int $maxThreadingDepth
	 */
	public function __construct(
		RevisionActionPermissions $permissions,
		Templating $templating,
		UserNameBatch $usernames,
		$maxThreadingDepth
	) {
		$this->permissions = $permissions;
		$this->templating = $templating;
		$this->urlGenerator = $this->templating->getUrlGenerator();
		$this->usernames = $usernames;
		$this->genderCache = GenderCache::singleton();
		$this->maxThreadingDepth = $maxThreadingDepth;
	}

	/**
	 * The self::buildProperties method is fairly expensive and only used for rendering
	 * history entries.  As such it is optimistically disabled unless requested
	 * here
	 *
	 * @param bool $shouldInclude
	 */
	public function setIncludeHistoryProperties( $shouldInclude ) {
		$this->includeProperties = (bool)$shouldInclude;
	}

	/**
	 * Outputing content can be somehwat expensive, as most of the content is loaded
	 * into DOMDocuemnts for processing of relidlinks and badimages.  Set this to false
	 * if the content will not be used such as for recent changes.
	 */
	public function setIncludeContent( $shouldInclude ) {
		$this->includeContent = (bool)$shouldInclude;
	}

	public function setContentFormat( $format, UUID $revisionId = null ) {
		if ( false === array_search( $format, $this->allowedContentFormats ) ) {
			throw new FlowException( "Unknown content format: $format" );
		}
		if ( $revisionId === null ) {
			// set default content format
			$this->contentFormat = $format;
		} else {
			// set per-revision content format
			$this->revisionContentFormat[$revisionId->getAlphadecimal()] = $format;
		}
	}

	/**
	 * @param FormatterRow $row
	 * @param IContextSource $ctx
	 * @return array|false
	 */
	public function formatApi( FormatterRow $row, IContextSource $ctx ) {
		$language = $ctx->getLanguage();
		$user = $ctx->getUser();
		// @todo the only permissions currently checked in this class are prev-revision
		// mostly permissions is used for the actions,  figure out how permissions should
		// fit into this class either used more or not at all.
		if ( $user->getName() !== $this->permissions->getUser()->getName() ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Formatting for wrong user' );
			return false;
		}

		$isContentAllowed = $this->includeContent && $this->permissions->isAllowed( $row->revision, 'view' );
		$isHistoryAllowed = $isContentAllowed ?: $this->permissions->isAllowed( $row->revision, 'history' );

		if ( !$isHistoryAllowed ) {
			return array();
		}

		$moderatedRevision = $this->templating->getModeratedRevision( $row->revision );
		$ts = $row->revision->getRevisionId()->getTimestampObj();
		$res = array(
			// Change all '_BC_bools' to ApiResult::META_BC_BOOLS when core
			// change is merged.
			'_BC_bools' => array(
				// https://gerrit.wikimedia.org/r/#/c/182858/
				'isOriginalContent',
				'isModerated',
			),
			'workflowId' => $row->workflow->getId()->getAlphadecimal(),
			'articleTitle' => $row->workflow->getArticleTitle()->getPrefixedText(),
			'revisionId' => $row->revision->getRevisionId()->getAlphadecimal(),
			'timestamp' => $ts->getTimestamp( TS_MW ),
			'changeType' => $row->revision->getChangeType(),
			// @todo push all date formatting to the render side?
			'dateFormats' => $this->getDateFormats( $row->revision, $ctx ),
			'properties' => $this->buildProperties( $row->workflow->getId(), $row->revision, $ctx, $row ),
			'isOriginalContent' => $row->revision->isOriginalContent(),
			'isModerated' => $moderatedRevision->isModerated(),
			// These are read urls
			'links' => $this->buildLinks( $row ),
			// These are write urls
			'actions' => $this->buildActions( $row ),
			'size' => array(
				'old' => $row->revision->getPreviousContentLength(),
				'new' => $row->revision->getContentLength(),
			),
			'author' => $this->serializeUser(
				$row->revision->getUserWiki(),
				$row->revision->getUserId(),
				$row->revision->getUserIp()
			),
			'lastEditUser' => $this->serializeUser(
				$row->revision->getLastContentEditUserWiki(),
				$row->revision->getLastContentEditUserId(),
				$row->revision->getLastContentEditUserIp()
			),
			'lastEditId' => $row->revision->isOriginalContent() ? null : $row->revision->getLastContentEditId()->getAlphadecimal(),
			'previousRevisionId' => $row->revision->isFirstRevision()
				? null
				: $row->revision->getPrevRevisionId()->getAlphadecimal(),
		);

		if ( $res['isModerated'] ) {
			$res['moderator'] = $this->serializeUser(
				$moderatedRevision->getModeratedByUserWiki(),
				$moderatedRevision->getModeratedByUserId(),
				$moderatedRevision->getModeratedByUserIp()
			);
			// @todo why moderate instead of moderated or something else?
			$res['moderateState'] = $moderatedRevision->getModerationState();
			$res['moderateReason'] = array(
				'content' => $moderatedRevision->getModeratedReason(),
				'format' => 'plaintext',
			);
		}

		if ( $isContentAllowed ) {
			// topic titles are always forced to plain text
			$contentFormat = $this->decideContentFormat( $row->revision );

			// @todo better name?
			$res['content'] = array(
				'content' => $this->templating->getContent( $row->revision, $contentFormat ),
				'format' => $contentFormat
			);
		}

		if ( $row instanceof TopicRow ) {
			$res['_BC_bools'] = array_merge(
				$res['_BC_bools'],
				array(
					'isWatched',
					'watchable',
				)
			);
			if (
				$row->summary &&
				$this->permissions->isAllowed( $row->summary, 'view' )
			) {
				$res['summary']['content'] = $this->templating->getContent( $row->summary, $this->contentFormat );
				$res['summary']['format'] = $this->contentFormat;
				$res['summary']['revId'] = $row->summary->getRevisionId()->getAlphadecimal();
			}

			// Only non-anon users can watch/unwatch a flow topic
			// isWatched - the topic is watched by current user
			// watchable - the user could watch the topic, eg, anon-user can't watch a topic
			if ( !$ctx->getUser()->isAnon() ) {
				// default topic is not watched and topic is not always watched
				$res['isWatched'] = (bool) $row->isWatched;
				$res['watchable'] = true;
			} else {
				$res['watchable'] = false;
			}
		}

		if ( $row->revision instanceof PostRevision ) {
			$res['_BC_bools'] = array_merge(
				$res['_BC_bools'],
				array(
					'isMaxThreadingDepth',
				)
			);

			$replyTo = $row->revision->getReplyToId();
			$res['replyToId'] = $replyTo ? $replyTo->getAlphadecimal() : null;
			$res['postId'] = $row->revision->getPostId()->getAlphadecimal();
			$res['isMaxThreadingDepth'] = $row->revision->getDepth() >= $this->maxThreadingDepth;
			$res['creator'] = $this->serializeUser(
				$row->revision->getCreatorWiki(),
				$row->revision->getCreatorId(),
				$row->revision->getCreatorIp()
			);

			// Always output this along with topic titles so they
			// have a safe parameter to use within l10n for content
			// output.
			if ( $row->revision->isTopicTitle() && !isset( $res['properties']['topic-of-post'] ) ) {
				$res['properties']['topic-of-post'] = $this->processParam(
					'topic-of-post',
					$row->revision,
					$row->workflow->getId(),
					$ctx,
					$row
				);
			}
		}

		return $res;
	}

	/**
	 * @param array $userData Contains `name`, `wiki`, and `gender` keys
	 * @return array
	 */
	public function serializeUserLinks( $userData ) {
		$name = $userData['name'];
		if ( isset( $this->userLinks[$name] ) ) {
			return $this->userLinks[$name];
		}

		$talkPageTitle = null;
		$userTitle = \Title::newFromText( $name, NS_USER );
		if ( $userTitle ) {
			$talkPageTitle = $userTitle->getTalkPage();
		}

		$blockTitle = \SpecialPage::getTitleFor( 'Block', $name );

		$userContribsTitle = \SpecialPage::getTitleFor( 'Contributions', $name );
		$userLinksBCBools = array(
			'_BC_bools' => array(
				'exists',
			),
		);
		$links = array(
			'contribs' => array(
				'url' => $userContribsTitle->getLinkURL(),
				'title' => $userContribsTitle->getText(),
				'exists' => true,
			) + $userLinksBCBools,
			'userpage' => array(
				'url' => $userTitle->getLinkURL(),
				'title' => $userTitle->getText(),
				'exists' => $userTitle->isKnown(),
			) + $userLinksBCBools,
		);

		if ( $talkPageTitle ) {
			$links['talk'] = array(
				'url' => $talkPageTitle->getLinkURL(),
				'title' => $talkPageTitle->getPrefixedText(),
				'exists' => $talkPageTitle->isKnown()
			) + $userLinksBCBools;
		}
		// is this right permissions? typically this would
		// be sourced from Linker::userToolLinks, but that
		// only undertands html strings.
		if ( $this->permissions->getUser()->isAllowed( 'block' ) ) {
			// only is the user has blocking rights
			$links += array(
				"block" => array(
					'url' => $blockTitle->getLinkURL(),
					'title' => wfMessage( 'blocklink' ),
					'exists' => true
				) + $userLinksBCBools,
			);
		}

		return $this->userLinks[$name] = $links;
	}

	public function serializeUser( $userWiki, $userId, $userIp ) {
		$res = array(
			'name' => $this->usernames->get( $userWiki, $userId, $userIp ),
			'wiki' => $userWiki,
			'gender' => 'unknown',
			'links' => array(),
			'id' => $userId
		);
		// Only works for the local wiki
		if ( wfWikiId() === $userWiki ) {
			$res['gender'] = $this->genderCache->getGenderOf( $res['name'], __METHOD__ );
		}
		if ( $res['name'] ) {
			$res['links'] = $this->serializeUserLinks( $res );
		}

		return $res;
	}

	/**
	 * @param AbstractRevision $revision
	 * @param IContextSource $ctx
	 * @return array Contains [timeAndDate, date, time]
	 */
	public function getDateFormats( AbstractRevision $revision, IContextSource $ctx ) {
		// also restricted to history
		if ( $this->includeProperties === false ) {
			return array();
		}

		$timestamp = $revision->getRevisionId()->getTimestampObj()->getTimestamp( TS_MW );
		$user = $ctx->getUser();
		$lang = $ctx->getLanguage();

		return array(
			'timeAndDate' => $lang->userTimeAndDate( $timestamp, $user ),
			'date' => $lang->userDate( $timestamp, $user ),
			'time' => $lang->userTime( $timestamp, $user ),
		);
	}

	/**
	 * @param FormatterRow $row
	 * @return array
	 * @throws FlowException
	 */
	public function buildActions( FormatterRow $row ) {
		$user = $this->permissions->getUser();
		$workflow = $row->workflow;
		$title = $workflow->getArticleTitle();

		// If a user is blocked from performing actions on this page return
		// an empty array of actions.
		//
		// We only check actual users and not anon's because the anonymous
		// version can be cached and served to many different ip addresses
		// which will not all be blocked.
		if ( !$user->isAnon() &&
			( $user->isBlockedFrom( $title, true ) || !$title->quickUserCan( 'edit', $user ) )
		) {
			return array();
		}

		$revision = $row->revision;
		$action = $revision->getChangeType();
		$workflowId = $workflow->getId();
		$revId = $revision->getRevisionId();
		$postId = method_exists( $revision, 'getPostId' ) ? $revision->getPostId() : null;
		$actionTypes = $this->permissions->getActions()->getValue( $action, 'actions' );
		if ( $actionTypes === null ) {
			wfDebugLog( 'Flow', __METHOD__ . ": No actions defined for action: $action" );
			return array();
		}

		// actions primarily vary by revision type...
		$links = array();
		foreach ( $actionTypes as $type ) {
			if ( !$this->permissions->isAllowed( $revision, $type ) ) {
				continue;
			}
			switch( $type ) {
			case 'thank':
				if (
					// thanks extension must be available
					class_exists( 'ThanksHooks' ) &&
					// anons can't give a thank
					!$user->isAnon() &&
					// can only thank for PostRevisions
					// (other revision objects have no getCreator* methods)
					$revision instanceof PostRevision &&
					// only thank a logged in user
					$revision->getCreatorId() > 0 &&
					// can't thank self
					$user->getId() !== $revision->getCreatorId()
				) {
					$links['thank'] = $this->urlGenerator->thankAction( $postId );
				}
				break;

			case 'reply':
				if ( !$postId ) {
					throw new FlowException( "$type called without \$postId" );
				} elseif ( !$revision instanceof PostRevision ) {
					throw new FlowException( "$type called without PostRevision object" );
				}

				/*
				 * If the post being replied to is at or exceeds the max
				 * threading depth, the reply link should point to parent.
				 */
				$replyToId = $postId;
				$replyToRevision = $revision;
				while ( $replyToRevision->getDepth() >= $this->maxThreadingDepth ) {
					$replyToId = $replyToRevision->getReplyToId();
					$replyToRevision = PostCollection::newFromId( $replyToId )->getLastRevision();
				}

				$links['reply'] = $this->urlGenerator->replyAction(
					$title,
					$workflowId,
					$replyToId,
					$revision->isTopicTitle()
				);
				break;

			case 'edit-header':
				$links['edit'] = $this->urlGenerator->editHeaderAction( $title, $workflowId, $revId );
				break;

			case 'edit-title':
				if ( !$postId ) {
					throw new FlowException( "$type called without \$postId" );
				}
				$links['edit'] = $this->urlGenerator
					->editTitleAction( $title, $workflowId, $postId, $revId );
				break;

			case 'edit-post':
				if ( !$postId ) {
					throw new FlowException( "$type called without \$postId" );
				}
				$links['edit'] = $this->urlGenerator
					->editPostAction( $title, $workflowId, $postId, $revId );
				break;

			case 'undo-edit-header':
			case 'undo-edit-post':
			case 'undo-edit-topic-summary':
				if ( !$revision->isFirstRevision() ) {
					$links['undo'] = $this->urlGenerator->undoAction( $revision, $title, $workflowId );
				}
				break;


			case 'hide-post':
				if ( !$postId ) {
					throw new FlowException( "$type called without \$postId" );
				}
				$links['hide'] = $this->urlGenerator->hidePostAction( $title, $workflowId, $postId );
				break;

			case 'delete-topic':
				$links['delete'] = $this->urlGenerator->deleteTopicAction( $title, $workflowId );
				break;

			case 'delete-post':
				if ( !$postId ) {
					throw new FlowException( "$type called without \$postId" );
				}
				$links['delete'] = $this->urlGenerator->deletePostAction( $title, $workflowId, $postId );
				break;

			case 'suppress-topic':
				$links['suppress'] = $this->urlGenerator->suppressTopicAction( $title, $workflowId );
				break;

			case 'suppress-post':
				if ( !$postId ) {
					throw new FlowException( "$type called without \$postId" );
				}
				$links['suppress'] = $this->urlGenerator->suppressPostAction( $title, $workflowId, $postId );
				break;

			case 'lock-topic':
				// lock topic link is only available to topics
				if ( !$revision instanceof PostRevision || !$revision->isTopicTitle() ) {
					continue;
				}

				$links['lock'] = $this->urlGenerator->lockTopicAction( $title, $workflowId );
				break;

			case 'restore-topic':
				$moderateAction = $flowAction = null;
				switch ( $revision->getModerationState() ) {
				case AbstractRevision::MODERATED_LOCKED:
					$moderateAction = 'unlock';
					$flowAction = 'lock-topic';
					break;
				case AbstractRevision::MODERATED_HIDDEN:
				case AbstractRevision::MODERATED_DELETED:
				case AbstractRevision::MODERATED_SUPPRESSED:
					$moderateAction = 'un' . $revision->getModerationState();
					$flowAction = 'moderate-topic';
					break;
				}
				if ( isset( $moderateAction ) && $moderateAction ) {
					$links[$moderateAction] = $this->urlGenerator->restoreTopicAction( $title, $workflowId, $moderateAction, $flowAction );
				}
				break;

			case 'restore-post':
				if ( !$postId ) {
					throw new FlowException( "$type called without \$postId" );
				}
				$moderateAction = $flowAction = null;
				switch( $revision->getModerationState() ) {
				case AbstractRevision::MODERATED_HIDDEN:
				case AbstractRevision::MODERATED_DELETED:
				case AbstractRevision::MODERATED_SUPPRESSED:
					$moderateAction = 'un' . $revision->getModerationState();
					$flowAction = 'moderate-post';
					break;
				}
				if ( $moderateAction ) {
					$links[$moderateAction] = $this->urlGenerator->restorePostAction( $title, $workflowId, $postId, $moderateAction, $flowAction );
				}
				break;

			case 'hide-topic':
				$links['hide'] = $this->urlGenerator->hideTopicAction( $title, $workflowId );
				break;

			// Need to use 'edit-topic-summary' to match FlowActions
			case 'edit-topic-summary':
				// summarize link is only available to topic workflow
				if( !in_array( $workflow->getType(), array( 'topic', 'topicsummary' ) ) ) {
					continue;
				}
				$links['summarize'] = $this->urlGenerator->editTopicSummaryAction( $title, $workflowId );
				break;


			default:
				wfDebugLog( 'Flow', __METHOD__ . ': unkown action link type: ' . $type );
				break;
			}
		}

		return $links;
	}

	/**
	 * @param FormatterRow $row
	 * @return Anchor[]
	 * @throws FlowException
	 */
	public function buildLinks( FormatterRow $row ) {
		$workflow = $row->workflow;
		$revision = $row->revision;
		$title = $workflow->getArticleTitle();
		$action = $revision->getChangeType();
		$workflowId = $workflow->getId();
		$revId = $revision->getRevisionId();
		$postId = method_exists( $revision, 'getPostId' ) ? $revision->getPostId() : null;

		$linkTypes = $this->permissions->getActions()->getValue( $action, 'links' );
		if ( $linkTypes === null ) {
			wfDebugLog( 'Flow', __METHOD__ . ": No links defined for action: $action" );
			return array();
		}

		$links = array();
		foreach ( $linkTypes as $type ) {
			switch( $type ) {
			case 'watch-topic':
				$links['watch-topic'] = $this->urlGenerator->watchTopicLink( $title, $workflowId );
				break;

			case 'unwatch-topic':
				$links['unwatch-topic'] = $this->urlGenerator->unwatchTopicLink( $title, $workflowId );
				break;

			case 'topic':
				$links['topic'] = $this->urlGenerator->topicLink( $title, $workflowId );
				break;

			case 'post':
				if ( !$postId ) {
					wfDebugLog( 'Flow', __METHOD__ . ': No postId available to render post link' );
					break;
				}
				$links['post'] = $this->urlGenerator->postLink( $title, $workflowId, $postId );
				break;

			case 'header-revision':
				$links['header-revision'] = $this->urlGenerator
					->headerRevisionLink( $title, $workflowId, $revId );
				break;

			case 'topic-revision':
				if ( !$postId ) {
					wfDebugLog( 'Flow', __METHOD__ . ': No postId available to render revision link' );
					break;
				}

				$links['topic-revision'] = $this->urlGenerator
					->topicRevisionLink( $title, $workflowId, $revId );
				break;

			case 'post-revision':
				if ( !$postId ) {
					wfDebugLog( 'Flow', __METHOD__ . ': No postId available to render revision link' );
					break;
				}

				$links['post-revision'] = $this->urlGenerator
					->postRevisionLink( $title, $workflowId, $postId, $revId );
				break;

			case 'post-history':
				if ( !$postId ) {
					wfDebugLog( 'Flow', __METHOD__ . ': No postId available to render post-history link' );
					break;
				}
				$links['post-history'] = $this->urlGenerator->postHistoryLink( $title, $workflowId, $postId );
				break;

			case 'topic-history':
				$links['topic-history'] = $this->urlGenerator->workflowHistoryLink( $title, $workflowId );
				break;

			case 'board-history':
				$links['board-history'] = $this->urlGenerator->boardHistoryLink( $title );
				break;

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'diff-header':
				$diffCallback = isset( $diffCallback ) ? $diffCallback : array( $this->urlGenerator, 'diffHeaderLink' );
				// don't break, diff links are rendered below
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'diff-post':
				$diffCallback = isset( $diffCallback ) ? $diffCallback : array( $this->urlGenerator, 'diffPostLink' );
				// don't break, diff links are rendered below
			case 'diff-post-summary':
				$diffCallback = isset( $diffCallback ) ? $diffCallback : array( $this->urlGenerator, 'diffSummaryLink' );

				/*
				 * To diff against previous revision, we don't really need that
				 * revision id; if no particular diff id is specified, it will
				 * assume a diff against previous revision. However, we do want
				 * to make sure that a previous revision actually exists to diff
				 * against. This could result in a network request (fetching the
				 * current revision), but it's likely being loaded anyways.
				 */
				if ( $revision->getPrevRevisionId() !== null ) {
					$links['diff'] = call_user_func( $diffCallback, $title, $workflowId, $revId );

					/*
					 * Different formatters have different terminology for the link
					 * that diffs a certain revision to the previous revision.
					 *
					 * E.g.: Special:Contributions has "diff" ($links['diff']),
					 * ?action=history has "prev" ($links['prev']).
					 */
					$links['diff-prev'] = clone $links['diff'];
					$links['diff-prev']->message = wfMessage( 'last' );
				}

				/*
				 * To diff against the current revision, we need to know the id
				 * of this last revision. This could be an additional network
				 * request, though anything using formatter likely already needs
				 * to request the most current revision (e.g. to check
				 * permissions) so we should be able to get it from local cache.
				 */
				$cur = $row->currentRevision;
				if ( !$revId->equals( $cur->getRevisionId() ) ) {
					$links['diff-cur'] = call_user_func( $diffCallback, $title, $workflowId, $cur->getRevisionId(), $revId );
					$links['diff-cur']->message = wfMessage( 'cur' );
				}
				break;

			case 'workflow':
				$links['workflow'] = $this->urlGenerator->workflowLink( $title, $workflowId );
				break;

			default:
				wfDebugLog( 'Flow', __METHOD__ . ': unkown action link type: ' . $type );
				break;
			}
		}


		return $links;
	}

	/**
	 * Build api properties defined in FlowActions for this change type
	 *
	 * This is a fairly expensive function(compared to the other methods in this class).
	 * As such its only output when specifically requested
	 *
	 * @param UUID $workflowId
	 * @param AbstractRevision $revision
	 * @param IContextSource $ctx
	 * @param FormatterRow|null $row
	 * @return array
	 */
	public function buildProperties(
		UUID $workflowId,
		AbstractRevision $revision,
		IContextSource $ctx,
		FormatterRow $row = null
	) {
		if ( $this->includeProperties === false ) {
			return array();
		}

		$changeType = $revision->getChangeType();
		$actions = $this->permissions->getActions();
		$params = $actions->getValue( $changeType, 'history', 'i18n-params' );
		if ( !$params ) {
			// should we have a sigil for i18n with no parameters?
			wfDebugLog( 'Flow', __METHOD__ . ": No i18n params for changeType $changeType on " . $revision->getRevisionId()->getAlphadecimal() );
			return array();
		}

		$res = array( '_key' => $actions->getValue( $changeType, 'history', 'i18n-message' ) );
		foreach ( $params as $param ) {
			$res[$param] = $this->processParam( $param, $revision, $workflowId, $ctx, $row );
		}

		return $res;
	}

	/**
	 * Mimic Echo parameter formatting
	 *
	 * @param string $param The requested i18n parameter
	 * @param AbstractRevision|AbstractRevision[] $revision The revision or
	 *  revisions to format or an array of revisions
	 * @param UUID $workflowId The UUID of the workflow $revision belongs tow
	 * @param IContextSource $ctx
	 * @param FormatterRow|null $row
	 * @return mixed A valid parameter for a core Message instance. These
	 *  parameters will be used with Message::parse
	 * @throws FlowException
	 */
	public function processParam(
		$param,
		$revision,
		UUID $workflowId,
		IContextSource $ctx,
		FormatterRow $row = null
	) {
		switch ( $param ) {
		case 'creator-text':
			if ( $revision instanceof PostRevision ) {
				return $this->usernames->getFromTuple( $revision->getCreatorTuple() );
			} else {
				return '';
			}

		case 'user-text':
			return $this->usernames->getFromTuple( $revision->getUserTuple() );

		case 'user-links':
			return Message::rawParam( $this->templating->getUserLinks( $revision ) );

		case 'summary':
			/*
			 * Fetch in HTML; unparsed wikitext in summary is pointless.
			 * Larger-scale wikis will likely also store content in html, so no
			 * Parsoid roundtrip is needed then (and if it *is*, it'll already
			 * be needed to render Flow discussions, so this is manageable)
			 */
			$content = $this->templating->getContent( $revision, 'html' );
			// strip html tags and decode to plaintext
			$content = Utils::htmlToPlaintext( $content, 140, $ctx->getLanguage() );
			return Message::plaintextParam( $content );

		case 'wikitext':
			$content = $this->templating->getContent( $revision, 'wikitext' );
			// This must be escaped and marked raw to prevent special chars in
			// content, like $1, from changing the i18n result
			return Message::plaintextParam( $content );

		// This is potentially two networked round trips, much too expensive for
		// the rendering loop
		case 'prev-wikitext':
			if ( $revision->isFirstRevision() ) {
				return '';
			}
			if ( $row === null ) {
				$previousRevision = $revision->getCollection()->getPrevRevision( $revision );
			} else {
				$previousRevision = $row->previousRevision;
			}
			if ( !$previousRevision ) {
				return '';
			}
			if ( !$this->permissions->isAllowed( $previousRevision, 'view' ) ) {
				return '';
			}

			$content = $this->templating->getContent( $previousRevision, 'wikitext' );
			return Message::plaintextParam( $content );

		case 'workflow-url':
			return $this->urlGenerator
				->workflowLink( null, $workflowId )
				->getFullUrl();

		case 'post-url':
			if ( !$revision instanceof PostRevision ) {
				throw new FlowException( 'Expected PostRevision but received' . get_class( $revision ) );
			}
			return $this->urlGenerator
				->postLink( null, $workflowId, $revision->getPostId() )
				->getFullUrl();

		case 'moderated-reason':
			// don-t parse wikitext in the moderation reason
			return Message::plaintextParam( $revision->getModeratedReason() );

		case 'topic-of-post':
			if ( !$revision instanceof PostRevision ) {
				throw new FlowException( 'Expected PostRevision but received ' . get_class( $revision ) );
			}
			$root = $revision->getRootPost();
			$content = $this->templating->getContent( $root, 'wikitext' );

			return Message::plaintextParam( $content );

		case 'post-of-summary':
			if ( !$revision instanceof PostSummary ) {
				throw new FlowException( 'Expected PostSummary but received ' . get_class( $revision ) );
			}
			/** @var PostRevision $post */
			$post = $revision->getCollection()->getPost()->getLastRevision();
			if ( $post->isTopicTitle() ) {
				return Message::plaintextParam( $this->templating->getContent( $post, 'wikitext' ) );
			} else {
				return Message::rawParam( $this->templating->getContent( $post, 'html' ) );
			}

		case 'bundle-count':
			return Message::numParam( count( $revision ) );

		default:
			wfWarn( __METHOD__ . ': Unknown formatter parameter: ' . $param );
			return '';
		}
	}

	protected function msg( $key /*...*/ ) {
		$params = func_get_args();
		if ( count( $params ) !== 1 ) {
			array_shift( $params );
			return wfMessage( $key, $params );
		}
		if ( !isset( $this->messages[$key] ) ) {
			$this->messages[$key] = new Message( $key );
		}
		return $this->messages[$key];
	}

	/**
	 * @param AbstractRevision $revision
	 * @return string
	 */
	protected function decideContentFormat( AbstractRevision $revision ) {
		if ( $revision instanceof PostRevision && $revision->isTopicTitle() ) {
			return 'plaintext';
		}
		$alpha = $revision->getRevisionId()->getAlphadecimal();
		if ( isset( $this->revisionContentFormat[$alpha] ) ) {
			return $this->revisionContentFormat[$alpha];
		}

		return $this->contentFormat;
	}

}
