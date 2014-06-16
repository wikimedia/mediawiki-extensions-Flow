<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Templating;
use Flow\UrlGenerator;
use IContextSource;

class RevisionViewFormatter {

	/**
	 * @param UrlGenerator
	 * @param RevisionFormatter
	 * @param Templating
	 */
	public function __construct( UrlGenerator $urlGenerator, RevisionFormatter $serializer, Templating $templating ) {
		$this->urlGenerator = $urlGenerator;
		$this->serializer = $serializer;
		$this->templating = $templating;
	}

	/**
	 * @param FormatterRow $row
	 * @param IContextSource $ctx
	 * @return array
	 */
	public function formatApi( FormatterRow $row, IContextSource $ctx ) {
		$res = $this->serializer->formatApi( $row, $ctx );
		$res['rev_view_links'] = $this->buildLinks( $row );
		$res['human_timestamp'] = $this->getHumanTimestamp( $res['timestamp'] );
		if ( $row->revision instanceof PostRevision ) {
			if ( $row->revision->isTopicTitle() ) {
				$res['isTopicTitle'] = true;
			} else {
				$res['isTopicTitle'] = false;
			}
			$root = $row->revision->getRootPost();
			$res['root']['content'] = $this->templating->getContent( $root, 'wikitext' );
		}
		if ( $row->revision instanceof PostSummary ) {
			$root = $row->revision->getCollection()->getPost()->getLastRevision();
			$res['root']['content'] = $this->templating->getContent( $root, 'wikitext' );
		}
		return $res;
	}

	/**
	 * Generate the links for single and diff vie actions
	 * @param FormatterRow
	 * @return array
	 */
	public function buildLinks( RevisionViewRow $row ) {
		if ( $row->revision->getPrevRevisionId() !== null ) {
			$links['diff'] = array(
				'url' => $this->urlGenerator->generateUrl(
					$row->workflow->getArticleTitle(),
					$row->diffAction,
					array(
						'workflow' => $row->workflow->getId()->getAlphadecimal(),
						$row->blockName . '_newRevision' => $row->revision->getRevisionId()->getAlphadecimal(),
					)
				),
				'title' => wfMessage( 'diff' )
			);
		} else {
			$links['diff'] = array(
				'url' => '',
				'title' => ''
			);
		}
		$links['hist'] = array(
			'url' => $this->urlGenerator->generateUrl(
				$row->workflow,
				'history',
				array(
					'workflow' => $row->workflow->getId()->getAlphadecimal()
				)
			),
			'title' => wfMessage( 'hist' )
		);
		$links['board'] = array(
			'url' => $this->urlGenerator->generateUrl(
				$row->workflow,
				'view'
			),
			'title' => $row->workflow->getArticleTitle()
		);
		if ( $row->revision instanceof PostRevision || $row->revision instanceof PostSummary ) {
			$links['root'] = array(
				'url' => $this->urlGenerator->generateUrl(
					$row->workflow,
					'view',
					array(
						'workflow' => $row->workflow->getId()->getAlphadecimal()
					)
				),
				'title' => $row->workflow->getArticleTitle()
			);
		}
		$links['single-view'] = array(
				'url' => $this->urlGenerator->generateUrl(
					$row->workflow,
					'single-view',
					array(
						'topic[postId]' => $row->revision->getCollectionId()->getAlphadecimal(),
						'topic[revId]' => $row->revision->getRevisionId()->getAlphadecimal()
					)
				),
				'title' => $row->workflow->getArticleTitle()
			);

		return $links;
	}

	public function getHumanTimestamp( $timestamp ) {
		$ts = new \MWTimestamp( $timestamp );
		return $ts->getHumanTimestamp();
	}

}

class RevisionDiffViewFormatter {

	protected $revisionViewFormatter;

	public function __construct( RevisionViewFormatter $revisionViewFormatter ) {
		$this->revisionViewFormatter = $revisionViewFormatter;
	}

	/**
	 * Diff would format against two revisions
	 */
	public function formatApi( FormatterRow $newRow, FormatterRow $oldRow, IContextSource $ctx ) {
		$oldRes = $this->revisionViewFormatter->formatApi( $oldRow, $ctx );
		$newRes = $this->revisionViewFormatter->formatApi( $newRow, $ctx );

		$oldContent = $oldRow->revision->getContent( 'wikitext' );
		$newContent = $newRow->revision->getContent( 'wikitext' );

		$differenceEngine = new \DifferenceEngine();

		$differenceEngine->setContent(
			new \TextContent( $oldContent ),
			new \TextContent( $newContent )
		);

		return array( 'new' => $newRes, 'old' => $oldRes, 'diff_content' => $differenceEngine->getDiffBody() );
	}
}
