<?php

// Make sure that the order is always correct
if ( $oldRevision->getRevisionId()->getTimestamp() > $newRevision->getRevisionId()->getTimestamp() ) {
	$temp = $oldRevision;
	$oldRevision = $newRevision;
	$newRevision = $temp;
}

$oldContent = $this->getContent( $oldRevision, 'wikitext', $user );
$newContent = $this->getContent( $newRevision, 'wikitext', $user );

$differenceEngine = new DifferenceEngine();
$templating = $this;

$differenceEngine->setContent(
	new TextContent( $oldContent ),
	new TextContent( $newContent )
);

$differenceEngine->showDiffStyle();

$getRevisionHeader = function( $revision ) use ( $templating, $block ) {
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
		$postFragment = '#flow-post-' . $newRevision->getPostId()->getHex();
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
					$block->getName().'[postId]' => $newRevision->getPostId()->getHex()
				)
			);

		$topicLinkTitle = clone $topicLinkTitle;
		$topicLinkTitle->setFragment( $postFragment );

		$historyLink = $templating->getUrlGenerator()
			->generateUrl(
				$block->getWorkflow(),
				'post-history',
				array(
					$block->getName().'[postId]' => $newRevision->getPostId()->getHex()
				)
			);
		$headerMsg = wfMessage( 'flow-compare-revisions-header-post' )
			->params(
				$block->getWorkflow()->getArticleTitle(),
				$this->getContent( $block->loadTopicTitle(), 'wikitext', $user ),
				$this->usernames->get( wfWikiId(), $newRevision->getCreatorId() ),
				$boardLink,
				$topicLinkTitle->getFullUrl( $topicLinkQuery ),
				$historyLink
			);
		break;
	case 'header':
		// @todo later
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
