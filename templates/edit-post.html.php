<?php

echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $topic->getId(), 'edit-post' ),
) );
$editToken = $user->getEditToken( 'flow' );
if ( $block->hasErrors() ) {
	echo '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo '<li>', $error->text() . '</li>'; // the pain ...
	}
	echo '</ul>';
}

echo Html::element( 'input', array(
		'type' => 'hidden',
		'name' => 'wpEditToken',
		'value' => $user->getEditToken( 'flow' ),
	) ),
	Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName() . '[postId]',
		'value' => $post->getPostId()->getHex(),
	) ),
	Html::textarea( $block->getName() . '[content]', $post->getContent() ),
	Html::element( 'input', array(
		'type' => 'submit',
		'value' => wfMessage( 'flow-action-edit-post' )->plain()
	) ),
	'</form>';

