<?php

$revisionId = $topicTitle->getRevisionId()->getHex();

/*
 * If we tried to submit a change against a revision that is not the latest,
 * $header will be our own change; let's get the real revision id from the
 * error details.
 */
if ( $block->hasErrors( 'prev_revision' ) ) {
	$error = $block->getErrorExtra( 'prev_revision' );
	$revisionId = $error['revision_id'];
}

// submit button text will be different if there's a more recent change already
$submitMessage = 'flow-edit-title-submit';
if ( $block->hasErrors( 'prev_revision' ) ) {
	$submitMessage = 'flow-edit-title-submit-overwrite';
}

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full'
) );
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-title-form flow-element-container'
) );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	// root post shares its uuid with the workflow
	'action' => $this->generateUrl( $topicTitle->getPostId(), 'edit-title' ),
) );
if ( $block->hasErrors() ) {
	echo '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo '<li>', $block->getErrorMessage( $error )->escaped() . '</li>';
	}
	echo '</ul>';
}

echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken ) ),
	Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName() . '[prev_revision]',
		'value' => $revisionId
	) ),
	Html::element(
		'input',
		array(
			'name' => $block->getName() . '[content]',
			'class' => 'flow-edit-title-textbox mw-ui-input',
			'value' => $this->getContent( $topicTitle, 'wikitext' ),
		),
		$this->getContent( $topicTitle, 'wikitext' )
	),
	Html::openElement( 'div', array(
		'class' => 'flow-edit-title-controls',
	) ),
		Html::element( 'input',
			array(
				'type' => 'submit',
				'value' => wfMessage( $submitMessage )->plain(),
				'class' => 'mw-ui-button mw-ui-constructive',
			)
		),
	Html::closeElement( 'div' ),
Html::closeElement( 'form' ),
Html::closeElement( 'div' ),
Html::closeElement( 'div' );
