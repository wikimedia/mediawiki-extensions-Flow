<?php

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
	// Messages: flow-post-action-censor-post, flow-post-action-delete-post,
	// flow-post-action-hide-post, flow-post-action-restore-post
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
	// Messages: flow-post-action-view, flow-post-action-post-history, flow-post-action-edit-post
	return Html::element( 'a',
		array(
			'href' => $url,
			'class' => $class,
		),
		wfMessage( "flow-post-action-$action" )->plain()
	);
};

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

$class = $post->isModerated() ? 'flow-post-moderated' : 'flow-post';
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
				<span class="flow-creator">
					<span class="flow-creator-simple" style="display: inline">
						<?php echo $post->getCreatorName( $user ); ?>
					</span>
					<span class="flow-creator-full" style="display: none">
						<?php echo $this->userToolLinks( $post->getCreatorId(), $post->getCreatorName() ); ?>
					</span>
				</span>
				<p class="flow-datestamp">
					<span class="flow-agotime" style="display: inline">
						<?php echo $post->getPostId()->getHumanTimestamp(); ?>
					</span>
					<span class="flow-utctime" style="display: none">
						<?php echo $post->getPostId()->getTimestampObj()->getTimestamp( TS_RFC2822 ); ?>
					</span>
				</p>
			</div>
		</div>
		<div class="flow-post-content">
			<?php echo $post->getContent( $user, 'html' ); ?>
		</div>
		<div class="flow-post-controls">
			<!-- @todo: these are currently barely visible because of CSS changes for topic-actions, these will need to change too though, don't forget -->
			<a class="flow-topic-actions-link" href="#"><?php echo wfMessage( 'flow-post-actions' )->escaped(); ?></a>
			<div class="flow-post-actions">
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
	<?php echo $replyForm; ?>
	<div class='flow-post-replies'>
		<?php
		foreach( $post->getChildren() as $child ) {
			echo $this->renderPost( $child, $block );
		}
		?>
	</div>
</div>
