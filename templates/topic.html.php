<?php

// treat title like unparsed (wiki)text
$title = $root->getContent( $user, 'wikitext' );

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full',
	'id' => 'flow-topic-' . $topic->getId()->getHex(),
	'data-topic-id' => $topic->getId()->getHex(),
	'data-title' => $title,
) );
?>
<div class="flow-titlebar mw-ui-button">

	<?php
	echo Html::rawElement(
		'a',
		array(
			'href' => $this->generateUrl( $root->getPostId(), 'edit-title' ),
			'class' => 'flow-edit-topic-link flow-icon flow-icon-top-aligned',
		),
		wfMessage( 'flow-topic-action-edit-title' )
	);
	?>

	<div class="flow-topic-title">
		<h2 class="flow-realtitle">
			<?php echo htmlspecialchars( $title ); ?>
		</h2>
	</div>
	<div class="flow-actions">
		<a class="flow-actions-link" href="#"><?php echo wfMessage( 'flow-topic-actions' )->escaped(); ?></a>
		<div class="flow-actions-flyout">
			<ul>
				<li class="flow-action-hide">
					<a href="#" class="mw-ui-button mw-ui-destructive">@todo: Hide topic</a>
				</li>
				<li class="flow-action-close">
					<a href="#" class="mw-ui-button">@todo: Close topic</a>
				</li>
			</ul>
		</div>
	</div>

	<?php
		echo Html::element(
			'a',
			array(
				'class' => 'flow-icon-permalink flow-icon flow-icon-top-aligned',
				'title' => wfMessage( 'flow-topic-action-view' )->text(),
				'href' => $this->generateUrl( $topic ),
			),
			wfMessage( 'flow-topic-action-view' )->text()
		);
	?>

	<ul class="flow-topic-posts-meta">
		<li class="flow-topic-participants">
			<?php echo $this->printParticipants( $root ); ?>
		</li>
		<li class="flow-topic-comments">
			<a href="#" class="flow-reply-link" data-topic-id="<?php echo $topic->getId()->getHex() ?>">
				<?php
					// get total number of posts in topic
					$comments = $root->getDescendantCount();
					echo wfMessage( 'flow-topic-comments', $comments )->text();
				?>
			</a>
		</li>
	</ul>

	<?php
		// @todo: afaik, there's no watchlist functionality yet; this blurb is just to position it correctly already

		$watchlistActive = false; // @todo: true if already watchlisted, false if not
		echo Html::element(
			'a',
			array(
				'class' => 'flow-icon-watchlist flow-icon flow-icon-bottom-aligned'
					. ( $watchlistActive ? ' flow-icon-watchlist-active' : '' ),
				'title' => wfMessage( 'flow-topic-action-watchlist' )->text(),
				'href' => '#',
				'onclick' => "alert( '@todo: Not yet implemented!' ); return false;"
			),
			wfMessage( 'flow-topic-action-watchlist' )->text()
		);
	?>

	<p class="flow-datestamp">
		<?php
			// timestamp html
			$content = '
				<span class="flow-agotime" style="display: inline">'. $topic->getLastModifiedObj()->getHumanTimestamp() .'</span>
				<span class="flow-utctime" style="display: none">'. $topic->getLastModifiedObj()->getTimestamp( TS_RFC2822 ) .'</span>';

			// build history button with timestamp html as content
			echo Html::rawElement( 'a',
				array(
					'class' => 'flow-action-history-link',
					'href' => $this->generateUrl( $root->getPostId(), 'topic-history' ),
				),
				$content
			);
		?>
	</p>
</div>
<?php
foreach( $root->getChildren() as $child ) {
	echo $this->renderPost( $child, $block, $root );
}

// Topic reply box
echo Html::openElement( 'div', array(
	'class' => 'flow-topic-reply-container flow-post-container',
	'data-post-id' => $root->getRevisionId()->getHex(),
	'id' => 'flow-topic-reply-' . $topic->getId()->getHex()
) );
?>
	<span class="flow-creator">
		<span class="flow-creator-simple" style="display: inline">
			<?php echo htmlspecialchars( $user->getName() ); ?>
		</span>
		<span class="flow-creator-full" style="display: none">
			<?php echo $this->userToolLinks( $user->getId(), $user->getName() ); ?>
		</span>
	</span>
<?php
	echo Html::openElement( 'form', array(
		'method' => 'POST',
		'action' => $this->generateUrl( $block->getWorkflow(), 'reply' ),
		'class' => 'flow-topic-reply-form',
	) ),
	Html::element( 'input', array(
		'type' => 'hidden',
		'name' => $block->getName() . '[replyTo]',
		'value' => $topic->getId()->getHex(),
	) ),
	Html::element( 'input', array(
		'type' => 'hidden',
		'name' => 'wpEditToken',
		'value' => $editToken,
	) ),
	Html::textarea( $block->getName() . '[topic-reply-content]', '', array(
		'placeholder' => wfMessage( 'flow-reply-topic-placeholder', $user->getName(), $title )->text(),
		'class' => 'flow-input mw-ui-input flow-topic-reply-content',
	) ),
	Html::openElement( 'div', array( 'class' => 'flow-post-form-controls' ) ),
	Html::element( 'input', array(
		'type' => 'submit',
		'value' => wfMessage( 'flow-reply-submit', $this->getUserText( $root->getCreator( $user ), $root ) )->text(),
		'class' => 'mw-ui-button mw-ui-constructive flow-topic-reply-submit',
	) ),
	Html::closeElement( 'div' ),
	Html::closeElement( 'form' ),
	Html::closeElement( 'div' );
?>
</div>
