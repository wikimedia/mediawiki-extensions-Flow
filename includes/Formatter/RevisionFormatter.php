<?php

namespace Flow\Formatter;

use Flow\Collection\HeaderCollection;
use Flow\Collection\PostCollection;
use Flow\Container;
use Flow\Data\ObjectManager;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use Flow\UrlGenerator;
use IContextSource;
use Message;
use Title;

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
	 * @param RevisionActionPermissions $permissions
	 * @param Templating $templating
	 */
	public function __construct( RevisionActionPermissions $permissions, Templating $templating ) {
		$this->permissions = $permissions;
		$this->templating = $templating;
		$this->urlGenerator = $this->templating->getUrlGenerator();
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

		$this->urlGenerator->withWorkflow( $row->workflow );

		$res = array(
			'workflowId' => $row->workflow->getId(),
			'revisionId' => $row->revision->getRevisionId()->getAlphadecimal(),
			'timestamp' => $row->revision->getRevisionId()->getTimestampObj()->getTimestamp( TS_MW ),
			'changeType' => $row->revision->getChangeType(),
			'content' => $this->templating->getContent( $row->revision ),
			'dateFormats' => $this->getDateFormats( $row->revision, $ctx ),
			'properties' => $this->buildProperties( $row->workflow->getId(), $row->revision, $ctx ),
			'isModerated' => $this->templating->getModeratedRevision( $row->revision )->isModerated(),
			'links' => $this->buildActionLinks( $row ),
			'size' => array(
				'old' => strlen( $row->previousRevision ? $row->previousRevision->getContentRaw() : '' ),
				'new' => strlen( $row->revision->getContentRaw() ),
			),
		);

		return $res;
	}

	/**
	 * @param AbstractRevision $revision
	 * @param IContextSource $ctx
	 * @return array Contains [timeAndDate, date, time]
	 */
	public function getDateFormats( AbstractRevision $revision, IContextSource $ctx ) {
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
	public function buildActionLinks( FormatterRow $row ) {
		$title = $row->workflow->getArticleTitle();
		$action = $row->revision->getChangeType();
		$workflowId = $row->workflow->getId();
		$revId = $row->revision->getRevisionId();
		$postId = method_exists( $row->revision, 'getPostId' ) ? $row->revision->getPostId() : null;

		$linkTypes = $this->permissions->getActions()->getValue( $action, 'links' );
		if ( $linkTypes === null ) {
			throw new FlowException( "No links defined for action: $action" );
		}

		$links = array();
		foreach ( $linkTypes as $type ) {
			switch( $type ) {
			case 'topic':
				$links['topic'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'view',
						array( 'workflow' => $workflowId->getAlphadecimal() )
					),
					wfMessage( 'flow-link-topic' )
				);
				break;

			case 'post':
				if ( !$postId ) {
					wfDebugLog( 'Flow', __METHOD__ . ': No postId available to render post link' );
					break;
				}
				$links['post'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'view',
						array(
							'workflow' => $workflowId->getAlphadecimal(),
						)
					) . '#post-' . $postId->getAlphadecimal(),
					wfMessage( 'flow-link-post' )
				);
				break;

			case 'header-revision':
				$links['header-revision'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'view',
						array(
							'workflow' => $workflowId->getAlphadecimal(),
							'header_revId' => $revId->getAlphadecimal(),
						)
					),
					wfMessage( 'flow-link-header-revision' )
				);
				break;

			case 'topic-revision':
				if ( !$postId ) {
					wfDebugLog( 'Flow', __METHOD__ . ': No postId available to render revision link' );
					break;
				}

				$links['topic-revision'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'view',
						array(
							'workflow' => $workflowId->getAlphadecimal(),
							'topic_postId' => $postId->getAlphadecimal(),
							'topic_revId' => $revId->getAlphadecimal(),
						)
					),
					wfMessage( 'flow-link-topic-revision' )
				);
				break;

			case 'post-revision':
				if ( !$postId ) {
					wfDebugLog( 'Flow', __METHOD__ . ': No postId available to render revision link' );
					break;
				}

				$links['post-revision'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'view',
						array(
							'workflow' => $workflowId->getAlphadecimal(),
							'topic_postId' => $postId->getAlphadecimal(),
							'topic_revId' => $revId->getAlphadecimal(),
						)
					),
					wfMessage( 'flow-link-post-revision' )
				);
				break;

			case 'post-history':
				if ( !$postId ) {
					wfDebugLog( 'Flow', __METHOD__ . ': No postId available to render history link' );
					break;
				}

				$links['post-history'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'history',
						array(
							'workflow' => $workflowId->getAlphadecimal(),
							'topic_postId' => $postId->getAlphadecimal(),
						)
					),
					wfMessage( 'hist' )
				);
				break;

			case 'topic-history':
				$links['topic-history'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'history',
						array( 'workflow' => $workflowId->getAlphadecimal() )
					),
					wfMessage( 'hist' )
				);
				break;

			case 'board-history':
				$links['board-history'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'history'
					),
					wfMessage( 'hist' )
				);
				break;

			case 'diff-header':
				$blockName = 'header';
				$diffLink = 'header';
			case 'diff-post':
				$blockName = 'topic';
				$diffLink = 'post';
			case 'diff-post-summary':
				$blockName = 'topicsummary';
				$diffLink = 'postsummary';
				/*
				 * To diff against previous revision, we don't really need that
				 * revision id; if no particular diff id is specified, it will
				 * assume a diff against previous revision. However, we do want
				 * to make sure that a previous revision actually exists to diff
				 * against. This could result in a network request (fetching the
				 * current revision), but it's likely being loaded anyways.
				 */
				if ( $row->revision->getPrevRevisionId() !== null ) {
					$links['diff'] = array(
						$this->urlGenerator->buildUrl(
							$title,
							'compare-' . $diffLink . '-revisions',
							array(
								'workflow' => $workflowId->getAlphadecimal(),
								$blockName . '_newRevision' => $revId->getAlphadecimal(),
							)
						),
						wfMessage( 'diff' )
					);

					/*
					 * Different formatters have different terminology for the link
					 * that diffs a certain revision to the previous revision.
					 *
					 * E.g.: Special:Contributions has "diff" ($links['diff']),
					 * ?action=history has "prev" ($links['prev']).
					 */
					$links['diff-prev'] = array( $links['diff'][0], wfMessage( 'last' ) );
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
					$links['diff-cur'] = array(
						$this->urlGenerator->buildUrl(
							$title,
							'compare-' . $diffLink . '-revisions',
							array(
								'workflow' => $workflowId->getAlphadecimal(),
								$blockName . '_newRevision' => $cur->getRevisionId()->getAlphadecimal(),
								$blockName . '_oldRevision' => $revId->getAlphadecimal(),
							)
						),
						wfMessage( 'cur' )
					);
				}
				break;

			case 'workflow':
				/** @var Title $linkTitle */
				list( $linkTitle, $query ) = $this->urlGenerator->buildUrlData(
					$title,
					'view'
				);
				$links['workflow'] = array(
					$linkTitle->getFullUrl( $query ),
					wfMessage( 'flow-link-board', $linkTitle->getPrefixedText() )
				);
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
	 * @param UUID $workflowId
	 * @param AbstractRevision $revision
	 * @param IContextSource $ctx
	 * @return array
	 */
	public function buildProperties( UUID $workflowId, AbstractRevision $revision, IContextSource $ctx ) {
		$changeType = $revision->getChangeType();
		$params = $this->permissions->getActions()->getValue( $changeType, 'history', 'i18n-params' );

		$res = array();
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
			return Message::rawParam( htmlspecialchars( $content ) );

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
			return Message::rawParam( htmlspecialchars( $content ) );

		case 'workflow-url':
			return $this->templating->getUrlGenerator()->generateUrl( $workflowId );

		case 'post-url':
			if ( !$revision instanceof PostRevision ) {
				throw new FlowException( 'Expected PostRevision but received' . get_class( $revision ) );
			}
			return $this->templating->getUrlGenerator()
				->generateUrl(
					$workflowId,
					'view',
					array(),
					'flow-post-' . $revision->getPostId()->getAlphadecimal()
				);

		case 'moderated-reason':
			// don-t parse wikitext in the moderation reason
			return Message::rawParam( htmlspecialchars( $revision->getModeratedReason() ) );

		case 'topic-of-post':
			if ( !$revision instanceof PostRevision ) {
				throw new FlowException( 'Expected PostRevision but received ' . get_class( $revision ) );
			}
			$root = $revision->getRootPost();
			$content = $this->templating->getContent( $root, 'wikitext' );

			if ( !$this->permissions->isAllowed( $root, 'view' ) ) {
				/*
				 * If a user is not allowed to view the content, a message will
				 * be displayed instead (which may contain html - links to the
				 * user). That HTML should not be escaped.
				 */
				return Message::rawParam( $content );
			}

			// normal msg param, will be escaped
			return $content;

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
}
