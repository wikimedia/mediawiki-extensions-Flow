<?php
// yes, this is a horrible quick hack
// probably be better off if the templates were classes that were called as
//     $template->render( $options );
// or some such

$self = $this;
$editToken = $user->getEditToken( 'flow' );
$postAction = function( $action, array $data = array() ) use( $self, $block, $root, $editToken ) {
	// actions that change things must be post requests
	// also, CSRF?
	echo '<li>' . Html::openElement( 'form', array(
		'method' => 'POST',
		'action' => $self->generateUrl( $block->getWorkflowId(), $action )
	) );
	echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );
	foreach ( $data as $name => $value ) {
		echo Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . "[$name]",
			'value' => $value,
		) );
	}
	echo Html::element( 'input', array(
		'type' => 'submit',
		// flow-post-action-delete-post
		// flow-post-action-history
		'value' => wfMessage( "flow-post-action-$action" )->plain(),
	) ) . '</form></li>';
};

$renderPost = function( $post ) use( $self, $block, $root, $postAction, &$renderPost, $editToken ) {
	echo '<div style="padding-left: 20px">';
	if ( $post->isFlagged( 'deleted' ) ) {
		echo wfMessage( 'flow-post-deleted' )
			. '<ul>';
		$postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ) );
		echo '<li>' . Html::element( 'a', array(
			'href' => $self->generateUrl( $block->getWorkflowId(), 'post-history', array(
				$block->getName() . '[postId]' => $post->getPostId()->getHex(),
			) ),
		), wfMessage( 'flow-post-action-history' )->plain() ) . '</li>';

		echo '</ul>';
	} else {
		echo wfMessage( 'flow-user' ) . Html::element( 'span', null, $post->getUserText() )
			. wfMessage( 'flow-post-id' ) . $post->getPostId()->getHex()
			. wfMessage( 'flow-content' ) . $post->getContent()
			. '<ul>';
		$postAction( 'delete-post', array( 'postId' => $post->getPostId()->getHex() ) );
		echo '<li>' . Html::element( 'a', array(
			'href' => $self->generateUrl( $block->getWorkflowId(), 'post-history', array(
				$block->getName() . '[postId]' => $post->getPostId()->getHex(),
			) ),
		), wfMessage( 'flow-post-action-history' )->plain() ) . '<li>';
		echo '</ul>'
			. Html::openElement( 'form', array(
				'method' => 'POST',
				// root post id is same as topic workflow id
				'action' => $self->generateUrl( $block->getWorkflowId(), 'reply' ),
			) );
			echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );
		if ( $block->getHexRepliedTo() === $post->getPostId()->getHex() ) {
			foreach ( $block->getErrors() as $error ) {
				echo $error->text() . '<br>'; // the pain ...
			}
		}
		echo Html::element( 'input', array(
				'type' => 'hidden',
				'name' => $block->getName() . '[replyTo]',
				'value' => $post->getPostId()->getHex(),
			) )
			. Html::textarea( $block->getName() . '[content]' )
			. Html::element( 'input', array(
				'type' => 'submit',
				'value' => wfMessage( 'flow-reply-to-post' )->plain()
			) )
			. '</form>';
	}
	foreach( $post->getChildren() as $child ) {
		$renderPost( $child );
	}
	echo '</div>';
};

if ( $root->isTopicTitle() ) {
	echo Html::element( 'h4', array(), $root->getContent() ),
		Html::rawElement( 'a', array(
			'href' => $this->generateUrl( $block->getWorkflowId(), 'edit-title' )
		), wfMessage( 'flow-action-edit-title' ) );

	foreach( $root->getChildren() as $child ) {
		$renderPost( $child );
	}
} else {
	$renderPost( $root );
}
