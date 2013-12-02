<?php

if ( $block->hasErrors() ) {
	echo '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo '<li>', $error->escaped() . '</li>';
	}
	echo '</ul>';
}

echo Html::openElement( 'form', array(
		'method' => 'POST',
		// root post shares its uuid with the workflow
		'action' => $this->generateUrl( $topicTitle->getPostId(), 'edit-title' ),
	) ),
	Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken ) ),
		Html::textarea( $block->getName() . '[content]', $this->getContent( $topicTitle, 'wikitext', $user ) ),
		Html::element( 'input',
			array(
				'type' => 'submit',
				'value' => wfMessage( 'flow-edit-title-submit' )->plain(),
				'class' => 'mw-ui-button mw-ui-primary',
			)
		),
	Html::closeElement( 'form' );

