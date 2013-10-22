<?php

$replyForm = '';
if ( !$post->isModerated() ) {
	$replyForm = Html::openElement( 'div', array( 'class' => 'flow-post-reply-container' ) );

	$replyForm .= '
		<span class="flow-creator">
			<span class="flow-creator-simple" style="display: inline">
				' .$this->getUserText( $user ) . '
			</span>
			<span class="flow-creator-full" style="display: none">
				' . $this->userToolLinks( $user->getId(), $user->getName() ) .'
			</span>
		</span>';

	$replyForm .= Html::openElement( 'form', array(
			'method' => 'POST',
			// root post id is same as topic workflow id
			'action' => $this->generateUrl( $block->getWorkflowId(), 'reply' ),
			'class' => 'flow-reply-form flow-element-container',
		) );
	$replyForm .= Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

	if ( $block->getHexRepliedTo() === $post->getPostId()->getHex() ) {
		foreach ( $block->getErrors() as $error ) {
			$replyForm .= $error->text() . '<br>'; // the pain ...
		}
	}

	$placeHolder = $postView->replyPlaceholder( $post );
	$replyForm .=
		Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . '[replyTo]',
			'value' => $post->getPostId()->getHex(),
		) ) .
		Html::element( 'input', array(
			'type' => 'hidden',
			'name' => 'placeholder',
			'value' => $placeHolder,
		) ) .
		Html::textarea( $block->getName() . '[content]', '', array(
			'placeholder' => $placeHolder,
			'title' => $placeHolder,
			'class' => 'flow-reply-content flow-input mw-ui-input',
		) ) .
		// NOTE: cancel button will be added via JS, makes no sense in non-JS context

		Html::openElement( 'div', array( 'class' => 'flow-post-form-controls' ) ) .
			Html::element( 'input', array(
				'type' => 'submit',
				'value' => $postView->replySubmit( $post ),
				'class' => 'mw-ui-button mw-ui-constructive flow-reply-submit',
			) ) .
		Html::closeElement( 'div' ) .
		Html::closeElement( 'form' ) .
		Html::closeElement( 'div' );
}

?>
<div class='flow-post-container'
	data-revision-id='<?php echo $post->getRevisionId()->getHex() ?>'
	data-post-id='<?php echo $post->getPostId()->getHex() ?>'
	data-creator-name='<?php echo $post->getCreatorName() ?>'>
	<div id="flow-post-<?php echo $post->getPostId()->getHex()?>" class='flow-post flow-element-container <?php echo $post->isModerated() ? 'flow-post-moderated' : 'flow-post-unmoderated' ?>' >
		<?php if ( $post->isModerated() ): ?>
			<p class="flow-post-moderated-message flow-post-moderated-<?php echo $post->getModerationState(); ?> flow-post-content-<?php echo $post->isAllowed( $user ) ? 'allowed' : 'disallowed'; ?>">
			<?php echo $post->getModeratedContent()->parse() ?>
		</p>
		<?php endif; ?>

		<div class="flow-post-main">
			<div class="flow-post-title">
				<span class="flow-creator">
					<span class="flow-creator-simple" style="display: inline">
						<?php echo $postView->creator( $post ) ?>
					</span>
					<span class="flow-creator-full" style="display: none">
						<?php echo $postView->creatorToolLinks( $post ) ?>
					</span>
				</span>
			</div>

			<div class="flow-post-content">
				<?php echo $post->getContent( $user, 'html' ); ?>
			</div>

			<?php echo $postView->editPostButton( $post, 'flow-edit-post-link flow-icon flow-icon-bottom-aligned' ); ?>

			<p class="flow-datestamp">
				<?php
					// timestamp html
					$content = '
						<span class="flow-agotime" style="display: inline">' . htmlspecialchars( $post->getPostId()->getHumanTimestamp() ) . '</span>
						<span class="flow-utctime" style="display: none">' . htmlspecialchars( $post->getPostId()->getTimestampObj()->getTimestamp( TS_RFC2822 ) ) . '</span>';

					// build history button with timestamp html as content
					echo $postView->postHistoryButton( $post, $content );
				?>
			</p>

			<div class="flow-post-interaction">
				<?php if ( !$post->isModerated() ): ?>
					<a class="flow-reply-link mw-ui-button" href="#"><span><?php echo $postView->replyLink( $post ); ?></span></a>
					<a class="flow-thank-link mw-ui-button" href="#" onclick="return mw.flow.notImplemented()">
						<span><?php echo $postView->thankLink( $post ); ?></span>
					</a>
				<?php else: ?>
					<?php list( $talkUrl, $talkLink ) = $postView->moderatedTalkLink( $post ); ?>
					<a class="flow-talk-link mw-ui-button" href="<?php echo $talkUrl; ?>">
						<span><?php echo $talkLink; ?></span>
					</a>
				<?php endif; ?>
			</div>
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

		<?php if ( $postView->actions()->isAllowedAny( 'hide-post', 'delete-post', 'censor-post', 'restore-post' ) ): ?>
		<div class="flow-actions">
			<a class="flow-actions-link flow-icon flow-icon-bottom-aligned" href="#" title="<?php echo wfMessage( 'flow-post-actions' )->escaped(); ?>"><?php echo wfMessage( 'flow-post-actions' )->escaped(); ?></a>
			<div class="flow-actions-flyout">
				<ul>
					<?php
					if ( $hidePost = $postView->hidePostButton( $post, 'flow-hide-post-link mw-ui-button' ) ) {
						echo "<li class='flow-action-hide'>$hidePost</li>";
					}
					if ( $deletePost = $postView->deletePostButton( $post, 'flow-delete-post-link mw-ui-button' ) ) {
						echo "<li class='flow-action-delete'>$deletePost</li>";
					}
					if ( $suppressPost = $postView->suppressPostButton( $post, 'flow-censor-post-link mw-ui-button' ) ) {
						echo "<li class='flow-action-censor'>$suppressPost</li>";
					}
					// @todo restore button will probably be moved somewhere else, some day
					if ( $restorePost = $postView->restorePostButton( $post, 'flow-restore-post-link mw-ui-button mw-ui-constructive' ) ) {
						echo "<li class='flow-action-restore'>$restorePost</li>";
					}
					?>
				</ul>
			</div>
		</div>
		<?php endif; ?>

	</div>

	<div class='flow-post-replies'>
		<?php
		foreach( $post->getChildren() as $child ) {
			echo $this->renderPost( $child, $block );
		}
		?>
	</div>

	<?php echo $replyForm; ?>
</div>
