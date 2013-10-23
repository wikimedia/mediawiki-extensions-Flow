<?php

$title = $block->getWorkflow()->getArticleTitle();

if ( $title->getNamespace() === NS_USER_TALK ) {
	$msgKey = 'flow-topic-permalink-warning-user-board';
	$displayText = $title->getText();
} else {
	$msgKey = 'flow-topic-permalink-warning';
	$displayText = $title->getPrefixedText();
}

$message = 	wfMessage(
	$msgKey,
	$displayText,
	$title->getCanonicalUrl()
);

echo Html::rawElement(
	'div',
	array(
		'class' => 'flow-topic-permalink-warning plainlinks',
	),
	$message->parse()
);
