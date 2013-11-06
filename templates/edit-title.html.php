<?php

echo wfMessage( 'flow-action-edit-title' )->escaped();
if ( $block->hasErrors() ) {
	echo wfMessage( 'flow-action-errors' )->escaped(), '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo $error->escaped();
	}
	echo '</ul>';
}

echo Html::openElement( 'form', array(
		'method' => 'POST',
		// root post shares its uuid with the workflow
		'action' => $this->generateUrl( $topicTitle->getPostId(), 'edit-title' ),
	) ),
	Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken ) ),
		Html::textarea( $block->getName() . '[content]', $this->getContent( $topicTitlex, 'wikitext', $topicTitle ) ),
		Html::element( 'input',
			array(
				'type' => 'submit',
				'value' => wfMessage( 'flow-action-edit-title' )->plain(),
				'class' => 'mw-ui-button mw-ui-primary',
			)
		),
	Html::closeElement( 'form' );

