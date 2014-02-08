<?php

// Make sure that the order is always correct
if ( $oldRevision->getRevisionId()->getTimestamp() > $newRevision->getRevisionId()->getTimestamp() ) {
	$temp = $oldRevision;
	$oldRevision = $newRevision;
	$newRevision = $temp;
}

$oldContent = $this->getContent( $oldRevision, 'wikitext' );
$newContent = $this->getContent( $newRevision, 'wikitext' );

$differenceEngine = new DifferenceEngine();
$templating = $this;

$differenceEngine->setContent(
	new TextContent( $oldContent ),
	new TextContent( $newContent )
);

$differenceEngine->showDiffStyle();

$getRevisionHeader = function( $revision ) use ( $templating, $block, $user ) {
	$timestamp = $templating->render(
		'flow:timestamp.html.php',
		array(
			'timestamp' => $revision->getRevisionId()->getTimestampObj(),
			'tag' => 'span',
		),
		true /* return */
	);
	$message = wfMessage( 'flow-compare-revisions-revision-header' )
		->rawParams( $timestamp )
		->params( $templating->getUserText( $revision, $user ) );

	$permalinkUrl = $templating->getUrlGenerator()
		->generateBlockUrl(
			$block->getWorkflow(),
			$revision,
			true
		);

	$link = Html::rawElement( 'a',
		array(
			'class' => 'flow-diff-revision-link',
			'href' => $permalinkUrl,
		),
		$message->parse()
	);

	return $link;
};

$headerMsg = null;

switch( $newRevision->getRevisionType() ) {
	case 'post':
		$postFragment = '#flow-post-' . $newRevision->getPostId()->getAlphadecimal();
		$boardLinkTitle = clone $block->getWorkflow()->getArticleTitle();
		$boardLinkTitle->setFragment( $postFragment );
		$boardLink = $templating->getUrlGenerator()
			->buildUrl(
				$boardLinkTitle,
				'view'
			);
		list( $topicLinkTitle, $topicLinkQuery ) = $templating->getUrlGenerator()
			->generateUrlData(
				$block->getWorkflow(),
				'view',
				array(
					$block->getName().'_postId' => $newRevision->getPostId()->getAlphadecimal()
				)
			);

		$topicLinkTitle = clone $topicLinkTitle;
		$topicLinkTitle->setFragment( $postFragment );

		$historyLink = $templating->getUrlGenerator()
			->generateUrl(
				$block->getWorkflow(),
				'post-history',
				array(
					$block->getName().'_postId' => $newRevision->getPostId()->getAlphadecimal()
				)
			);
		$headerMsg = wfMessage( 'flow-compare-revisions-header-post' )
			->params(
				$block->getWorkflow()->getArticleTitle(),
				$this->getContent( $block->loadTopicTitle(), 'wikitext' ),
				$this->usernames->get( wfWikiId(), $newRevision->getCreatorId() ),
				$boardLink,
				$topicLinkTitle->getFullUrl( $topicLinkQuery ),
				$historyLink
			);
		break;
	case 'header':
		$boardLinkTitle = $block->getWorkflow()->getArticleTitle();
		$boardLink = $templating->getUrlGenerator()
			->buildUrl(
				$boardLinkTitle,
				'view'
			);
		$historyLink = $templating->getUrlGenerator()
			->generateUrl(
				$block->getWorkflow(),
				'board-history'
			);
		$headerMsg = wfMessage( 'flow-compare-revisions-header-header' )
			->params(
				$block->getWorkflow()->getArticleTitle(),
				$this->usernames->get( wfWikiId(), $newRevision->getUserId() ),
				$boardLink,
				$historyLink
			);
		break;
	default:
		throw new \Flow\Exception\InvalidDataException( "Unsupported revision type ".$newRevision->getRevisionType(), 'fail-load-data' );
}

if ( $headerMsg ) {
	echo Html::rawElement(
		'div',
		array(
			'class' => 'flow-compare-revisions-header plainlinks',
		),
		$headerMsg->parse()
	);
}

?>
<div class="flow-compare-revisions">
<?php echo $differenceEngine->getDiff( $getRevisionHeader( $oldRevision ), $getRevisionHeader( $newRevision ) ); ?>
</div>
