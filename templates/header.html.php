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
		$block->getErrorMessage( 'content' )->parse()
	);
}

if ( $header ) {
	$headerContent = $this->getContent( $header, 'html' );
	$class = 'flow-header-exists';
} else {
	$headerContent = wfMessage( 'flow-header-empty' )->parse();
	$class = 'flow-header-empty';
}

echo Html::rawElement(
	'div',
	array(
		'id' => 'flow-header-content',
		'class' => "$class ui-helper-clearfix",
	),
	$headerContent
);

echo Html::element(
	'a',
	array(
		'href' => $this->urlGenerator->editHeaderLink(
			$workflow->getArticleTitle(),
			$workflow->getId()
		)->getFullUrl(),
		'class' => 'flow-header-edit-link flow-icon flow-icon-bottom-aligned',
		'title' => wfMessage( 'flow-edit-header-link' )->text()
	),
	wfMessage( 'flow-edit-header-link' )->text()
);

echo Html::closeElement( 'div' );
