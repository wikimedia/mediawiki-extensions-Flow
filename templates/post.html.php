<?php

$replyForm = '';
if ( !$post->isModerated() ) {
	$replyForm = Html::openElement( 'form', array(
			'method' => 'POST',
			// root post id is same as topic workflow id
			'action' => $this->generateUrl( $block->getWorkflowId(), 'reply' ),
			'class' => 'flow-reply-form',
		) );
	$replyForm .= Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

	if ( $block->getHexRepliedTo() === $post->getPostId()->getHex() ) {
		foreach ( $block->getErrors() as $error ) {
			$replyForm .= $error->text() . '<br>'; // the pain ...
		}
	}
	$replyForm .=
		Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . '[replyTo]',
			'value' => $post->getPostId()->getHex(),
		) ) .
		Html::textarea( $block->getName() . '[content]', '', array(
			'placeholder' => wfMessage( 'flow-reply-placeholder',
				$post->getCreatorName( $user ) )->text(),
			'class' => 'flow-reply-content flow-input mw-ui-input',
		) ) .
		// NOTE: cancel button will be added via JS, makes no sense in non-JS context

		Html::openElement( 'div', array( 'class' => 'flow-post-form-controls' ) ) .
			Html::element( 'input', array(
				'type' => 'submit',
				'value' => wfMessage( 'flow-reply-submit', $post->getCreatorName( $user ) )->text(),
				'class' => 'mw-ui-button mw-ui-constructive flow-reply-submit',
			) ) .
		Html::closeElement( 'div' ) .
		Html::closeElement( 'form' );
}


// The actual output
echo Html::openElement( 'div', array(
	'class' => 'flow-post-container',
	'data-post-id' => $post->getRevisionId()->getHex(),
) );
	echo Html::openElement( 'div', array(
		'class' => $post->isModerated() ? 'flow-post flow-post-moderated' : 'flow-post',
		'data-post-id' => $post->getPostId()->getHex(),
		'id' => 'flow-post-' . $post->getPostId()->getHex(),
	) ); ?>

		<?php if ( $post->isModerated() ): ?>
			<p class="flow-post-moderated-message flow-post-moderated-<?php echo $post->getModerationState(); ?> flow-post-content-<?php echo $post->isAllowed( $user ) ? 'allowed' : 'disallowed'; ?>">
			<?php
				// passing in null as user (unprivileged) will get the "hidden/deleted/suppressed by XYZ" text
				echo $post->getContent( null );
			?>
		</p>
		<?php endif; ?>

		<div class="flow-post-main">
			<div class="flow-post-title">
				<span class="flow-creator">
					<span class="flow-creator-simple" style="display: inline">
						<?php echo $post->getCreatorName( $user ); ?>
					</span>
					<span class="flow-creator-full" style="display: none">
						<?php echo $this->userToolLinks( $post->getCreatorId(), $post->getCreatorName() ); ?>
					</span>
				</span>
					</div>

			<div class="flow-post-content">
				<?php echo $post->getContent( $user, 'html' ); ?>
			</div>
			<?php if ( $postActionMenu->isAllowed( 'edit-post' ) ) {
				echo $postActionMenu->getButton( 'edit-post', wfMessage( 'flow-post-action-edit-post' )->plain(), 'flow-edit-post-link flow-icon flow-icon-bottom-aligned' );
			}
			?>

			<p class="flow-datestamp">
				<?php
					// timestamp html
					$content = '
						<span class="flow-agotime" style="display: inline">'. $post->getPostId()->getHumanTimestamp() .'</span>
						<span class="flow-utctime" style="display: none">'. $post->getPostId()->getTimestampObj()->getTimestamp( TS_RFC2822 ) .'</span>';

					// build history button with timestamp html as content
					if ( $postActionMenu->isAllowed( 'post-history' ) ) {
						echo $postActionMenu->getButton( 'post-history', $content, 'flow-action-history-link' );
					} else {
						echo $content;
					}
				?>
			</p>

			<div class="flow-post-interaction">
				<?php if ( !$post->isModerated() ): ?>
					<a class="flow-reply-link mw-ui-button" href="#"><span><?php echo wfMessage( 'flow-reply-link', $post->getCreatorName( $user ) )->escaped(); ?></span></a>
					<a class="flow-thank-link mw-ui-button" href="#" onclick="alert( '@todo: Not yet implemented!' ); return false;"><span><?php echo wfMessage( 'flow-thank-link', $post->getCreatorName( $user ) )->escaped(); ?></span></a>
				<?php else: ?>
					<?php
						$user = User::newFromId( $post->getModeratedByUserId() );
						$title = $user->getTalkPage();
					?>
					<a class="flow-talk-link mw-ui-button" href="<?php echo $title->getLinkURL(); ?>">
						<span><?php echo wfMessage( 'flow-talk-link', $post->getModeratedByUserText() )->escaped(); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $postActionMenu->isAllowedAny( 'hide-post', 'delete-post', 'censor-post' ) ): ?>
		<div class="flow-actions">
			<a class="flow-actions-link flow-icon flow-icon-bottom-aligned" href="#"><?php echo wfMessage( 'flow-post-actions' )->escaped(); ?></a>
			<div class="flow-actions-flyout">
				<ul>
					<?php
					if ( $postActionMenu->isAllowed( 'hide-post' ) ) {
						echo '<li class="flow-action-hide">'. $postActionMenu->getButton( 'hide-post', wfMessage( 'flow-post-action-hide-post' )->plain(), 'flow-hide-post-link mw-ui-button mw-ui-destructive mw-ui-destructive-low' ) .'</li>';
					}
					if ( $postActionMenu->isAllowed( 'delete-post' ) ) {
						echo '<li class="flow-action-delete">'. $postActionMenu->getButton( 'delete-post', wfMessage( 'flow-post-action-delete-post' )->plain(), 'flow-delete-post-link mw-ui-button mw-ui-destructive mw-ui-destructive-medium' ) .'</li>';
					}
					if ( $postActionMenu->isAllowed( 'censor-post' ) ) {
						echo '<li class="flow-action-censor">'. $postActionMenu->getButton( 'censor-post', wfMessage( 'flow-post-action-censor-post' )->plain(), 'flow-censor-post-link mw-ui-button mw-ui-destructive' ) .'</li>';
					}
					?>
				</ul>
			</div>
		</div>
		<?php endif; ?>

	</div>

	<?php echo $replyForm; ?>
	<div class='flow-post-replies'>
		<?php
		foreach( $post->getChildren() as $child ) {
			echo $this->renderPost( $child, $block );
		}
		?>
	</div>
</div>
