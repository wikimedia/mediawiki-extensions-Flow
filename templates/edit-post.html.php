<?php

$revisionId = $post->getRevisionId()->getAlphadecimal();

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
$submitMessage = 'flow-edit-post-submit';
$submitClass = 'mw-ui-button mw-ui-constructive';
if ( $block->hasErrors( 'prev_revision' ) ) {
	$submitMessage = 'flow-edit-post-submit-overwrite';
	$submitClass = 'mw-ui-button mw-ui-destructive';
}

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full'
) );
echo Html::openElement( 'div', array(
	'class' => 'flow-post-container'
) );
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-post-form flow-element-container'
) );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $topic->getId(), 'edit-post' ),
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
		'value' => $editToken,
	) ),
	Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName() . '_postId',
		'value' => $post->getPostId()->getAlphadecimal(),
	) ),
	Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName() . '[prev_revision]',
		'value' => $revisionId
	) ),
	Html::textarea(
		$block->getName() . '_content',
		$this->getContent( $post, 'wikitext' ),
		array(
			'class' => 'mw-ui-input',
			'rows' => '10'
		)
	),
	Html::openElement( 'div', array(
		'class' => 'flow-post-form-controls',
	) ),
		Html::rawElement( 'div', array(
			'class' => 'flow-terms-of-use plainlinks',
		), wfMessage( 'flow-terms-of-use-edit' )->parse() ),
		Html::element( 'input', array(
			'type' => 'submit',
			'class' => $submitClass,
			'value' => wfMessage( $submitMessage )->plain()
		) ),
		Html::element( 'div', array( 'class' => 'clear' ) ),
	Html::closeElement( 'div' ),
Html::closeElement( 'form' ),
Html::closeElement( 'div' ),
Html::closeElement( 'div' ),
Html::closeElement( 'div' );
