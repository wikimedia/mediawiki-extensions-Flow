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
				'value' => wfMessage( 'flow-reply-submit', $post->getCreatorName() )->plain(),
				'class' => 'mw-ui-button mw-ui-constructive flow-reply-submit',
			) ) .
		Html::closeElement( 'div' ) .
		Html::closeElement( 'form' );
}

$class = $post->isModerated() ? 'flow-post-moderated' : 'flow-post';
$actions = array();


// The actual output
echo Html::openElement( 'div', array(
	'class' => 'flow-post-container',
	'data-post-id' => $post->getRevisionId()->getHex(),
) );
	echo Html::openElement( 'div', array(
		'class' => $class,
		'data-post-id' => $post->getPostId()->getHex(),
		'id' => 'flow-post-' . $post->getPostId()->getHex(),
	) ); ?>

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

			<?php if ( !$post->isOriginalContent() ): ?>
				<div class="flow-post-edited">
					<?php echo wfMessage(
						'flow-post-edited',
						$post->getLastContentEditorName( $user ),
						$post->getLastContentEditId()->getHumanTimestamp()
					); ?>
				</div>
			<?php endif ?>

			<p class="flow-datestamp">
				<span class="flow-agotime" style="display: inline">
					<?php echo $post->getPostId()->getHumanTimestamp(); ?>
				</span>
				<span class="flow-utctime" style="display: none">
					<?php echo $post->getPostId()->getTimestampObj()->getTimestamp( TS_RFC2822 ); ?>
				</span>
			</p>

			<?php if ( !$post->isModerated() ): ?>
				<div class="flow-post-interaction">
					<a class="flow-reply-link mw-ui-button" href="#"><span><?php echo wfMessage( 'flow-reply-link', $post->getCreatorName() )->escaped(); ?></span></a>
					<a class="flow-thank-link mw-ui-button" href="#" onclick="alert( '@todo: Not yet implemented!' ); return false;"><span><?php echo wfMessage( 'flow-thank-link', $post->getCreatorName() )->escaped(); ?></span></a>
				</div>
			<?php endif; ?>
		</div>

		<div class="flow-actions">
			<a class="flow-actions-link flow-icon flow-icon-bottom-aligned" href="#"><?php echo wfMessage( 'flow-post-actions' )->escaped(); ?></a>
			<div class="flow-actions-flyout">
				<ul>
					<?php
					foreach( $postActionMenu->get( $user, $block, $post, $editToken ) as $key => $action ) {
						// @todo: $actions currently includes a lot of actions, design only wants censor actions here; figure out where others belong
						echo "<li class=\"flow-action-$key\">$action</li>";
					}
					?>
				</ul>
			</div>
		</div>

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
