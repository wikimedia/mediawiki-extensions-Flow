<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Data\ObjectManager;
use Flow\Exception\DataModelException;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
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
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Formatting for wrong user' );
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
			'isModerated' => $row->revision->isModerated(),
			'links' => $this->buildActionLinks(
				$row->workflow->getArticleTitle(),
				$row->revision->getChangeType(),
				$row->workflow->getId(),
				$row->revision->getRevisionId(),
				method_exists( $row->revision, 'getPostId' ) ? $row->revision->getPostId() : null
			),
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
	 * @param Title $title
	 * @param string $action
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @param UUID|null $postId
	 * @return array|false
	 */
	public function buildActionLinks( Title $title, $action, UUID $workflowId, UUID $revId, UUID $postId = null ) {
		$linkTypes = $this->permissions->getActions()->getValue( $action, 'links' );
		if ( $linkTypes === null ) {
			// invalid action
			return null;
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
					wfDebugLog( __CLASS__, __FUNCTION__ . ': No postId available to render post link' );
					break;
				}
				$links['post'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'view',
						array(
							'workflow' => $workflowId->getAlphadecimal(),
							'topic_postId' => $postId->getAlphadecimal(),
						)
					) . '#post-' . $postId->getAlphadecimal(),
					wfMessage( 'flow-link-post' )
				);
				break;

			case 'post-history':
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
				if ( !$revId ) {
					wfDebugLog( __CLASS__, __FUNCTION__ . ': No revId available to render diff link' );
					break;
				}
				$links['diff'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'compare-header-revisions',
						array(
							'workflow' => $workflowId->getAlphadecimal(),
							'header_newRevision' => $revId->getAlphadecimal(),
						)
					),
					wfMessage( 'diff' )
				);
				break;

			case 'diff-post':
				if ( !$revId ) {
					wfDebugLog( __CLASS__, __FUNCTION__ . ': No revId available to render diff link' );
					break;
				}
				$links['diff'] = array(
					$this->urlGenerator->buildUrl(
						$title,
						'compare-post-revisions',
						array(
							'workflow' => $workflowId->getAlphadecimal(),
							'topic_newRevision' => $revId->getAlphadecimal(),
						)
					),
					wfMessage( 'diff' )
				);
				break;

			case 'workflow':
				/** @var Title $linkTitle */
				list( $linkTitle, $query ) = $this->urlGenerator->buildUrlData(
					$title,
					'view'
				);
				$links['workflow'] = array(
					$linkTitle->getFullUrl( $query ),
					$linkTitle->getPrefixedText() // @todo this isn't wfMessage object
				);
				break;

			default:
				wfDebugLog( __CLASS__, __FUNCTION__ . ': unkown action link type: ' . $type );
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
				// wfDebugLog( __CLASS__, __FUNCTION__ . ': Something something' );
				return '';
			}
			if ( !$this->permissions->isAllowed( $previousRevision, 'view' ) ) {
				// @todo message about being unavailable?
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
			try {
				$content = $this->templating->getContent( $revision->getRootPost(), 'wikitext' );
			} catch ( DataModelException $e ) {
				/** @var ObjectManager $storage */
				$storage = Container::get( 'storage.post' );
				/** @var PostRevision[] $found */
				$found = $storage->find(
					array( 'tree_rev_descendant_id' => $workflowId ),
					array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
				);
				if ( !$found ) {
					wfWarn( __METHOD__ . ': No tree_rev_descendant_id matching ' . $workflowId->getAlphadecimal() );
					return '';
				}
				$content = $this->templating->getContent( reset( $found ), 'wikitext' );
			}
			return Message::rawParam( htmlspecialchars( $content ) );

		case 'bundle-count':
			return Message::numParam( count( $revision ) );

		default:
			wfWarn( __METHOD__ . ': Unknown formatter parameter: ' . $param );
			return '';
		}
	}
}
