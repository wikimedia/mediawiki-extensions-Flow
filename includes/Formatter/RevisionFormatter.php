<?php

namespace Flow\Formatter;

use Flow\Anchor;
use Flow\Data\UserNameBatch;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
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

	protected $includeProperties = false;

	protected $allowedContentFormats = array( 'html', 'wikitext' );

	protected $contentFormat = 'html';

	/**
	 * @param RevisionActionPermissions $permissions
	 * @param Templating $templating
	 */
	public function __construct(
		RevisionActionPermissions $permissions,
		Templating $templating,
		UserNameBatch $usernames
	) {
		$this->permissions = $permissions;
		$this->templating = $templating;
		$this->urlGenerator = $this->templating->getUrlGenerator();
		$this->usernames = $usernames;
		$this->genderCache = GenderCache::singleton();
	}

	/**
	 * The self::buildProperties method is fairly expensive and only used for rendering
	 * history entries.  As such it is optimistically disabled unless requested
	 * here
	 */
	public function setIncludeHistoryProperties( $shouldInclude ) {
		$this->includeProperties = (bool)$shouldInclude;
	}

	public function setContentFormat( $format ) {
		if ( false === array_search( $format, $this->allowedContentFormats ) ) {
			throw new FlowException( "Unknown content format: $format" );
		}
		$this->contentFormat = $format;
	}

	/**
	 * @param FormatterRow $row
	 * @param IContextSource $ctx
	 * @return array|false
	 */
	public function formatApi( FormatterRow $row, IContextSource $ctx ) {
		// @todo the only permissions currently checked in this class are prev-revision
		// mostly permissions is used for the actions,  figure out how permissions should
		// fit into this class either used more or not at all.
		if ( $ctx->getUser()->getName() !== $this->permissions->getUser()->getName() ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Formatting for wrong user' );
			return false;
		}

		$section = new \ProfileSection( __METHOD__ );
		$isContentAllowed = $this->permissions->isAllowed( $row->revision, 'view' );
		$isHistoryAllowed = $isContentAllowed ?: $this->permissions->isAllowed( $row->revision, 'history' );

		if ( !$isHistoryAllowed ) {
			return array();
		}

		$this->urlGenerator->withWorkflow( $row->workflow );
		$moderatedRevision = $this->templating->getModeratedRevision( $row->revision );
		$res = array(
			'workflowId' => $row->workflow->getId()->getAlphadecimal(),
			'revisionId' => $row->revision->getRevisionId()->getAlphadecimal(),
			'timestamp' => $row->revision->getRevisionId()->getTimestampObj()->getTimestamp( TS_MW ),
			'changeType' => $row->revision->getChangeType(),
			'dateFormats' => $this->getDateFormats( $row->revision, $ctx ),
			'properties' => $this->buildProperties( $row->workflow->getId(), $row->revision, $ctx ),
			'isModerated' => $moderatedRevision->isModerated(),
			// These are read urls
			'links' => $this->buildLinks( $row ),
			// These are write urls
			'actions' => $this->buildActions( $row ),
			'size' => array(
				'old' => strlen( $row->previousRevision ? $row->previousRevision->getContentRaw() : '' ),
				'new' => strlen( $row->revision->getContentRaw() ),
			),
			'author' => $this->serializeUser(
				$row->revision->getUserWiki(),
				$row->revision->getUserId(),
				$row->revision->getUserIp()
			),
		);

		$prevRevId = $row->revision->getPrevRevisionId();
		$res['previousRevisionId'] = $prevRevId ? $prevRevId->getAlphadecimal() : null;

		if ( $res['isModerated'] ) {
			$res['moderator'] = $this->serializeUser(
				$moderatedRevision->getModeratedByUserWiki(),
				$moderatedRevision->getModeratedByUserId(),
				$moderatedRevision->getModeratedByUserIp()
			);
			$res['moderateState'] = $moderatedRevision->getModerationState();
		}

		if ( $isContentAllowed ) {

			// topic titles are always forced to plain text
			$contentFormat = ( $row->revision instanceof PostRevision && $row->revision->isTopicTitle() )
				? 'wikitext'
				: $this->contentFormat;

			$res += array(
				'content' => $this->templating->getContent( $row->revision, $contentFormat ),
				'contentFormat' => $contentFormat,
				'size' => array(
					'old' => null,
					// @todo this isn't really correct
					'new' => strlen( $row->revision->getContentRaw() ),
				),
			);
			if ( $row->previousRevision
				&& $this->permissions->isAllowed( $row->previousRevision, 'view' )
			) {
				$res['size']['old'] = strlen( $row->previousRevision->getContentRaw() );
			}
		}

		if ( $row instanceof TopicRow &&
			$row->summary &&
			$this->permissions->isAllowed( $row->summary, 'view' )
		) {
			// Maybe always have both parsed and unparsed versions available
			$res['summary'] = $this->templating->getContent( $row->summary, $this->contentFormat );
			$res['summaryRevId'] = $row->summary->getRevisionId()->getAlphadecimal();
		}

		if ( $row->revision instanceof PostRevision ) {
			$replyTo = $row->revision->getReplyToId();
			$res['replyToId'] = $replyTo ? $replyTo->getAlphadecimal() : null;
			$res['postId'] = $row->revision->getPostId()->getAlphadecimal();
		}

		return $res;
	}

	/**
	 * @param array $user Contains `name`, `wiki`, and `gender` keys
	 */
	public function serializeUserLinks( $user ) {
		$links = array(
			"contribs" => array(
				'url' => '',
				'title' => '',
			),
			"talk" => array(
				'url' => '',
				'title' => '',
			),
		);
		// is this right permissions? typically this would
		// be sourced from Linker::userToolLinks, but that
		// only undertands html strings.
		if ( $this->permissions->getUser()->isAllowed( 'block' ) ) {
			// only is the user has blocking rights
			$links += array(
				"block" => array(
					'url' => '',
					'link' => '',
				),
			);
		}

		return $links;
	}

	public function serializeUser( $userWiki, $userId, $userIp ) {
		$section = new \ProfileSection( __METHOD__ );
		$res = array(
			'name' => $this->usernames->get( $userWiki, $userId, $userIp ),
			'wiki' => $userWiki,
			'gender' => 'unknown',
			'links' => array(),
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

		$section = new \ProfileSection( __METHOD__ );
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
	 */
	public function buildActions( FormatterRow $row ) {
		$section = new \ProfileSection( __METHOD__ );
		$workflow = $row->workflow;
		$revision = $row->revision;
		$title = $workflow->getArticleTitle();
		$action = $revision->getChangeType();
		$workflowId = $workflow->getId();
		$revId = $revision->getRevisionId();
		$postId = method_exists( $revision, 'getPostId' ) ? $revision->getPostId() : null;
		$actionTypes = $this->permissions->getActions()->getValue( $action, 'actions' );
		if ( $actionTypes === null ) {
			throw new FlowException( "No actions defined for action: $action" );
		}

		// actions primarily vary by revision type...
		$links = array();
		foreach ( $actionTypes as $type ) {
			if ( !$this->permissions->isAllowed( $revision, $type ) ) {
				continue;
			}
			switch( $type ) {
			case 'reply':
				if ( !$postId ) {
					throw new FlowException( "$type called without \$postId" );
				}
				$links['reply'] = $this->urlGenerator->replyAction( $title, $workflowId, $postId );
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

			case 'lock':
				// @todo
				break;

			case 'restore-post':
				// @todo
				break;

			case 'hide-topic':
				$links['hide'] = $this->urlGenerator->hideTopicAction( $title, $workflowId );
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

			case 'close-topic':
				// close topic link is only available to topic workflow
				if( !in_array( $workflow->getType(), array( 'topic', 'topicsummary' ) ) ) {
					continue;
				}
				$links['close'] = $this->urlGenerator->closeTopicAction( $title, $workflowId );
				break;

			case 'restore-topic':
				switch ( $revision->getModerationState() ) {
					case 'close':
						$moderateAction = 'reopen';
						$flowAction = 'close-open-topic';
					break;
					case 'delete':
					case 'hide':
					case 'suppress':
						$moderateAction = 'un' . $revision->getModerationState();
						$flowAction = 'moderate-topic';
					break;
				}
				if ( $moderateAction ) {
					$links[$moderateAction] = $this->urlGenerator->restoreTopicAction( $title, $workflowId, $moderateAction, $flowAction );
				}
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
		$section = new \ProfileSection( __METHOD__ );
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

			case 'diff-header':
				$diffCallback = isset( $diffCallback ) ? $diffCallback : array( $this->urlGenerator, 'diffHeaderLink' );
				// don't break, diff links are rendered below
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

			case 'diff-post-summary':
				/*
				 * To diff against previous revision, we don't really need that
				 * revision id; if no particular diff id is specified, it will
				 * assume a diff against previous revision. However, we do want
				 * to make sure that a previous revision actually exists to diff
				 * against. This could result in a network request (fetching the
				 * current revision), but it's likely being loaded anyways.
				 */
				if ( $revision->getPrevRevisionId() !== null ) {
					$links['diff'] = $this->urlGenerator
						->diffSummaryLink( $title, $workflowId, $revId );

					/*
					 * Different formatters have different terminology for the link
					 * that diffs a certain revision to the previous revision.
					 *
					 * E.g.: Special:Contributions has "diff" ($links['diff']),
					 * ?action=history has "prev" ($links['prev']).
					 */
					$links['diff-prev'] = clone $links['diff'];
					$links['diff-prev']->title = $this->msg( 'last' );
				}

				/*
				 * To diff against the current revision, we need to know the id
				 * of this last revision. This could be an additional network
				 * request, though anything using formatter likely already needs
				 * to request the most current revision (e.g. to check
				 * permissions) so we should be able to get it from local cache.
				 */
				$cur = $row->currentRevision;
				if ( !$revision->getRevisionId()->equals( $cur->getRevisionId() ) ) {
					$links['diff-cur'] = $this->urlGenerator
						->diffSummaryLink( $title, $workflowId, $revId, $cur->getRevisionId() );
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
	 * @return array
	 */
	public function buildProperties( UUID $workflowId, AbstractRevision $revision, IContextSource $ctx ) {
		if ( $this->includeProperties === false ) {
			return array();
		}

		$section = new \ProfileSection( __METHOD__ );
		$changeType = $revision->getChangeType();
		$actions = $this->permissions->getActions();
		$params = $actions->getValue( $changeType, 'history', 'i18n-params' );
		if ( !$params ) {
			// should we have a sigil for i18n with no parameters?
			wfDebugLog( 'Flow', __METHOD__ . ": No i18n params for changeTyp4 $changeType on " . $revision->getRevisionId()->getAlphadecimal() );
			return array();
		}

		$res = array( '_key' => $actions->getValue( $changeType, 'history', 'i18n-message' ) );
		foreach ( $params as $param ) {
			$res[$param] = $this->processParam( $param, $revision, $workflowId, $ctx );
		}

		return $res;
	}

	/**
	 * Mimic Echo parameter formatting
	 *
	 * @param string $param The requested i18n parameter
	 * @param AbstractRevision|array $revision The revision to format or an array of revisions
	 * @param UUID $workflowId The UUID of the workflow $revision belongs tow
	 * @param IContextSource $ctx
	 * @return mixed A valid parameter for a core Message instance
	 * @throws FlowException
	 */
	protected function processParam( $param, /* AbstractRevision|array */ $revision, UUID $workflowId, IContextSource $ctx ) {
		switch ( $param ) {
		case 'creator-text':
			if ( $revision instanceof PostRevision ) {
				return $this->templating->getCreatorText( $revision );
			} else {
				return '';
			}

		case 'user-text':
			return $this->templating->getUserText( $revision );

		case 'user-links':
			return Message::rawParam( $this->templating->getUserLinks( $revision ) );

		case 'summary':
			/*
			 * Fetch in HTML; unparsed wikitext in summary is pointless.
			 * Larger-scale wikis will likely also store content in html, so no
			 * Parsoid roundtrip is needed then (and if it *is*, it'll already
			 * be needed to render Flow discussions, so this is manageable)
			 */
			$lang = $ctx->getLanguage();
			$content = $this->templating->getContent( $revision, 'html' );
			$content = strip_tags( $content );
			return Message::rawParam( htmlspecialchars( $lang->truncate( trim( $content ), 140 ) ) );

		case 'wikitext':
			$content = $this->templating->getContent( $revision, 'wikitext' );
			return Message::rawParam( $content );

		// This is potentially two networked round trips, much too expensive for
		// the rendering loop
		case 'prev-wikitext':
			if ( $revision->isFirstRevision() ) {
				return '';
			}
			$previousRevision = $revision->getCollection()->getPrevRevision( $revision );
			if ( !$previousRevision ) {
				return '';
			}
			if ( !$this->permissions->isAllowed( $previousRevision, 'view' ) ) {
				return '';
			}

			$content = $this->templating->getContent( $previousRevision, 'wikitext' );
			return Message::rawParam( $content );

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
			return Message::rawParam( htmlspecialchars( $revision->getModeratedReason() ) );

		case 'topic-of-post':
			if ( !$revision instanceof PostRevision ) {
				throw new FlowException( 'Expected PostRevision but received ' . get_class( $revision ) );
			}
			$root = $revision->getRootPost();
			$content = $this->templating->getContent( $root, 'wikitext' );
			return Message::rawParam( $content );

		case 'post-of-summary':
			if ( !$revision instanceof PostSummary ) {
				throw new FlowException( 'Expected PostSummary but received ' . get_class( $revision ) );
			}
			$post = $revision->getCollection()->getPost()->getLastRevision();
			if ( $post->isTopicTitle() ) {
				$content = $this->templating->getContent( $post, 'wikitext' );
				if ( $this->permissions->isAllowed( $post, 'view' ) ) {
					$content = htmlspecialchars( $content );
				}
			} else {
				$content = $this->templating->getContent( $post, 'html' );
			}
			return Message::rawParam( $content );
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
			$this->messages[$key] = new \Message( $key );
		}
		return $this->messages[$key];
	}
}
