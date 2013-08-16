<?php

if ( $block->hasErrors( 'content' ) ) {
	echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
}
if ( $summary ) {
	// contains HTML5+RDFa content
	echo $summary->getContent( $user, 'html' );
}

$editLink = $this->generateUrl( $workflow, 'edit-summary' );
?>
<div class="flow-summary-edit-link"><a href="<?php echo htmlspecialchars( $editLink );?>"><?php echo wfMessage( 'flow-edit-summary-link' )->escaped()?></a></div>
