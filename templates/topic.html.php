<?php

// treat title like unparsed (wiki)text
$title = $root->getContent( $user, 'wikitext' );

echo Html::element( 'hr', array( 'class' => 'flow-topic-separator' ) );

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full',
	'id' => 'flow-topic-' . $topic->getId()->getHex(),
	'data-topic-id' => $topic->getId()->getHex(),
	'data-title' => $title,
) );
?>
<div class="flow-topic-opener"></div>
<div class="flow-titlebar">
	<div class="flow-topic-title">
		<div class="flow-realtitle">
			<?php echo htmlspecialchars( $title ); ?>
		</div>
	</div>

	<a class="flow-topic-actions-link" href="#"><?php echo wfMessage( 'flow-topic-actions' )->escaped(); ?></a>
	<div class="flow-topic-actions">
		<ul>
			<li class="flow-action-edit-title">
				<?php
				echo Html::rawElement( 'a',
					array(
						'href' => $this->generateUrl( $root->getPostId(), 'edit-title' )
					),
					wfMessage( 'flow-topic-action-edit-title' )
				);
				?>
			</li>
			<li class="flow-action-topic-history">
				<?php
				echo Html::rawElement( 'a',
					array(
						'href' => $this->generateUrl( $root->getPostId(), 'topic-history' )
					),
					wfMessage( 'flow-topic-action-history' )
				);
				?>
			</li>
		</ul>
	</div>

	<p class="flow-topic-posts-meta">
		@todo: participants<br />
		@todo: # comments
	</p>

	<p class="flow-topic-datestamp">
		<span class="flow-agotime" style="display: inline">
			<?php echo wfMessage( 'flow-last-modified' )->rawParams(
				$topic->getLastModifiedObj()->getHumanTimestamp()
			); ?>
		</span>
		<span class="flow-utctime" style="display: none">
			<?php
			$ts = new MWTimestamp( $topic->getLastModified() );
			echo $ts->getTimestamp( TS_RFC2822 );
			?>
		</span>
	</p>
</div>

<?php
foreach( $root->getChildren() as $child ) {
	echo $this->renderPost( $child, $block, $root );
}
?>
</div>
