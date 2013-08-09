<?php

if ( $block->hasErrors( 'content' ) ) {
	echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
}
if ( $summary ) {
	echo htmlspecialchars( $summary->getContent() );
}

$editLink = $this->generateUrl( $workflow, 'edit-summary' );
?>
<div class="flow-summary-edit-link"><a href="<?php echo htmlspecialchars($editLink);?>"><?php echo wfMessage( 'flow-edit-summary-link' )->escaped()?></a></div>