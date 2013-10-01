<?php

echo Html::openElement(
	'div',
	array( 'id' => 'flow-summary' )
);

if ( $block->hasErrors( 'content' ) ) {
	echo Html::element(
		'p',
		array( 'id' => 'flow-summary-error' ),
		$block->getError( 'content' )->text()
	);
}

if ( $summary ) {
	echo Html::rawElement(
		'div',
		array( 'id' => 'flow-summary-content' ),
		$summary->getContent( $user, 'html' ) // contains HTML5+RDFa content
	);
}

echo Html::element(
	'a',
	array(
		'href' => $this->generateUrl( $workflow, 'edit-summary' ),
		'class' => 'flow-summary-edit-link flow-icon flow-icon-bottom-aligned',
	),
	wfMessage( 'flow-edit-summary-link' )->text()
);

echo Html::closeElement( 'div' );
