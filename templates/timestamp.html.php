<?php echo Html::openElement( isset( $tag ) ? $tag : 'p', array( 'class' => 'flow-datestamp' ) ); ?>
	<?php if ( isset( $historicalLink ) && $historicalLink !== null ) {?>
	<a href="<?php echo $historicalLink; ?>">
	<?php } ?>
		<span class="flow-agotime" style="display: inline"><?php echo htmlspecialchars( $timestamp->getHumanTimestamp() ); ?></span>
		<span class="flow-utctime" style="display: none"><?php echo htmlspecialchars( $timestamp->getTimestamp( TS_RFC2822 ) ); ?></span>
	</a>
	<?php if ( isset( $historicalLink ) && $historicalLink !== null ) { echo '</a>'; } ?>
<?php echo Html::closeElement( isset( $tag ) ? $tag : 'p' ); ?>