<?php
// yes, this is a horrible quick hack
// probably be better off if the templates were classes that were called as
//     $template->render( $options );
// or some such

$self = $this;

$editToken = $user->getEditToken( 'flow' );

$renderPost = function( $post ) use( $self, $block, $root, $user ) {
	echo $self->renderPost( $post, $block, $root );
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
	<div class="flow-topic-actionslink">
		<a><?php echo wfMessage( 'flow-topic-actions' )->escaped() ?></a>
		<div class="flow-actionbox-pokey">&nbsp;</div>
		<div class="flow-topic-actionbox">
			<ul>
				<!-- Actions for entire topic. Currently none -->
			</ul>
		</div>
	</div>
	<span class="flow-topic-datestamp">
		<span class="flow-agotime" style="display: inline">&lt;timestamp&gt;</span>
		<span class="flow-utctime" style="display: none">&lt;timestamp&gt;</span>
	</span>
</div>

<?php
foreach( $root->getChildren() as $child ) {
	$renderPost( $child );
}
?>
</div>