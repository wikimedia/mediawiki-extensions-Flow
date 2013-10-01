<?php

// owning workflow
echo Html::openElement( 'div', array(
	'id' => 'flow-summary'
) );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $workflow, 'edit-summary' ),
	'class' => 'flow-summary-form',
) );
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

echo Html::textarea(
	$block->getName() . '[content]',
	$summary ? $summary->getContent( $user, 'wikitext' ) : '',
	array(
		'class' => 'mw-ui-input',
		'data-summary-id' => $summary ? $summary->getRevisionId()->getHex() : ''
	)
);
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-summary-controls',
) );
echo Html::element( 'input', array(
	'type' => 'submit',
	'class' => 'mw-ui-button mw-ui-constructive',
	'value' => wfMessage( 'flow-summaryedit-submit' )->plain(),
) );
echo Html::closeElement( 'div' );
echo Html::closeElement( 'form' );
echo Html::closeElement( 'div' );
