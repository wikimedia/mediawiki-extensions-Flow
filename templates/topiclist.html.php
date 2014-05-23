<<<<<<< HEAD   (649234 Fix issues with empty TopicList blocks / formatters.)
=======
<?php

if ( $permissions->isAllowed( null, 'new-post' ) ) {
	$workflow = $block->getWorkflow();
	echo Html::openElement( 'div', array( 'class' => 'flow-new-topic-container flow-element-container' ) );
	echo Html::openElement( 'form', array(
		'method' => 'POST',
		'action' => $this->urlGenerator->newTopicLink(
			$workflow->getArticleTitle(),
			$workflow->isNew() ? null : $workflow->getId()
		)->getFullUrl(),
		'class' => 'flow-newtopic-form',
		'id' => 'flow-newtopic-form',
	) );
	echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

	if ( $block->hasErrors( 'topic' ) ) {
		echo '<p>' . $block->getErrorMessage( 'topic' )->parse() . '</p>';
	}
	echo Html::input(
		$block->getName() . '_topic', '', 'text', array(
			'placeholder' => wfMessage( 'flow-newtopic-title-placeholder' )->text(),
			'title' => wfMessage( 'flow-newtopic-title-placeholder' )->text(),
			'class' => 'flow-newtopic-title mw-ui-input',
		)
	);

	if ( $block->hasErrors( 'content' ) ) {
		echo '<p>' . $block->getErrorMessage( 'content' )->parse() . '</p>';
	}
	echo Html::textarea( $block->getName() . '_content', '', array(
		'placeholder' => wfMessage( 'flow-newtopic-content-placeholder' )->text(),
		'class' => 'flow-newtopic-step2 flow-newtopic-content mw-ui-input',
		'rows' => '10',
	) );
	echo Html::openElement( 'div', array( 'class' => 'flow-form-controls flow-newtopic-step2' ) );
	echo Html::rawElement( 'div', array(
		'class' => 'flow-terms-of-use plainlinks',
	), Flow\TermsOfUse::getAddTopicTerms() );
	echo Html::element( 'input', array(
		'type' => 'submit',
		'class' => 'mw-ui-button mw-ui-constructive flow-newtopic-submit',
		'value' => wfMessage( 'flow-newtopic-save' )->text(),
	) );
	echo Html::element( 'div', array( 'class' => 'clear' ) );
	echo Html::closeElement( 'div' );
	echo Html::closeElement( 'form' );
	echo Html::closeElement( 'div' );
}

if ( $page ) {
	echo $this->buildPagingLinkHtml( $block, $page, 'rev' );
}

// @todo hide for non-js users, or support them?
?>

<ul class="topic-collapser ui-helper-clearfix flow-element-container">
	<?php $msg = wfMessage( 'flow-topic-collapsed-one-line' )->escaped(); ?>
	<li class="topic-one-line" data-collapse-class="topic-collapsed-one-line" title="<?php echo $msg; ?>">
		<?php echo $msg; ?>
	</li>
	<?php $msg = wfMessage( 'flow-topic-collapsed-full' )->escaped(); ?>
	<li class="topic-collapsed" data-collapse-class="topic-collapsed-full" title="<?php echo $msg; ?>">
		<?php echo $msg; ?>
	</li>
	<?php $msg = wfMessage( 'flow-topic-complete' )->escaped(); ?>
	<li class="topic-complete active" title="<?php echo $msg; ?>"><?php echo $msg; ?></li>
</ul>

<div class="flow-topics">
<?php
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
	echo $topic->render( $this, array( 'topiclist-block' => $block ), true );
}

if ( $page && $page->getPagingLink( 'fwd' ) ) {
	echo $this->buildPagingLinkHtml( $block, $page, 'fwd' );
}
?>
</div>
>>>>>>> BRANCH (d50ded Merge "Repair non-js post editing")
