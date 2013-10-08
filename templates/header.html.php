<?php

echo Html::openElement(
	'div',
	array( 'id' => 'flow-header' )
);

if ( $block->hasErrors( 'content' ) ) {
	echo Html::element(
		'p',
		array( 'id' => 'flow-header-error' ),
		$block->getError( 'content' )->text()
	);
}

if ( $header ) {
	echo Html::rawElement(
		'div',
		array( 'id' => 'flow-header-content' ),
		$header->getContent( $user, 'html' ) // contains HTML5+RDFa content
	);
}

echo Html::element(
	'a',
	array(
		'href' => $this->generateUrl( $workflow, 'edit-header' ),
		'class' => 'flow-header-edit-link flow-icon flow-icon-bottom-aligned',
	),
	wfMessage( 'flow-edit-header-link' )->text()
);

echo Html::closeElement( 'div' );
