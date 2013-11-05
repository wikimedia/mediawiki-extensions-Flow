<?php

$topicTitle = $root->getContent( $user, 'wikitext' );
$topicLink = $this->generateUrl( $topic );

$creator = $post->getCreatorName();
$title = wfMessage( 'flow-post-history', $topicTitle, $creator )->escaped();
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );

$timespans = $historyRenderer->getTimespans( $history );

?>
<div class="flow-history-container">
	<p class='flow-history-pages'>
		<span class="plainlinks">
			<?php echo wfMessage( 'flow-history-pages-post', $topicLink, $topicTitle )->parse(); ?>
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
