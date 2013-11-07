<li class="<?php echo $class; ?>">
	<p><span class="plainlinks"><?php echo $message->parse(); ?></span></p>
<?php
echo $this->render( 'flow:timestamp.html.php', array(
	'timestamp' => $timestamp,
	'historicalLink' => $historicalLink,
), true );
?>
	<?php echo $children ? '<ul>' . $children . '</ul>' : ''; ?>
</li>
