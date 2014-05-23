<<<<<<< HEAD   (649234 Fix issues with empty TopicList blocks / formatters.)
=======
<?php

$pageTitle = $topic->getArticleTitle();
$pageLink = $this->urlGenerator->boardLink( $pageTitle )->getFullUrl();
$pageTitle = $pageTitle->getText();

// $title sourced from Templating::getContent is safe for output
$title = $this->getContent( $root, 'wikitext' );
$title = wfMessage( 'flow-topic-history' )->rawParams( $title )->escaped();
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );

$timespans = $historyRenderer->getTimespans( $history );

?>
<div class="flow-history-container">
	<p class="flow-history-pages">
		<span class="plainlinks">
			<?php echo wfMessage( 'flow-history-pages-topic', $pageLink, $pageTitle )->parse(); ?>
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
>>>>>>> BRANCH (d50ded Merge "Repair non-js post editing")
