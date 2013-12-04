<?php

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full'
) );
echo Html::openElement( 'div', array(
	'class' => 'flow-edit-title-form flow-element-container'
) );
echo Html::openElement( 'form', array(
	'method' => 'POST',
	// root post shares its uuid with the workflow
	'action' => $this->generateUrl( $topicTitle->getPostId(), 'edit-title' ),
) );
if ( $block->hasErrors() ) {
	echo '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo '<li>', $error->escaped() . '</li>';
	}
	echo '</ul>';
}

echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken ) ),
	Html::element(
		'input',
		array(
			'name' => $block->getName() . '[content]',
			'class' => 'flow-edit-title-textbox mw-ui-input',
			'value' => $this->getContent( $topicTitle, 'wikitext', $user ),
		),
		$this->getContent( $topicTitle, 'wikitext', $user )
	),
	Html::openElement( 'div', array(
		'class' => 'flow-edit-title-controls',
	) ),
		Html::element( 'input',
			array(
				'type' => 'submit',
				'value' => wfMessage( 'flow-edit-title-submit' )->plain(),
				'class' => 'mw-ui-button mw-ui-constructive',
			)
		),
	Html::closeElement( 'div' ),
Html::closeElement( 'form' ),
Html::closeElement( 'div' ),
Html::closeElement( 'div' );
