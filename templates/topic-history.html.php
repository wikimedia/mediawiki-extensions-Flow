<?php

$title = $root->getContent( $user, 'wikitext' );
$title = wfMessage( 'flow-topic-history', $title )->escaped();
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );

$timestampDay = new MWTimestamp( strtotime( date( 'Y-m-d' ) ) );
$timestampWeek = new MWTimestamp( strtotime( '1 week ago' ) );

$day = $history->getTimespan( $timestampDay, null );
$week = $history->getTimespan( $timestampWeek, $timestampDay );
$old = $history->getTimespan( null, $timestampWeek );

?>
<div class="flow-history-container">
<!--
@todo: build functionality
	<p class='flow-history-pages'>Appears on <a>X's Talkpage</a> and Y others</p>";
-->

	<div class="flow-history-log">
		<?php
			if ( $day->numRows() ) {
				echo '<h2>' . wfMessage( 'flow-topic-history-day' )->escaped() . '</h2>';
				echo $day->render();
			}
			if ( $week->numRows() ) {
				echo '<h2>' . wfMessage( 'flow-topic-history-week' )->escaped() . '</h2>';
				echo $week->render();
			}
			if ( $old->numRows() ) {
				echo '<h2>' . wfMessage( 'flow-topic-history-old' )->escaped() . '</h2>';
				echo $old->render();
			}
		?>
	</div>
</div>
