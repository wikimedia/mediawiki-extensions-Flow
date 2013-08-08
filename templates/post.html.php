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
	return Html::element( 'a',
		array(
			'href' => $url,
			'class' => $class,
		),
		wfMessage( "flow-post-action-$action" )->plain()
	);
};

$renderPost = function( $post ) use( $self, $block ) {
	echo $self->renderPost( $post, $block );
};

echo Html::openElement( 'div', array(
	'class' => 'flow-post-container',
	'data-post-id' => $post->getRevisionId()->getHex(),
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
	$actions['restore'] = $postAction( 'restore-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-constructive' );

	$actions['history'] = Html::element( 'a', array(
		'href' => $self->generateUrl( $block->getWorkflowId(), 'post-history', array(
			$block->getName() . '[postId]' => $post->getPostId()->getHex(),
		) ),
	), wfMessage( 'flow-post-action-history' )->plain() );
} else {
	$user = Html::element( 'span', null, $post->getUserText() );
	$content = $post->getContent();
	$actions['delete'] = $postAction( 'delete-post', array( 'postId' => $post->getPostId()->getHex() ), 'mw-ui-destructive' );
	$actions['history'] = $getAction( 'post-history' );
	$actions['permalink'] = $getAction( 'view' );
	$actions['edit-post'] = $getAction( 'edit-post' );
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
	$replyForm .= Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . '[replyTo]',
			'value' => $post->getPostId()->getHex(),
		) ) .
		Html::textarea( $block->getName() . '[content]', '', array(
			'placeholder' => wfMessage( 'flow-reply-placeholder',
				$post->getUserText() )->text(),
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
	<div class="flow-post-actions">
		<a><?php echo wfMessage('flow-post-actions')->escaped(); ?></a>
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