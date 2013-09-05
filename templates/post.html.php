<?php

$editToken = $user->getEditToken( 'flow' );
$self = $this;

$postAction = function( $action, array $data = array(), $class = '' ) use( $self, $block, $editToken ) {
	// actions that change things must be post requests
	$output = '';
	$output .= Html::openElement( 'form', array(
		'method' => 'POST',
		'action' => $self->generateUrl( $block->getWorkflowId(), $action )
	) );
	$output .= Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );
	foreach ( $data as $name => $value ) {
		$output .= Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . "[$name]",
			'value' => $value,
		) );
	}
	// Give grep a chance to find the usages:
	// flow-post-action-censor-post, flow-post-action-delete-post, flow-post-action-hide-post,
	// flow-post-action-restore-post
	$output .= Html::element( 'input', array(
		'type' => 'submit',
		'class' => 'mw-ui-button '.$class,
		'value' => wfMessage( "flow-post-action-$action" )->plain(),
	) ) . '</form>';

	return $output;
};

$getAction = function( $action, $data = array(), $class = '' ) use ( $post, $self, $block ) {
	$url = $self->generateUrl(
		$block->getWorkflowId(),
		$action,
		array(
			$block->getName() . '[postId]' => $post->getPostId()->getHex(),
		)
	);
	// Give grep a chance to find the usages:
	// flow-post-action-view, flow-post-action-post-history, flow-post-action-edit-post
	return Html::element( 'a',
		array(
			'href' => $url,
			'class' => $class,
		),
		wfMessage( "flow-post-action-$action" )->plain()
	);
};

$createReplyForm = function() use( $self, $block, $post, $editToken ) {
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
				$post->getOrigUserText() )->text(),
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

$class = $post->isModerated() ? 'flow-post-moderated' : 'flow-post';
$content = $post->getContent();
$userText = $post->getUserText();
if ( !$userText instanceof \Message ) {
	$userText = Html::element( 'span', null, $userText );
}
$actions = array();
$replyForm = '';

// Build the actions for the post
switch( $post->getModerationState() ) {
case $post::MODERATED_NONE:
	if ( $user->isAllowed( 'flow-hide' ) ) {
		$actions['hide'] = $postAction( 'hide-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-destructive' );
	}
	if ( $user->isAllowed( 'flow-delete' ) ) {
		$actions['delete'] = $postAction( 'delete-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-destructive' );
	}
	if ( $user->isAllowed( 'flow-censor' ) ) {
		$actions['censor'] = $postAction( 'censor-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-destructive' );
	}
	$actions['history'] = $getAction( 'post-history' );
	$actions['edit-post'] = $getAction( 'edit-post' );
	$replyForm = $createReplyForm();
	break;

case $post::MODERATED_HIDDEN:
	if ( $user->isAllowed( 'flow-hide' ) ) {
		$actions['restore'] = $postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-constructive' );
	}
	if ( $user->isAllowed( 'flow-delete' ) ) {
		$actions['delete'] = $postAction( 'delete-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-destructive' );
	}
	if ( $user->isAllowed( 'flow-censor' ) ) {
		$actions['censor'] = $postAction( 'censor-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-destructive' );
	}
	$actions['history'] = $getAction( 'post-history' );
	break;

case $post::MODERATED_DELETED:
	if ( $user->isAllowedAny( 'flow-delete', 'flow-censor' ) ) {
		$actions['restore'] = $postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-constructive' );
	}
	$actions['history'] = $getAction( 'post-history' );
	break;

case $post::MODERATED_CENSORED:
	if ( !$user->isAllowed( 'flow-censor' ) ) {
		// no children, no nothing
		return;
	}
	$actions['restore'] = $postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-constructive' );
	$actions['history'] = $getAction( 'post-history' );
	break;
}

// Default always-available actions
$actions['permalink'] = $getAction( 'view' );

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
		<div class="flow-post-title">
			<div class="flow-post-authorline">
				<?php echo $userText; ?>
				<span class="flow-datestamp">
					<span class="flow-agotime" style="display: inline">
						<?php echo $self->timeAgo( $post->getPostId() ); ?>
					</span>
					<span class="flow-utctime" style="display: none">
						<?php echo $post->getPostId()->getTimestampObj()->getTimestamp( TS_RFC2822 ); ?>
					</span>
				</span>
			</div>
		</div>
		<div class="flow-post-content">
			<?php echo $content ?>
		</div>
		<div class="flow-post-controls">
			<div class="flow-post-actions">
				<a><?php echo wfMessage( 'flow-post-actions' )->escaped(); ?></a>
				<div class="flow-actionbox-pokey">&nbsp;</div>
				<div class="flow-post-actionbox">
					<ul>
						<?php
						foreach( $actions as $key => $action ) {
							echo '<li class="flow-action-'.$key.'">' . $action . "</li>\n";
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
