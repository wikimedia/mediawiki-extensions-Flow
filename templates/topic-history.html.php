<?php

$title = $root->getContent( $user, 'wikitext' );
$title = wfMessage( 'flow-topic-history', $title )->escaped();
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );

$timestampLast4 = new MWTimestamp( strtotime( '4 hours ago' ) );
$timestampDay = new MWTimestamp( strtotime( date( 'Y-m-d' ) ) );
$timestampWeek = new MWTimestamp( strtotime( '1 week ago' ) );

$timespans = array(
	'flow-topic-history-last4' => $history->getTimespan( $timestampLast4, null ),
	'flow-topic-history-day' => $history->getTimespan( $timestampDay, $timestampLast4 ),
	'flow-topic-history-week' => $history->getTimespan( $timestampWeek, $timestampDay ),
	'flow-topic-history-old' => $history->getTimespan( null, $timestampWeek ),
);

?>
<div class="flow-history-container">
<!--
@todo: build functionality
	<p class='flow-history-pages'>Appears on <a>X's Talkpage</a> and Y others</p>";
-->

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
