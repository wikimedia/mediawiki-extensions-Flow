<?php

$titleText = $this->getContent( $topicTitle, 'wikitext' );
$topicLink = $this->urlGenerator
	->topicLink( $topic->getArticleTitle(), $topic->getId() )
	->getFullUrl();

$creator = $post->getCreatorIp();
if ( $creator === null ) {
	$creator = $this->usernames->get( wfWikiId(), $post->getCreatorId() );
}
$title = wfMessage( 'flow-post-history', $titleText, $creator )->escaped();
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );

$timespans = $historyRenderer->getTimespans( $history );

?>
<div class="flow-history-container">
	<p class="flow-history-pages">
		<span class="plainlinks">
			<?php echo wfMessage( 'flow-history-pages-post', $topicLink, \Message::rawParam( htmlspecialchars( $titleText ) ) )->parse(); ?>
		</span>
	</p>

	<div class="flow-history-log">
		<?php
		foreach ( $timespans as $text => $timespan ) {
			$timespan = $history->getTimespan( $timespan['from'], $timespan['to'] );
			if ( $timespan->numRows() ) {
				echo "<h2>$text</h2>";
				echo $historyRenderer->render( $timespan );
			}
		}
		?>
	</div>
</div>
