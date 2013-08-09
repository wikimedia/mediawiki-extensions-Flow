<?php

// owning workflow
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $workflow, 'edit-summary' ),
) );
$editToken = $user->getEditToken( 'flow' );
echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );
if ( $block->hasErrors( 'prev_revision' ) ) {
	echo '<p>' . $block->getError( 'prev_revision' )->escaped() . '</p>';
}
if ( $summary ) {
	echo Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName()."[prev_revision]",
		'value' => $summary->getRevisionId()->getHex(),
	) );
}
echo '<h3>' . wfMessage( 'flow-summary' )->escaped() . '</h3>';
if ( $block->hasErrors( 'content' ) ) {
	echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
}
echo Html::textarea( $block->getName() . '[content]', $summary ? $summary->getContent() : '' );
echo Html::element( 'input', array(
	'type' => 'submit',
	'value' => wfMessage( 'flow-edit-summary' )->plain(),
) );
echo '</form>';

