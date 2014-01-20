<?php

$revisionId = '';
if ( $header ) {
	// if header already exists, propagate it's revisions id
	$revisionId = $header->getRevisionId()->getHex();

	/*
	 * If we tried to submit a change against a revision that is not the latest,
	 * $header will be our own change; let's get the real revision id from the
	 * error details.
	 */
	if ( $block->hasErrors( 'prev_revision' ) ) {
		$error = $block->getErrorExtra( 'prev_revision' );
		$revisionId = $error['revision_id'];
	}
}

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

echo Html::element( 'input', array(
	'type' => 'hidden',
	'name' => 'wpEditToken',
	'value' => $editToken
) );

if ( $header ) {
	echo Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName()."[prev_revision]",
		'value' => $revisionId
	) );
}

echo Html::textarea(
	$block->getName() . '[content]',
	$header ? $this->getContent( $header, 'wikitext' ) : '',
	array(
		'class' => 'mw-ui-input',
		'rows' => '10',
		'data-header-id' => $revisionId
	)
);
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-header-controls',
) );

// submit button text will be different if there's a more recent change already
$submitMessage = 'flow-edit-header-submit';
if ( $block->hasErrors( 'prev_revision' ) ) {
	$submitMessage = 'flow-edit-header-submit-overwrite';
}
echo Html::element( 'input', array(
	'type' => 'submit',
	'class' => 'mw-ui-button mw-ui-constructive',
	'value' => wfMessage( $submitMessage )->plain(),
) );

echo Html::closeElement( 'div' );
echo Html::closeElement( 'form' );
echo Html::closeElement( 'div' );
echo Html::closeElement( 'div' );
