<<<<<<< HEAD   (649234 Fix issues with empty TopicList blocks / formatters.)
=======
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
	'action' => $this->urlGenerator->editPostLink(
		$topic->getArticleTitle(),
		$topic->getId(),
		$post->getPostId()
	)->getFullURL(),
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
		'name' => $block->getName() . '[prev_revision]',
		'value' => $revisionId
	) ),
	Html::rawElement(
		'textarea',
		array(
			'name' => $block->getName() . '_content',
			'class' => 'mw-ui-input',
			'rows' => '10'
		),
		$this->getContent( $post, 'wikitext' )
	),
	Html::openElement( 'div', array(
		'class' => 'flow-form-controls',
	) ),
		Html::rawElement( 'div', array(
			'class' => 'flow-terms-of-use plainlinks',
		), Flow\TermsOfUse::getEditTerms() ),
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
>>>>>>> BRANCH (d50ded Merge "Repair non-js post editing")
