<?php

$pageTitle = $topic->getArticleTitle();
$pageLink = $this->urlGenerator->boardLink( $pageTitle )->getFullUrl();
$pageTitle = $pageTitle->getText();

$title = $this->getContent( $root, 'wikitext' );
$title = wfMessage( 'flow-topic-history' )->rawParams( htmlspecialchars( $title ) )->escaped();
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
