<?php

$type = $revision->getRevisionType();
if ( $type == 'post' && $revision->isTopicTitle() ) {
	$type = 'title';
}

$timestamp = $revision->getRevisionId()->getTimestampObj();
$formattedTimestamp = $this->render( 'flow:timestamp.html.php', array(
	'timestamp' => $timestamp,
	'tag' => 'span',
), true );

$urlGenerator = $this->getUrlGenerator();

if ( $revision->getPrevRevisionId() ) {
	$params = array(
		$block->getName().'[newRevision]' => $revision->getRevisionId()->getHex(),
		$block->getName().'[oldRevision]' => $revision->getPrevRevisionId()->getHex()
	);
	if ( $revision->getRevisionType() === 'header' ) {
		$params['definition'] = 'header-view';
	}
	$compareLink = $urlGenerator->generateUrl(
		$block->getWorkflow(),
		'compare-revisions',
		$params
	);
} else {
	$compareLink = false;
}

switch( $revision->getRevisionType() ) {
	case 'post':
		$historyLink = $urlGenerator->generateUrl(
			$block->getWorkflow(),
			'post-history',
			array(
				$block->getName().'[postId]' => $revision->getPostId()->getHex(),
			)
		);

		$msgKey = $compareLink ? 'flow-revision-permalink-warning-post' : 'flow-revision-permalink-warning-post-first';
		$message = wfMessage( $msgKey )
			->rawParams( $formattedTimestamp )
			->params(
				$block->getWorkflow()->getArticleTitle(),
				$this->getContent( $block->loadTopicTitle(), 'wikitext' ),
				$historyLink
			);

		if ( $compareLink ) {
			$message->params( $compareLink );
		}
		break;
	case 'header':
		$historyLink = $urlGenerator->generateUrl(
			$block->getWorkflow(),
			'view',
			array(
				'definition' => 'board-history'
			)
		);

		$msgKey = $compareLink ? 'flow-revision-permalink-warning-header' : 'flow-revision-permalink-warning-header-first';
		$message = wfMessage( $msgKey )
			->rawParams( $formattedTimestamp )
			->params(
				$historyLink
			);

		if ( $compareLink ) {
			$message->params( $compareLink );
		}
		break;
	default:
		throw new \Flow\Exception\InvalidDataException( "Unknown revision type: " . $revision->getRevisionType(), 'fail-load-data' );
}

echo Html::rawElement(
	'div',
	array(
		'class' => 'flow-revision-permalink-warning plainlinks',
	),
	$message->parse()
);
