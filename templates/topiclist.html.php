<?php

$editToken = $user->getEditToken( 'flow' );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $topicList->getWorkflow(), 'new-topic' ),
	'class' => 'flow-newtopic-form',
) );
echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

echo '<h3>' . wfMessage( 'flow-newtopic-header' )->escaped() . '</h3>';
if ( $topicList->hasErrors( 'topic' ) ) {
	echo '<p>' . $topicList->getError( 'topic' )->escaped() . '</p>';
}
echo Html::textarea( $topicList->getName() . '[topic]', '', array(
	'placeholder' => wfMessage( 'flow-newtopic-title-placeholder' )->text(),
	'rows' => 1,
) );

if ( $topicList->hasErrors( 'content' ) ) {
	echo '<p>' . $topicList->getError( 'content' )->escaped() . '</p>';
}
echo Html::textarea( $topicList->getName() . '[content]', '', array(
	'placeholder' => wfMessage( 'flow-newtopic-content-placeholder' )->text(),
	'rows' => '10',
) );
echo Html::element( 'input', array(
	'type' => 'submit',
	'value' => wfMessage( 'flow-newtopic-save' )->text(),
) );
echo Html::element( 'div', array(
	'class' => 'flow-newtopic-caution',
), wfMessage( 'flow-newtopic-caution' )->parse() );
echo '</form>';

foreach ( $topics as $topic ) {
	echo $topic->render( $this, array(), true );
}
