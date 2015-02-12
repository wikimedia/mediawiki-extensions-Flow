<?php

namespace Flow\Formatter;

use Flow\Data\Pager\PagerPage;
use Flow\Model\Workflow;
use Flow\UrlGenerator;
use IContextSource;

class TopicListFormatter extends BaseTopicListFormatter {
	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var RevisionFormatter
	 */
	protected $serializer;

	public function __construct( UrlGenerator $urlGenerator, RevisionFormatter $serializer ) {
		$this->urlGenerator = $urlGenerator;
		$this->serializer = $serializer;
	}

	public function buildEmptyResult( Workflow $workflow ) {
		$title = $workflow->getArticleTitle();
		return array(
			'title' => $title->getPrefixedText(),
			'actions' => $this->buildApiActions( $workflow ),
		) + parent::buildEmptyResult( $workflow );
	}

	public function formatApi(
		Workflow $listWorkflow,
		array $workflows,
		array $found,
		PagerPage $page,
		IContextSource $ctx
	) {
		$res = $this->buildResult( $listWorkflow, $workflows, $found, $ctx ) +
			$this->buildEmptyResult( $listWorkflow );
		$pagingOption = $page->getPagingLinksOptions();
		$res['links']['pagination'] = $this->buildPaginationLinks(
			$listWorkflow,
			$pagingOption
		);
		$title = $listWorkflow->getArticleTitle();
		$saveSortBy = true;
		$res['links']['board-sort']['updated'] = $this->urlGenerator->boardLink( $title, 'updated', $saveSortBy )->getLinkURL();
		$res['links']['board-sort']['newest'] = $this->urlGenerator->boardLink( $title, 'newest', $saveSortBy )->getLinkURL();

		// Link to designated new-topic page, for no-JS users
		$res['links']['newtopic'] = $this->urlGenerator->newTopicAction( $title, $listWorkflow->getId() )->getLinkURL();

		return $res;
	}

	/**
	 * @param Workflow $listWorkflow
	 * @param Workflow[] $workflows
	 * @param FormatterRow[] $found
	 * @param IContextSource $ctx
	 * @return array
	 */
	protected function buildResult( Workflow $listWorkflow, array $workflows, array $found, IContextSource $ctx ) {
		$revisions = $posts = $replies = array();
		foreach( $found as $formatterRow ) {
			$serialized = $this->serializer->formatApi( $formatterRow, $ctx );
			if ( !$serialized ) {
				continue;
			}
			$revisions[$serialized['revisionId']] = $serialized;
			$posts[$serialized['postId']][] = $serialized['revisionId'];
			$replies[$serialized['replyToId']][] = $serialized['postId'];
		}

		foreach ( $revisions as $i => $serialized ) {
			$alpha = $serialized['postId'];
			$revisions[$i]['replies'] = isset( $replies[$alpha] ) ? $replies[$alpha] : array();
		}

		$list = array();
		if ( $workflows ) {
			$orig = $workflows;
			$workflows = array();
			foreach ( $orig as $workflow ) {
				$alpha = $workflow->getId()->getAlphadecimal();
				if ( isset( $posts[$alpha] ) ) {
					$list[] = $alpha;
					$workflows[$alpha] = $workflow;
				} else {
					wfDebugLog( 'Flow', __METHOD__ . ": No matching root post for workflow $alpha" );
				}
			}

			foreach ( $list as $alpha ) {
				// Metadata that requires everything to be serialied first
				$metadata = $this->generateTopicMetadata( $posts, $revisions, $workflows, $alpha, $ctx );
				foreach ( $posts[$alpha] as $revId ) {
					$revisions[$revId] += $metadata;
				}
			}
		}

		return array(
			'workflowId' => $listWorkflow->getId()->getAlphadecimal(),
			// array_values must be used to ensure 0-indexed array
			'roots' => $list,
			'posts' => $posts,
			'revisions' => $revisions,
		);
	}

	protected function buildApiActions( Workflow $workflow ) {
		return array(
			'newtopic' => $this->urlGenerator->newTopicAction( $workflow->getArticleTitle() ),
		);
	}

	protected function generateTopicMetadata( array $posts, array $revisions, array $workflows, $postAlphaId, IContextSource $ctx ) {
		$language = $ctx->getLanguage();
		$user = $ctx->getUser();

		$replies = -1;
		$authors = array();
		$stack = new \SplStack;
		$stack->push( $revisions[$posts[$postAlphaId][0]] );
		do {
			$data = $stack->pop();
			$replies++;
			$authors[] = $data['creator']['name'];
			foreach ( $data['replies'] as $postId ) {
				$stack->push( $revisions[$posts[$postId][0]] );
			}
		} while( !$stack->isEmpty() );

		/** @var Workflow|null $workflow */
		$workflow = isset( $workflows[$postAlphaId] ) ? $workflows[$postAlphaId] : null;
		$ts = $workflow ? $workflow->getLastModifiedObj()->getTimestamp() : 0;
		return array(
			'reply_count' => $replies,
			'last_updated_readable' => $language->userTimeAndDate( $ts, $user ),
			// ms timestamp
			'last_updated' => $ts * 1000,
		);
	}
}
