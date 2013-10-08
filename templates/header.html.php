<?php

if ( $block->hasErrors( 'content' ) ) {
	echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
}
if ( $header ) {
	?><div class="flow-header"><?php
	// contains HTML5+RDFa content
	echo $header->getContent( $user, 'html' );
	?></div><?php
}

$editLink = $this->generateUrl( $workflow, 'edit-header' );
?>
<div class="flow-header-edit-link"><a href="<?php echo htmlspecialchars( $editLink );?>"><?php echo wfMessage( 'flow-edit-header-link' )->escaped()?></a></div>
