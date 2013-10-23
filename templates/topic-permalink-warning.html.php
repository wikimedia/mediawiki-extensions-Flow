<?php echo Html::rawElement(
	'div',
	array(
		'class' => 'flow-topic-permalink-warning plainlinks',
	),
	wfMessage(
		'flow-topic-permalink-warning',
		$block->getWorkflow()->getArticleTitle()->getPrefixedText(),
		$block->getWorkflow()->getArticleTitle()->getCanonicalUrl()
	)->parse()
);
