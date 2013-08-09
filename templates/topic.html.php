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
	$output = '';
	$output .= Html::openElement( 'form', array(
		'method' => 'POST',
		'action' => $self->generateUrl( $root->getPostId(), $action )
	) );
	$output .= Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );
	foreach ( $data as $name => $value ) {
		$output .= Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . "[$name]",
			'value' => $value,
		) );
	}
	$output .= Html::element( 'input', array(
		'type' => 'submit',
		// flow-post-action-delete-post
		// flow-post-action-history
		'value' => wfMessage( "flow-post-action-$action" )->plain(),
	) ) . '</form>';

	return $output;
};

$renderPost = function( $post ) use( $self, $block, $root, $postAction, &$renderPost, $editToken ) {
	echo Html::openElement( 'div', array(
		'class' => 'flow-post-container',
	) );

	$class = 'flow-post';
	$actions = array();
	$replyForm = '';
	if ( $post->isFlagged( 'deleted' ) ) {
		$class .= ' flow-post-deleted';
	}

	echo Html::openElement( 'div', array(
		'class' => $class,
		'data-post-id' => $post->getPostId()->getHex(),
		'id' => 'flow-post-' . $post->getPostId()->getHex(),
	) );

	if ( $post->isFlagged( 'deleted' ) ) {
		$content = wfMessage( 'flow-post-deleted' );
		$user = wfMessage( 'flow-post-deleted' );

		// TODO make conditional on rights
		$actions['restore'] = $postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ) );

		$actions['history'] = Html::element( 'a', array(
			'href' => $self->generateUrl( $root->getPostId(), 'post-history', array(
				$block->getName() . '[postId]' => $post->getPostId()->getHex(),
			) ),
		), wfMessage( 'flow-post-action-history' )->plain() );
	} else {
		$user = Html::element( 'span', null, $post->getUserText() );
		$content = $post->getContent();
		$actions['delete'] = $postAction( 'delete-post', array( 'postId' => $post->getPostId()->getHex() ) );
		$actions['history'] = Html::element( 'a', array(
			'href' => $self->generateUrl( $root->getPostId(), 'post-history', array(
				$block->getName() . '[postId]' => $post->getPostId()->getHex(),
			) ),
		), wfMessage( 'flow-post-action-history' )->plain() );
		$replyForm = Html::openElement( 'form', array(
				'method' => 'POST',
				// root post id is same as topic workflow id
				'action' => $self->generateUrl( $root->getPostId(), 'reply' ),
				'class' => 'flow-reply-form',
			) );
			echo Html::element( 'input', array( 'type' => 'hidden', 'name' => 'wpEditToken', 'value' => $editToken) );

		if ( $block->getHexRepliedTo() === $post->getPostId()->getHex() ) {
			foreach ( $block->getErrors() as $error ) {
				$replyForm .= $error->text() . '<br>'; // the pain ...
			}
		}
		$replyForm .= Html::element( 'input', array(
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
?>
<div class="flow-post-title">
	<div class="flow-post-authorline">
<?php
echo $user;
?>
		<span class="flow-datestamp">
			<span class="flow-agotime" style="display: inline">&lt;timestamp&gt;</span>
			<span class="flow-utctime" style="display: none">&lt;timestamp&gt;</span>
		</span>
	</div>
</div>

<div class="flow-post-content">
<?php
echo $content
?>
</div>
<div class="flow-post-controls">
	<a class="flow-post-actions">
		<?php echo wfMessage('flow-post-actions')->escaped(); ?>
		<div class="flow-post-actionbox-pokey">&nbsp;</div>
		<div class="flow-post-actionbox">
			<ul>
<?php
foreach( $actions as $key => $action ) {
	echo '<li class="flow-action-'.$key.'">' . $action . '</li>';
}
?>
			</ul>
		</div>
	</a>
</div>
<?php

	echo '</div>';

	echo $replyForm;

	echo Html::openElement( 'div', array(
		'class' => 'flow-post-replies',
	) );
	foreach( $post->getChildren() as $child ) {
		$renderPost( $child );
	}
	echo '</div>';
	echo '</div>';
};

$title = $root->getContent();

echo Html::element( 'hr', array( 'class' => 'flow-topic-separator' ) );

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full',
	'id' => 'flow-topic-' . $topic->getId()->getHex(),
	'data-topic-id' => $topic->getId()->getHex(),
) );
?>
<div class="flow-titlebar">
	<div class="flow-topic-title">
		<div class="flow-realtitle">
<?php echo htmlspecialchars( $title ); ?>
		</div>
	</div>
	<div class="flow-topiccontrols">
	</div>
</div>
<div class="flow-metabar">
	<span class="flow-topic-datestamp">
		<span class="flow-agotime" style="display: inline">&lt;timestamp&gt;</span>
		<span class="flow-utctime" style="display: none">&lt;timestamp&gt;</span>
	</span>
	<a class="flow-topic-actionslink">
		<?php echo wfMessage( 'flow-topic-actions' )->escaped() ?>
		<div class="flow-topic-actionbox-pokey">&nbsp;</div>
		<div class="flow-topic-actionbox">
			<ul>
				<!-- Actions for entire topic. Currently none -->
			</ul>
		</div>
	</a>
</div>

<?php
foreach( $root->getChildren() as $child ) {
	$renderPost( $child );
}
?>
</div>