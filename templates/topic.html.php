<?php // yes, this is a horrible quick hack ?>

<?php $self = $this ?>
<?php $renderPost = function( $post ) use( $self, $block, $root, &$renderPost ) { ?>
	<div style="padding-left: 20px">
		User: <?php echo Html::element( 'span', null, $post->getUserText() ) ?><br>
		Post ID: <?php echo $post->getPostId() ?><br>
		Content: <?php echo Html::element( 'p', null, $post->getContent() ) ?>
		<?php
		echo Html::openElement( 'form', array(
			'method' => 'POST',
			// root post id is same as topic workflow id
			'action' => $self->generateUrlForId( $root->getPostId(), 'reply' ),
		) );
		if ( $block->getRepliedTo() === $post->getPostId() ) {
			foreach ( $block->getErrors as $error ) {
				echo $error->text() . '<br>'; // the pain ...
			}
		}
		echo Html::element( 'input', array(
			'type' => 'hidden',
			'name' => $block->getName() . '[replyTo]',
			'value' => $post->getPostId(),
		) );
		echo Html::textarea( $block->getName() . '[content]' );
		echo Html::element( 'input', array(
			'type' => 'submit',
			'value' => wfMessage( 'flow-reply-to-post' )->text()
		) );
		echo '</form>';
		foreach( $post->getChildren() as $child ) {
			$renderPost( $child );
		}
		?>
	</div>
<?php } ?>

<?php echo Html::element( 'h4', array(), $root->getContent() ) ?>
<?php foreach( $root->getChildren() as $child ) { $renderPost( $child ); } ?>
