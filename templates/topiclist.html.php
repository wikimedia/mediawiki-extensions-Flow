<?php

$editToken = $user->getEditToken( 'flow' );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $topicList->getWorkflow(), 'new-topic' ),
	'class' => 'flow-newtopic-form',
) );
echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

if ( $topicList->hasErrors( 'topic' ) ) {
	echo '<p>' . $topicList->getError( 'topic' )->escaped() . '</p>';
}
echo Html::textarea( $topicList->getName() . '[topic]', '', array(
	'placeholder' => wfMessage( 'flow-newtopic-title-placeholder' )->text(),
	'class' => 'flow-newtopic-title flow-input',
	'rows' => 1,
) );

if ( $topicList->hasErrors( 'content' ) ) {
	echo '<p>' . $topicList->getError( 'content' )->escaped() . '</p>';
}
echo Html::textarea( $topicList->getName() . '[content]', '', array(
	'placeholder' => wfMessage( 'flow-newtopic-content-placeholder' )->text(),
	'class' => 'flow-newtopic-step2 flow-newtopic-content flow-input',
	'rows' => '10',
) );
echo Html::openElement( 'div', array( 'class' => 'flow-post-form-controls flow-newtopic-step2' ) );
echo Html::element( 'input', array(
	'type' => 'submit',
	'class' => 'mw-ui-button mw-ui-primary flow-newtopic-submit',
	'value' => wfMessage( 'flow-newtopic-save' )->text(),
) );
echo Html::closeElement( 'div' );
echo Html::element( 'div', array(
	'class' => 'flow-disclaimer flow-newtopic-step2',
), wfMessage( 'flow-disclaimer' )->parse() );
echo '</form>';

foreach ( $topics as $topic ) {
	echo $topic->render( $this, array(), true );
}
