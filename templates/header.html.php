<div class="flow-header">
<?php
if ( $block->hasErrors( 'content' ) ) {
	echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
}

if ( $header ) {
	// contains HTML5+RDFa content
	echo Html::rawElement(
		'div',
		array( 'class' => 'flow-header-content' ),
		$header->getContent( $user, 'html' )
	);
}

$editLink = $this->generateUrl( $workflow, 'edit-header' );
?>
<div class="flow-header-edit-link"><a href="<?php echo htmlspecialchars( $editLink );?>"><?php echo wfMessage( 'flow-edit-header-link' )->escaped()?></a></div>
</div>
