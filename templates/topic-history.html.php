<?php

$pageTitle = $topic->getArticleTitle();
$pageLink = $this->urlGenerator->buildUrl( $pageTitle, 'view' );
$pageTitle = $pageTitle->getText();

$title = $root->getContent( $user, 'wikitext' );
$title = wfMessage( 'flow-topic-history', $title )->escaped();
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );

$timespans = $historyRenderer->getTimespans( $history );

?>
<div class="flow-history-container">
	<p class='flow-history-pages'><?php echo wfMessage( 'flow-history-pages-topic', $pageLink, $pageTitle )->parse(); ?></p>

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
