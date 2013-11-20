<?php

// @todo consolidate into FlowActions, currently isAllowed method only exists in PostActionPermissions
if ( $user->isAllowed( 'edit' ) ) {
	echo Html::openElement( 'div', array( 'class' => 'flow-new-topic-container flow-element-container' ) );
	echo Html::openElement( 'form', array(
		'method' => 'POST',
		'action' => $this->generateUrl( $block->getWorkflow(), 'new-topic' ),
		'class' => 'flow-newtopic-form',
	) );
	echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

	if ( $block->hasErrors( 'topic' ) ) {
		echo '<p>' . $block->getError( 'topic' )->escaped() . '</p>';
	}
	echo Html::input(
		$block->getName() . '[topic]', '', 'text', array(
			'placeholder' => wfMessage( 'flow-newtopic-title-placeholder' )->text(),
			'title' => wfMessage( 'flow-newtopic-title-placeholder' )->text(),
			'class' => 'flow-newtopic-title mw-ui-input',
		)
	);

	if ( $block->hasErrors( 'content' ) ) {
		echo '<p>' . $block->getError( 'content' )->escaped() . '</p>';
	}
	echo Html::textarea( $block->getName() . '[content]', '', array(
		'placeholder' => wfMessage( 'flow-newtopic-content-placeholder' )->text(),
		'title' => wfMessage( 'flow-newtopic-content-placeholder' )->text(),
		'class' => 'flow-newtopic-step2 flow-newtopic-content mw-ui-input',
		'rows' => '10',
	) );
	echo Html::openElement( 'div', array( 'class' => 'flow-post-form-controls flow-newtopic-step2' ) );
	echo Html::element( 'input', array(
		'type' => 'submit',
		'class' => 'mw-ui-button mw-ui-constructive flow-newtopic-submit',
		'value' => wfMessage( 'flow-newtopic-save' )->text(),
	) );
	echo Html::closeElement( 'div' );
	echo Html::closeElement( 'form' );
	echo Html::closeElement( 'div' );
}

if ( $page && $page->getPagingLink( 'rev' ) ) {
	$linkData = $page->getPagingLink( 'rev' );
	echo $this->getPagingLink( $block, 'rev', $linkData['offset'], $linkData['limit'] );
}

/*
 * We'll want to register the Parsoidlinks callback as soon as possible, so that
 * once the first topic is being rendered, the links for the last topic have
 * already been pre-loaded as well.
 */
foreach ( $topics as $topic ) {
	$root = $topic->loadRootPost();
	$this->registerParsoidLinks( $root );
}

foreach ( $topics as $topic ) {
	echo $topic->render( $this,
		array(
			'topiclist-block' => $block,
		),
		true
	);
}

if ( $page && $page->getPagingLink( 'fwd' ) ) {
	$linkData = $page->getPagingLink( 'fwd' );
	echo $this->getPagingLink( $block, 'fwd', $linkData['offset'], $linkData['limit'] );
}
