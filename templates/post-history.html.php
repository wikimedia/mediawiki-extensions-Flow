<?php

$topicTitle = $root->getContent( $user, 'wikitext' );
$topicLink = $this->generateUrl( $topic );
$creator = $this->getUserText( $post->getCreator() );
$title = wfMessage( 'flow-post-history', $topicTitle, $creator )->escaped();
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );

$timestampLast4 = new MWTimestamp( strtotime( '4 hours ago' ) );
$timestampDay = new MWTimestamp( strtotime( date( 'Y-m-d' ) ) );
$timestampWeek = new MWTimestamp( strtotime( '1 week ago' ) );

$timespans = array(
	'flow-history-last4' => $history->getTimespan( $timestampLast4, null ),
	'flow-history-day' => $history->getTimespan( $timestampDay, $timestampLast4 ),
	'flow-history-week' => $history->getTimespan( $timestampWeek, $timestampDay ),
	'flow-history-old' => $history->getTimespan( null, $timestampWeek ),
);

?>
<div class="flow-history-container">
	<p class='flow-history-pages'><?php echo wfMessage( 'flow-history-pages-post', $topicLink, $topicTitle )->parse(); ?></p>

	<div class="flow-history-log">
		<?php
		foreach ( $timespans as $i18n => $history ) {
			if ( $history->numRows() ) {
				echo '<h2>' . wfMessage( $i18n )->escaped() . '</h2>';
				echo $historyRenderer->render( $history );
			}
		}
		?>
	</div>
</div>
