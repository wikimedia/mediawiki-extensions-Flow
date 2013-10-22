<li class="<?php echo $class; ?>">
	<p><?php echo $message->parse(); ?></p>
	<p class="flow-datestamp">
		<a href="<?php echo '#'; /* @todo: link to historical revision */ ?>">
			<span class="flow-agotime" style="display: inline"><?php echo htmlspecialchars( $timestamp->getHumanTimestamp() ); ?></span>
			<span class="flow-utctime" style="display: none"><?php echo htmlspecialchars( $timestamp->getTimestamp( TS_RFC2822 ) ); ?></span>
		</a>
	</p>
	<?php echo $children ? '<ul>' . $children . '</ul>' : ''; ?>
</li>
