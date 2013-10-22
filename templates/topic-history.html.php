<?php

use Flow\View\History;

$title = $root->getContent( $user, 'wikitext' );
$title = wfMessage( 'flow-topic-history', $title )->escaped();
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );

// organize history per timespan
$day = new History();
$week = new History();
$old = new History();

$timestampDay = wfTimestamp( TS_MW, strtotime( date( 'Y-m-d' ) ) );
$timestampWeek = wfTimestamp( TS_MW, strtotime( '1 week ago' ) );

foreach ( $history as $revision ) {
	$timestamp = $revision->getRevisionId()->getTimestamp();

	if ( $timestamp > $timestampDay ) {
		$day->addRevision( $revision );
	} elseif ( $timestamp > $timestampWeek ) {
		$week->addRevision( $revision );
	} else {
		$old->addRevision( $revision );
	}
}

?>
<div class="flow-history-container">
<!--
@todo: build functionality
	<p class='flow-history-pages'>Appears on <a>X's Talkpage</a> and Y others</p>";
-->

	<div class="flow-history-log">
		<?php
			if ( $day->numRows() ) {
				echo '<h2>' . wfMessage( 'flow-topic-history-day' )->escaped() . '</h2>' .
				$day->render();
			}
			if ( $week->numRows() ) {
				echo '<h2>' . wfMessage( 'flow-topic-history-week' )->escaped() . '</h2>' .
				$week->render();
			}
			if ( $old->numRows() ) {
				echo '<h2>' . wfMessage( 'flow-topic-history-old' )->escaped() . '</h2>' .
				$old->render();
			}
		?>
	</div>
</div>
