<?php

$revisionId = '';
if ( $topicSummary ) {
	// if summary already exists, propagate it's revisions id
	$revisionId = $topicSummary->getRevisionId()->getAlphadecimal();

	if ( $block->hasErrors( 'prev_revision' ) ) {
		$error = $block->getErrorExtra( 'prev_revision' );
		$revisionId = $error['revision_id'];
	}
}

// owning workflow
echo Html::openElement( 'div', array(
	'id' => 'flow-topic-summary-' . $revisionId,
	'class' => 'flow-topic-container'
) );
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-topic-summary-form flow-element-container'
) );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $workflow, 'edit-topic-summary' ),
	'class' => 'flow-edit-form',
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

if ( $topicSummary ) {
	echo Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName()."_prev_revision",
		'value' => $revisionId
	) );
}

echo Html::textarea(
	$block->getName() . '_summary',
	$topicSummary ? $this->getContent( $topicSummary, 'wikitext' ) : '',
	array(
		'class' => 'mw-ui-input',
		'rows' => '10',
		'data-topic-summary-id' => $revisionId
	)
);
echo Html::openElement( 'div', array(
	'class' => 'flow-form-controls flow-edit-controls',
) );
echo Html::rawElement( 'div', array(
	'class' => 'flow-terms-of-use plainlinks',
), Flow\TermsOfUse::getSummarizeTerms() );

// submit button text will be different if there's a more recent change already
$submitMessage = 'flow-summarize-topic-submit';
$submitClass = 'mw-ui-button mw-ui-constructive';
if ( $block->hasErrors( 'prev_revision' ) ) {
	$submitMessage = 'flow-summarize-topic-submit-overwrite';
	$submitClass = 'mw-ui-button mw-ui-destructive';
}
echo Html::element( 'input', array(
	'type' => 'submit',
	'class' => $submitClass,
	'value' => wfMessage( $submitMessage )->plain(),
) );
echo Html::element( 'div', array( 'class' => 'clear' ) );

echo Html::closeElement( 'div' );
echo Html::closeElement( 'form' );
echo Html::closeElement( 'div' );
echo Html::closeElement( 'div' );
