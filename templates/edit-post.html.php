<?php

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
	'action' => $this->generateUrl( $topic->getId(), 'edit-post' ),
) );
if ( $block->hasErrors() ) {
	echo '<ul>';
	foreach ( $block->getErrors() as $error ) {
		echo '<li>', $block->getErrorMessage( $error )->escaped() . '</li>';
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
		'value' => $post->getPostId()->getPretty(),
	) ),
	Html::textarea(
		$block->getName() . '[content]',
		$this->getContent( $post, 'wikitext', $user ),
		array(
			'class' => 'mw-ui-input',
			'rows' => '10'
		)
	),
	Html::openElement( 'div', array(
		'class' => 'flow-post-form-controls',
	) ),
		Html::element( 'input', array(
			'type' => 'submit',
			'class' => 'mw-ui-button mw-ui-constructive',
			'value' => wfMessage( 'flow-edit-post-submit' )->plain()
		) ),
	Html::closeElement( 'div' ),
Html::closeElement( 'form' ),
Html::closeElement( 'div' ),
Html::closeElement( 'div' ),
Html::closeElement( 'div' );
