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
?>
				<div class="flow-post-actionbox">
					<ul>
						<?php
						foreach( $actions as $key => $action ) {
							echo '<li class="flow-action-'.$key.'">' . $action . "</li>\n";
						}
						?>
					</ul>
				</div>
