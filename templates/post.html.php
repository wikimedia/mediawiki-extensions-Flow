<?php

$replyForm = '';
/*
 * Only display reply form if:
 * * new reply depth will be no more than maxThreadingDepth
 * * user has sufficient permissions
 */
if ( $post->getDepth() <= $maxThreadingDepth - 1 && $postView->actions()->isAllowed( 'reply' ) ) {
	$replyForm = Html::openElement( 'div', array( 'class' => 'flow-post-reply-container' ) );

	$replyForm .= '<span class="flow-creator">' .
		$this->userToolLinks( $user->getId(), $user->getName() ) .
		'</span>';

	$replyForm .= Html::openElement( 'form', array(
			'method' => 'POST',
			// root post id is same as topic workflow id
			'action' => $this->generateUrl( $block->getWorkflowId(), 'reply' ),
			'class' => 'flow-reply-form flow-element-container',
		) );
	$replyForm .= Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

	if ( $block->getAlphadecimalRepliedTo() === $post->getPostId()->getAlphadecimal() ) {
		$replyForm .= '<ul>';
		foreach ( $block->getErrors() as $error ) {
			$replyForm .= '<li>' . $block->getErrorMessage( $error )->parse() . '</li>';
		}
		$replyForm .= '</ul>';
	}

	$placeHolder = $postView->replyPlaceholder();
	$replyForm .=
		Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . '_replyTo',
			'value' => $post->getPostId()->getAlphadecimal(),
		) ) .
		Html::textarea( $block->getName() . '_content', '', array(
			'placeholder' => $placeHolder,
			'title' => $placeHolder,
			'class' => 'flow-reply-content mw-ui-input',
			'rows' => '10',
		) ) .
		// NOTE: cancel button will be added via JS, makes no sense in non-JS context
		Html::openElement( 'div', array( 'class' => 'flow-form-controls' ) ) .
			Html::rawElement( 'div', array(
				'class' => 'flow-terms-of-use plainlinks',
			), wfMessage( 'flow-terms-of-use-reply' )->parse() ) .
			Html::element( 'input', array(
				'type' => 'submit',
				'value' => $postView->replySubmit(),
				'class' => 'mw-ui-button mw-ui-constructive flow-reply-submit',
			) ) .
			Html::element( 'div', array( 'class' => 'clear' ) ) .
		Html::closeElement( 'div' ) .
		Html::closeElement( 'form' ) .
		Html::closeElement( 'div' );
}

$containerClass = 'flow-post-container';

if ( $post->getDepth() >= $maxThreadingDepth ) {
	$containerClass .= ' flow-post-max-depth';
}

echo Html::openElement( 'div', array(
	'class' => $containerClass,
	'data-revision-id' => $post->getRevisionId()->getAlphadecimal(),
	'data-post-id' => $post->getPostId()->getAlphadecimal(),
	'data-creator-name' => $postView->creator(),
) );

$postClass = 'flow-post flow-element-container';

if ( $postView->creator() === $user->getName() ) {
	$postClass .= ' flow-post-own';
}

if ( $post->isModerated() ) {
	$postClass .= ' flow-post-moderated';
} else {
	$postClass .= ' flow-post-unmoderated';
}
?>
	<div id="flow-post-<?php echo $post->getPostId()->getAlphadecimal()?>" class='<?php echo $postClass; ?>' >
		<?php
		if ( $post->isModerated() ):
			$moderationState = htmlspecialchars( $post->getModerationState() );
			$allowed = $postView->actions()->isAllowed( 'view' ) ? 'allowed' : 'disallowed';
		?>
			<p class="flow-post-moderated-message flow-post-moderated-<?php echo $moderationState; ?> flow-post-content-<?php echo $allowed; ?>">
				<span class="flow-post-moderated-view"></span>
			</p>
			<div class="flow-post-moderated-show-message">
				<?php echo wfMessage( 'flow-post-moderated-toggle-' . $moderationState . '-show', $moderatedByUser )->rawParams( $userLink )->escaped(); ?>
			</div>
			<div class="flow-post-moderated-hide-message">
				<?php echo wfMessage( 'flow-post-moderated-toggle-' . $moderationState . '-hide', $moderatedByUser )->rawParams( $userLink )->escaped(); ?>
			</div>
		<?php
		endif;

		if ( $postView->actions()->isAllowedAny( 'hide-post', 'delete-post', 'suppress-post', 'restore-post', 'view', 'post-history' ) ): ?>
			<div class="flow-tipsy flow-actions">
				<a class="flow-tipsy-link" href="#" title="<?php echo wfMessage( 'flow-post-actions' )->escaped(); ?>"><?php echo wfMessage( 'flow-post-actions' )->escaped(); ?></a>
				<div class="flow-tipsy-flyout">
					<ul>
						<?php
						// Permanent link
						$viewButton = $postActionMenu->getButton(
							'view',
							wfMessage( 'flow-post-action-view' )->escaped(),
							'mw-ui-button flow-action-permalink-link',
							// This URL fragment triggers highlightPost behavior in front-end JS.
							'flow-post-' . $post->getPostId()->getAlphadecimal()
						);
						if ( $viewButton ) {
							echo '<li class="flow-action-permalink">', $viewButton, '</li>';
						}
						$hidePost = $postView->hidePostButton( 'flow-hide-post-link mw-ui-button' );
						if ( $hidePost ) {
							echo '<li class="flow-action-hide">' . $hidePost . '</li>';
						}
						// History link
						if ( $post->getPrevRevisionId() ) {
							$historyButton =  $postActionMenu->getButton(
								'post-history',
								wfMessage( 'flow-post-action-post-history' )->escaped(),
								'mw-ui-button flow-action-post-history-link'
							);
							if ( $historyButton ) {
								echo '<li class="flow-action-post-history">', $historyButton, '</li>';
							}
						}
						$deletePost = $postView->deletePostButton( 'flow-delete-post-link mw-ui-button' );
						if ( $deletePost ) {
							echo '<li class="flow-action-delete">' . $deletePost . '</li>';
						}
						$suppressPost = $postView->suppressPostButton( 'flow-suppress-post-link mw-ui-button' );
						if ( $suppressPost ) {
							echo '<li class="flow-action-suppress">' . $suppressPost . '</li>';
						}
						// @todo restore button will probably be moved somewhere else, some day
						$restorePost = $postView->restorePostButton( 'flow-restore-post-link mw-ui-button mw-ui-constructive' );
						if ( $restorePost ) {
							echo '<li class="flow-action-restore">' . $restorePost . '</li>';
						}
						?>
					</ul>
				</div>
			</div>
		<?php
		endif;
		?>
		<div class="flow-post-main">
			<div class="flow-post-title">
				<span class="flow-creator">
					<?php echo $postView->creatorToolLinks() ?>
				</span>
			</div>

			<div class="flow-post-content ui-helper-clearfix">
				<?php echo $this->getContent( $post, 'html' ), $postView->createModifiedTipsyLink( $block ), $postView->createModifiedTipsyHtml( $block ); ?>
			</div>
			<?php
				echo $this->render( 'flow:timestamp.html.php', array(
					'timestamp' => $post->getPostId()->getTimestampObj(),
				), true );
			?>
			<div class="flow-post-interaction">
				<?php echo $postView->postInteractionLinks( 'flow-reply-link mw-ui-button', 'flow-edit-post-link mw-ui-button' ); ?>
			</div>
		</div>
	</div>

	<div class="flow-post-replies">
		<?php
		foreach( $post->getChildren() as $child ) {
			echo $this->renderPost( $child, $block );
		}
		?>
	<?php echo $replyForm; ?>
	</div>

</div>
