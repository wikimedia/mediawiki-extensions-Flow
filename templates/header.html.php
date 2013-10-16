<?php

echo Html::openElement(
	'div',
	array(
		'id' => 'flow-header',
		'class' => 'flow-element-container',
	)
);

if ( $block->hasErrors( 'content' ) ) {
	echo Html::element(
		'p',
		array( 'id' => 'flow-header-error' ),
		$block->getError( 'content' )->text()
	);
}

if ( $header ) {
	$headerContent = $header->getContent( $user, 'html' );
	$class = 'flow-header-exists';
} else {
	$headerContent = wfMessage( 'flow-header-empty' )->parse();
	$class = 'flow-header-empty';
}

echo Html::rawElement(
	'div',
	array(
		'id' => 'flow-header-content',
		'class' => $class,
	),
	$headerContent
);

echo Html::element(
	'a',
	array(
		'href' => $this->generateUrl( $workflow, 'edit-header' ),
		'class' => 'flow-header-edit-link flow-icon flow-icon-bottom-aligned',
	),
	wfMessage( 'flow-edit-header-link' )->text()
);

echo Html::closeElement( 'div' );
