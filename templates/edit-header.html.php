<?php

// owning workflow
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $workflow, 'edit-header' ),
	'class' => 'flow-header-form',
) );
echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );
if ( $block->hasErrors( 'prev_revision' ) ) {
	echo '<p>' . $block->getError( 'prev_revision' )->escaped() . '</p>';
}
if ( $header ) {
	echo Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName()."[prev_revision]",
		'value' => $header->getRevisionId()->getHex(),
	) );
}
if ( $block->hasErrors( 'content' ) ) {
	echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
}

echo Html::textarea(
	$block->getName() . '[content]',
	$header ? $header->getContent( $user, 'wikitext' ) : '',
	array(
		'data-header-id' => $header ? $header->getRevisionId()->getHex() : ''
	)
);
echo Html::element( 'input', array(
	'type' => 'submit',
	'class' => 'mw-ui-button mw-ui-constructive',
	'value' => wfMessage( 'flow-headeredit-submit' )->plain(),
) );
echo Html::closeElement( 'form' );
