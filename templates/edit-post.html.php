<?php

echo Html::openElement( 'form', array(
	'method' => 'POST',
	'action' => $this->generateUrl( $topic->getId(), 'edit-post' ),
) );
if ( $block->hasErrors() ) {
	echo '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo '<li>', $error->escaped() . '</li>';
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
		'name' => $block->getName() . '[postId]',
		'value' => $post->getPostId()->getHex(),
	) ),
	Html::textarea( $block->getName() . '[content]', $this->getContent( $post, 'wikitext', $user ) ),
	Html::element( 'input', array(
		'type' => 'submit',
		'class' => 'mw-ui-button mw-ui-primary',
		'value' => wfMessage( 'flow-edit-post-submit' )->plain()
	) ),
	Html::closeElement( 'form' );
