<li class="<?php echo $class; ?>">
	<p><?php echo $message->parse(); ?></p>
	<p class="flow-datestamp">
		<span class="flow-agotime" style="display: inline"><?php echo htmlspecialchars( $timestamp->getHumanTimestamp() ); ?></span>
		<span class="flow-utctime" style="display: none"><?php echo htmlspecialchars( $timestamp->getTimestamp( TS_RFC2822 ) ); ?></span>
	</p>
	<?php echo $children ? '<ul>' . $children . '</ul>' : ''; ?>
</li>
