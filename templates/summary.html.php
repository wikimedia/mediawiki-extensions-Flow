<?php

// owning workflow
if ( $block->needCreate() ) {
	$postAction = $this->generateUrlForCreate( $workflow, 'edit-summary' );
} else {
	$postAction = $this->generateUrl( $workflow, 'edit-summary' );
}
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $postAction,
) );
if ( $block->hasErrors( 'prev_revision' ) ) {
	echo '<p>' . $block->getError( 'prev_revision' )->escaped() . '</p>';
}
if ( $summary ) {
	echo Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName()."[prev_revision]",
		'value' => $summary->getRevisionId(),
	) );
}
echo '<h3>' . wfMessage( 'flow-summary' )->escaped() . '</h3>';
if ( $block->hasErrors( 'content' ) ) {
	echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
}
echo Html::textarea( $block->getName() . '[content]', $summary ? $summary->getContent() : '' );
echo Html::element( 'input', array(
	'type' => 'submit',
	'value' => wfMessage( 'flow-edit-summary' )->text(),
) );
echo '</form>';

