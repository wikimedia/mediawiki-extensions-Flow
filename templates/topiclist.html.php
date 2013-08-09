<?php

$editToken = $user->getEditToken( 'flow' );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $topicList->getWorkflow(), 'new-topic' )
) );
echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

echo '<h3>' . wfMessage( 'flow-topic-title' )->escaped() . '</h3>';
if ( $topicList->hasErrors( 'topic' ) ) {
	echo '<p>' . $topicList->getError( 'topic' )->escaped() . '</p>';
}
echo Html::textarea( $topicList->getName() . '[topic]' );

echo '<h3>' . wfMessage( 'flow-topic-content' )->escaped() . '</h3>';
if ( $topicList->hasErrors( 'content' ) ) {
	echo '<p>' . $topicList->getError( 'content' )->escaped() . '</p>';
}
echo Html::textarea( $topicList->getName() . '[content]' );
echo Html::element( 'input', array(
	'type' => 'submit',
	'value' => wfMessage( 'flow-new-message' )->plain()
) );
echo '</form>';

foreach ( $topics as $topic ) {
	echo $topic->render( $this, array(), true );
}
