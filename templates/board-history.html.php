<?php
$this->getOutput()->setHtmlTitle( $title );
$this->getOutput()->setPageTitle( $title );
?>
<div class="flow-board-history-container">
	<div class="flow-history-log">
		<ul>
			<li><?php echo implode( "</li>\n<li>", $lines ) ?></li>
		</ul>
	</div>
</div>
