<?php
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );
$timespans = $historyRenderer->getTimespans( $history );
?>
<div class="flow-history-container">
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
