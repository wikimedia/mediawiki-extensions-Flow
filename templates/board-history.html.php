<?php
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );
?>
<div class="flow-history-container">
	<div class="flow-history-log">
		<?php
			if ( $historyExists ) {
				$timespans = $historyRenderer->getTimespans( $history );
				foreach ( $timespans as $text => $timespan ) {
					$timespan = $history->getTimespan( $timespan['from'], $timespan['to'] );
					if ( $timespan->numRows() ) {
						echo "<h2>$text</h2>";
						echo $historyRenderer->render( $timespan );
					}
				}
			}
		?>
	</div>
</div>
