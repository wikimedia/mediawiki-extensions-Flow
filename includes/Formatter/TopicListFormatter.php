<?php

namespace Flow\Formatter;

use Flow\Anchor;
use Flow\Data\PagerPage;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Templating;
use Flow\UrlGenerator;
use IContextSource;

class TopicListFormatter {

	public function __construct( UrlGenerator $urlGenerator, RevisionFormatter $serializer, Templating $templating ) {
		$this->urlGenerator = $urlGenerator;
		$this->serializer = $serializer;
		$this->templating = $templating;
	}

	public function buildEmptyResult( Workflow $workflow ) {
		return array(
			'type' => 'topiclist',
			'roots' => array(),
			'posts' => array(),
			'revisions' => array(),
			'links' => array( 'pagination' => array() ),
			'actions' => $this->buildApiActions( $workflow ),
			'sortby' => 'newest',
			'submitted' => array(),
		);
	}

	public function formatApi(
		Workflow $listWorkflow,
		array $workflows,
		array $found,
		PagerPage $page,
		IContextSource $ctx
	) {
		$section = new \ProfileSection( __METHOD__ );
		$res = $this->buildResult( $listWorkflow, $workflows, $found, $ctx ) +
			$this->buildEmptyResult( $listWorkflow );
		$pagingOption = $page->getPagingLinksOptions();
		foreach ( $pagingOption as $k => $v ) {
			$pagingOption[$k] = array (
				'offset-id' => $v['offset'],
				'offset-dir' => $v['direction'],
				'limit' => $v['limit']
			);
			if ( isset( $v['sortby'] ) ) {
				$pagingOption[$k]['sortby'] = $v['sortby'];
			}
		}
		$res['links']['pagination'] = $this->buildPaginationLinks(
			$listWorkflow,
			$pagingOption
		);
		$title = $listWorkflow->getArticleTitle();
		$res['links']['board-sort']['updated'] = $this->urlGenerator->boardLink( $title, 'updated' )->getLocalURL();
		$res['links']['board-sort']['newest']  = $this->urlGenerator->boardLink( $title, 'newest' )->getLocalURL();

		return $res;
	}

	protected function buildPaginationLinks( Workflow $workflow, array $links ) {
		$res = array();
		$title = $workflow->getArticleTitle();
		foreach ( $links as $key => $options ) {
			// prefix all options with topiclist_
			$realOptions = array();
			foreach ( $options as $k => $v ) {
				$realOptions["topiclist_$k"] = $v;
			}
			$res[$key] = new Anchor(
				$key, // @todo i18n
				$title,
				$realOptions
			);
		}

		return $res;
	}

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
			if ( $formatterRow->summary ) {
				$revisions[$serialized['revisionId']]['summary'] = $this->templating->getContent( $formatterRow->summary );
			}
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
				$list[] = $alpha = $workflow->getId()->getAlphadecimal();
				$workflows[$alpha] = $workflow;
			}

			foreach ( $list as $alpha ) {
				// Metadata that requires everything to be serialied first
				$metadata = $this->generateTopicMetadata( $posts, $revisions, $workflows, $alpha );
				foreach ( $posts[$alpha] as $revId ) {
					$revisions[$revId] += $metadata;
				}
			}
		}

		return array(
			'workflowId' => $listWorkflow->getId()->getAlphadecimal(),
			'roots' => $list,
			'posts' => $posts,
			'revisions' => $revisions,
			'links' => array(
				'search' => array(
					'url' => '',
					'title' => '',
				),
				'pagination' => array(
					'load_more' => array(
						'url' => '',
						'title' => '',
					),
				),
			),
		);
	}

	protected function buildApiActions( Workflow $workflow ) {
		return array(
			'newtopic' => $this->urlGenerator->newTopicAction( $workflow->getArticleTitle() ),
		);
	}

	protected function generateTopicMetadata( array $posts, array $revisions, array $workflows, $postAlphaId ) {
		$replies = -1;
		$authors = array();
		$stack = new \SplStack;
		$stack->push( $revisions[$posts[$postAlphaId][0]] );
		do {
			$data = $stack->pop();
			$replies++;
			$authors[] = $data['author']['wiki'] . "\t" . $data['author']['name'];
			foreach ( $data['replies'] as $postId ) {
				$stack->push( $revisions[$posts[$postId][0]] );
			}
		} while( !$stack->isEmpty() );

		$workflow = isset( $workflows[$postAlphaId] ) ? $workflows[$postAlphaId] : null;

		return array(
			'reply_count' => $replies,
			'author_count' => count( array_unique( $authors ) ),
			// ms timestamp
			'last_updated' => $workflow ? $workflow->getLastModifiedObj()->getTimestamp() * 1000 : null,
		);
	}
}
