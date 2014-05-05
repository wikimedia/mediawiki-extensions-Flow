<?php

$revisionId = $topicTitle->getRevisionId()->getAlphadecimal();

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
$submitClass = 'mw-ui-button mw-ui-constructive';
if ( $block->hasErrors( 'prev_revision' ) ) {
	$submitMessage = 'flow-edit-title-submit-overwrite';
	$submitClass = 'mw-ui-button mw-ui-destructive';
}

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full'
) );
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-title-form flow-element-container'
) );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	// $topicTitle->getPostId() is the same as the workflow id
	'action' => $this->urlGenerator->editTitleLink( null, $topicTitle->getPostId() )->getFullUrl(),
) );
if ( $block->hasErrors() ) {
	echo '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo '<li>', $block->getErrorMessage( $error )->parse() . '</li>';
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
			'name' => $block->getName() . '_content',
			'class' => 'flow-edit-content mw-ui-input',
			'value' => $this->getContent( $topicTitle, 'wikitext' ),
		),
		$this->getContent( $topicTitle, 'wikitext' )
	),
	Html::openElement( 'div', array(
		'class' => 'flow-edit-title-controls',
	) ),
	Html::rawElement( 'div', array(
		'class' => 'flow-terms-of-use plainlinks'
	),  Flow\TermsOfUse::getReplyTerms() ),
		Html::element( 'input',
			array(
				'type' => 'submit',
				'class' => $submitClass,
				'value' => wfMessage( $submitMessage )->plain(),
			)
		),
	Html::element( 'div', array( 'class' => 'clear' ) ),
	Html::closeElement( 'div' ),
Html::closeElement( 'form' ),
Html::closeElement( 'div' ),
Html::closeElement( 'div' );
