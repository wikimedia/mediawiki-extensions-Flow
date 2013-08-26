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
if ( $block->hasErrors( 'content' ) ) {
	echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
}
$content = $summary ? \Flow\ParsoidUtils::convertHTML5ToWikitext( $summary->getContent() ) : '';
echo Html::textarea( $block->getName() . '[content]', $content );
echo Html::element( 'input', array(
	'type' => 'submit',
	'class' => 'mw-ui-button mw-ui-constructive',
	'value' => wfMessage( 'flow-summaryedit-submit' )->plain(),
) );
echo '</form>';

