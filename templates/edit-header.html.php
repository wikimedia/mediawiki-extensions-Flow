<?php

// owning workflow
echo Html::openElement( 'div', array(
	'id' => 'flow-header',
) );
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-header-form flow-element-container'
) );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $workflow, 'edit-header' ),
	'class' => 'flow-header-form',
) );
if ( $block->hasErrors() ) {
	echo '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo '<li>', $block->getErrorMessage( $error )->parse() . '</li>';
	}
	echo '</ul>';
}

echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );
if ( $header ) {
	echo Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName()."[prev_revision]",
		'value' => $header->getRevisionId()->getHex(),
	) );
}

echo Html::textarea(
	$block->getName() . '[content]',
	$header ? $this->getContent( $header, 'wikitext' ) : '',
	array(
		'class' => 'mw-ui-input',
		'rows' => '10',
		'data-header-id' => $header ? $header->getRevisionId()->getHex() : ''
	)
);
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-header-controls',
) );

echo Html::element( 'input', array(
	'type' => 'submit',
	'class' => 'mw-ui-button mw-ui-constructive',
	'value' => wfMessage( 'flow-edit-header-submit' )->plain(),
) );
echo Html::closeElement( 'div' );
echo Html::closeElement( 'form' );
echo Html::closeElement( 'div' );
echo Html::closeElement( 'div' );
