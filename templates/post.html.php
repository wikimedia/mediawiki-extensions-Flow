<?php

$self = $this;

$createReplyForm = function() use( $self, $block, $post, $editToken, $user ) {
	$replyForm = Html::openElement( 'form', array(
			'method' => 'POST',
			// root post id is same as topic workflow id
			'action' => $self->generateUrl( $block->getWorkflowId(), 'reply' ),
			'class' => 'flow-reply-form',
		) );
	$replyForm .= Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

	if ( $block->getHexRepliedTo() === $post->getPostId()->getHex() ) {
		foreach ( $block->getErrors() as $error ) {
			$replyForm .= $error->text() . '<br>'; // the pain ...
		}
	}
	return $replyForm .
		Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . '[replyTo]',
			'value' => $post->getPostId()->getHex(),
		) ) .
		Html::textarea( $block->getName() . '[content]', '', array(
			'placeholder' => wfMessage( 'flow-reply-placeholder',
				$post->getCreatorName( $user ) )->text(),
			'class' => 'flow-reply-content flow-input',
		) ) .
		Html::openElement( 'div', array( 'class' => 'flow-post-form-extras' ) ) .
		Html::openElement( 'div', array( 'class' => 'flow-post-form-controls' ) ) .
		Html::element( 'input', array(
			'type' => 'submit',
			'value' => wfMessage( 'flow-reply-submit' )->plain(),
			'class' => 'mw-ui-button mw-ui-primary flow-reply-submit',
		) ) .
		Html::closeElement( 'div' ) .
		Html::element( 'div', array(
			'class' => 'flow-disclaimer',
		), wfMessage( 'flow-disclaimer' )->parse() ) .
		Html::closeElement( 'div' ) .
		'</form>';
};

$actions = array();
$replyForm = '';

if ( $post->getModerationState() == $post::MODERATED_NONE ) {
	$replyForm = $createReplyForm();
}

// The actual output
echo Html::openElement( 'div', array(
	'class' => 'flow-post-container',
	'data-post-id' => $post->getRevisionId()->getHex(),
) );
	echo Html::openElement( 'div', array(
		'class' => $post->isModerated() ? 'flow-post-moderated' : 'flow-post',
		'data-post-id' => $post->getPostId()->getHex(),
		'id' => 'flow-post-' . $post->getPostId()->getHex(),
	) ); ?>
		<div class="flow-post-title">
			<div class="flow-post-authorline">
				<span class="flow-creator">
					<span class="flow-creator-simple" style="display: inline">
						<?php echo $post->getCreatorName( $user ); ?>
					</span>
					<span class="flow-creator-full" style="display: none">
						<?php echo $this->userToolLinks( $post->getCreatorId(), $post->getCreatorName() ); ?>
					</span>
				</span>
				<span class="flow-datestamp">
					<span class="flow-agotime" style="display: inline">
						<?php echo $post->getPostId()->getHumanTimestamp(); ?>
					</span>
					<span class="flow-utctime" style="display: none">
						<?php echo $post->getPostId()->getTimestampObj()->getTimestamp( TS_RFC2822 ); ?>
					</span>
				</span>
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
		</div>
		<div class="flow-post-content">
			<?php echo $post->getContent( $user, 'html' ); ?>
		</div>
		<?php if ( $post->hasHiddenContent() ): /* getHiddenContent returns blank if not hidden, could always render it? */ ?>
			<div class="flow-post-content-hidden" style="display: none">
				<?php echo $post->getHiddenContent( 'html' ); ?>
			</div>
		<?php endif ?>
		<div class="flow-post-controls">
			<div class="flow-post-actions">
				<a><?php echo wfMessage( 'flow-post-actions' )->escaped(); ?></a>
				<div class="flow-actionbox-pokey">&nbsp;</div>
				<div class="flow-post-actionbox">
					<ul>
						<?php
						foreach( $postActionMenu->get( $user, $block, $post, $editToken ) as $key => $action ) {
							echo "<li class=\"flow-action-$key\">$action</li>";
						}
						?>
					</ul>
				</div>
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
