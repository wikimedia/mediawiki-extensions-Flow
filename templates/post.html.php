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

	if ( $block->getHexRepliedTo() === $post->getPostId()->getHex() ) {
		$replyForm .= '<ul>';
		foreach ( $block->getErrors() as $error ) {
			$replyForm .= '<li>' . $block->getErrorMessage( $error )->escaped() . '</li>';
		}
		$replyForm .= '</ul>';
	}

	$placeHolder = $postView->replyPlaceholder();
	$replyForm .=
		Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . '[replyTo]',
			'value' => $post->getPostId()->getHex(),
		) ) .
		Html::textarea( $block->getName() . '[content]', '', array(
			'placeholder' => $placeHolder,
			'title' => $placeHolder,
			'class' => 'flow-reply-content mw-ui-input',
			'rows' => '10',
		) ) .
		// NOTE: cancel button will be added via JS, makes no sense in non-JS context

		Html::openElement( 'div', array( 'class' => 'flow-post-form-controls' ) ) .
			Html::element( 'input', array(
				'type' => 'submit',
				'value' => $postView->replySubmit(),
				'class' => 'mw-ui-button mw-ui-constructive flow-reply-submit',
			) ) .
		Html::closeElement( 'div' ) .
		Html::closeElement( 'form' ) .
		Html::closeElement( 'div' );
}

echo Html::openElement( 'div', array(
	'class' => 'flow-post-container ' . ( $post->getDepth() >= $maxThreadingDepth ? 'flow-post-max-depth' : '' ),
	'data-revision-id' => $post->getRevisionId()->getHex(),
	'data-post-id' => $post->getPostId()->getHex(),
	'data-creator-name' => $post->getCreatorName()
) );
?>
	<div id="flow-post-<?php echo $post->getPostId()->getHex()?>" class='flow-post flow-element-container <?php echo $post->isModerated() ? 'flow-post-moderated' : 'flow-post-unmoderated' ?>' >
		<?php
		if ( $post->isModerated() ):
			$moderationState = $post->getModerationState();
			$allowed = $postView->actions()->isAllowed( 'view' ) ? 'allowed' : 'disallowed';
			echo Html::rawElement(
				'p',
				array( 'class' => "flow-post-moderated-message flow-post-moderated-$moderationState flow-post-content-$allowed", ),
				// Passing null user will return the 'moderated by Foo' content
				$this->getContent( $post, 'html' )
			);
		endif;
		?>

		<div class="flow-post-main">
			<div class="flow-post-title">
				<span class="flow-creator">
					<?php echo $postView->creatorToolLinks() ?>
				</span>
			</div>

			<?php echo $postView->editPostButton( 'flow-edit-post-link flow-icon flow-icon-bottom-aligned' ); ?>

			<div class="flow-post-content">
				<?php echo $this->getContent( $post, 'html', $user ); ?>
			</div>

			<?php if ( $postView->actions()->isAllowedAny( 'hide-post', 'delete-post', 'suppress-post', 'restore-post' ) ): ?>
				<div class="flow-tipsy flow-actions">
					<a class="flow-tipsy-link flow-icon flow-icon-bottom-aligned" href="#" title="<?php echo wfMessage( 'flow-post-actions' )->escaped(); ?>" data-tipsy-gravity="e"><?php echo wfMessage( 'flow-post-actions' )->escaped(); ?></a>
					<div class="flow-tipsy-flyout">
						<ul>
							<?php
							if ( $hidePost = $postView->hidePostButton( 'flow-hide-post-link mw-ui-button' ) ) {
								echo '<li class="flow-action-hide">' . $hidePost . '</li>';
							}
							if ( $deletePost = $postView->deletePostButton( 'flow-delete-post-link mw-ui-button' ) ) {
								echo '<li class="flow-action-delete">' . $deletePost . '</li>';
							}
							if ( $suppressPost = $postView->suppressPostButton( 'flow-suppress-post-link mw-ui-button' ) ) {
								echo '<li class="flow-action-suppress">' . $suppressPost . '</li>';
							}
							// @todo restore button will probably be moved somewhere else, some day
							if ( $restorePost = $postView->restorePostButton( 'flow-restore-post-link mw-ui-button mw-ui-constructive' ) ) {
								echo '<li class="flow-action-restore">' . $restorePost . '</li>';
							}
							?>
						</ul>
					</div>
				</div>
			<?php
				endif;

				$historyLink = $postView->postHistoryLink( $block->getName() );

				echo $this->render( 'flow:timestamp.html.php', array(
					'historicalLink' => $historyLink,
					'timestamp' => $post->getPostId()->getTimestampObj(),
				), true );
			?>
			<div class="flow-post-interaction">
				<?php if ( !$post->isModerated() && $postView->actions()->isAllowed( 'reply' ) ): ?>
					<a class="flow-reply-link mw-ui-button" href="#"><span><?php echo $postView->replyLink(); ?></span></a>
				<?php endif ?>
			</div>

			<?php
			echo Html::element(
				'a',
				array(
					'class' => 'flow-icon-permalink flow-icon flow-icon-bottom-aligned',
					'title' => wfMessage( 'flow-post-action-view' )->text(),
					'href' => $this->generateUrl( $block->getWorkflowId() ) . '#flow-post-' . $post->getPostId()->getHex(),
				),
				wfMessage( 'flow-topic-action-view' )->text()
			);
			?>
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
