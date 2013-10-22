<?php

// treat title like unparsed (wiki)text
$title = $root->getContent( $user, 'wikitext' );

// pre-register recursive callbacks; will then be fetched all at once when the
// first one's result is requested
$indexDescendantCount = $root->registerDescendantCount();
$indexParticipants = $root->registerParticipants();

// topic reply box
if ( $root->isModerated() ) {
	$otherUser = User::newFromId( $root->getModeratedByUserId() );
	$title = $otherUser->getTalkPage();

	$topicReplyBox = '<a class="flow-talk-link mw-ui-button" href="' . $title->getLinkURL() .
		'"><span>' . wfMessage( 'flow-talk-link', $root->getModeratedByUserText() )->escaped() .
		'</span></a>';
} else {
	$topicReplyBox = Html::openElement( 'div', array(
			'class' => 'flow-topic-reply-container flow-post-container flow-element-container',
			'data-post-id' => $root->getRevisionId()->getHex(),
			'id' => 'flow-topic-reply-' . $topic->getId()->getHex()
		) ) .
			'<span class="flow-creator">
				<span class="flow-creator-simple" style="display: inline">' .
					$this->getUserText( $user ) .
				'</span>
				<span class="flow-creator-full" style="display: none">' .
					$this->userToolLinks( $user->getId(), $user->getName() ) .
				'</span>
			</span>' .
			Html::openElement( 'form', array(
				'method' => 'POST',
				'action' => $this->generateUrl( $block->getWorkflow(), 'reply' ),
				'class' => 'flow-topic-reply-form',
			) ) .
				Html::element( 'input', array(
					'type' => 'hidden',
					'name' => $block->getName() . '[replyTo]',
					'value' => $topic->getId()->getHex(),
				) ) .
				Html::element( 'input', array(
					'type' => 'hidden',
					'name' => 'wpEditToken',
					'value' => $editToken,
				) ) .
				Html::textarea( $block->getName() . '[topic-reply-content]', '', array(
					'placeholder' => wfMessage( 'flow-reply-topic-placeholder', $user->getName(), $title )->text(),
					'class' => 'flow-input mw-ui-input flow-topic-reply-content',
				) ) .
				Html::openElement( 'div', array( 'class' => 'flow-post-form-controls' ) ) .
					Html::element( 'input', array(
						'type' => 'submit',
						'value' => wfMessage( 'flow-reply-submit', $this->getUserText( $root->getCreator( $user ), $root ) )->text(),
						'class' => 'mw-ui-button mw-ui-constructive flow-topic-reply-submit',
					) ) .
				Html::closeElement( 'div' ) .
			Html::closeElement( 'form' ) .
		Html::closeElement( 'div' );
}

//
// Content starts here
//

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full' . ( $root->isModerated() ? ' flow-topic-moderated' : '' ),
	'id' => 'flow-topic-' . $topic->getId()->getHex(),
	'data-topic-id' => $topic->getId()->getHex(),
	'data-title' => $root->isModerated() ? '' : $title,
) );
?>
<div class="flow-element-container">
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
			<?php if ( $root->isModerated() ): ?>
				<h2 class='flow-topic-moderated flow-topic-moderated-<?php echo $root->getModerationState() ?>'>
					<?php // passing null as user (unprivileged) gets the hidden/deleted/suppressed text
					echo htmlspecialchars( $root->getContent( null ) ) ?>
				</h2>
			<?php else: ?>
				<h2 class="flow-realtitle">
					<?php echo htmlspecialchars( $title ); ?>
				</h2>
			<?php endif ?>
		</div>

		<div class="flow-actions">
			<a class="flow-actions-link" href="#"><?php echo wfMessage( 'flow-topic-actions' )->escaped(); ?></a>
			<div class="flow-actions-flyout">
				<ul>
					<?php if ( $postActionMenu->isAllowed( 'hide-topic' ) ) {
						echo '<li class="flow-action-hide">', $postActionMenu->getButton(
							'hide-topic',
							wfMessage( 'flow-topic-action-hide-topic' )->plain(),
							'mw-ui-button'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'delete-topic' ) ) {
						echo '<li class="flow-action-delete">', $postActionMenu->getButton(
							'delete-topic',
							wfMessage( 'flow-topic-action-delete-topic' )->plain(),
							'mw-ui-button'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'censor-topic' ) ) {
						echo '<li class="flow-action-censor">', $postActionMenu->getButton(
							'censor-topic',
							wfMessage( 'flow-topic-action-censor-topic' )->plain(),
							'mw-ui-button'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'restore-topic' ) ) {
						echo '<li class="flow-action-restore">', $postActionMenu->getButton(
							'restore-topic',
							wfMessage( 'flow-topic-action-restore-topic' )->plain(),
							'mw-ui-button'
						), '</li>';
					} ?>
					<li class="flow-action-close">
						<a href="#" class="mw-ui-button">@todo: Close topic</a>
					</li>
				</ul>
			</div>
		</div>

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

		<?php if ( $root->isAllowed( $user ) ):
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
					<?php echo $this->printParticipants( $root, $indexParticipants ); ?>
				</li>
				<li class="flow-topic-comments">
					<a href="#" class="flow-reply-link" data-topic-id="<?php echo $topic->getId()->getHex() ?>">
						<?php
							// get total number of posts in topic
							$comments = $root->getRecursiveResult( $indexDescendantCount );
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
		<?php endif; /* !$root->isModerated() */ ?>
	</div>
</div>
<?php
if ( $root->isAllowed( $user ) ) {
	$children = $root->getChildren();
	if ( $root->isModerated() ) {
		// moderated only shows parent post
		$children = array_slice( $children, 0, 1 );
	}
	echo '<div class="flow-topic-children-container">';
	foreach( $children as $child ) {
		echo $this->renderPost( $child, $block, $root );
	}
	// Topic reply box
	echo $topicReplyBox, "</div>";
}
echo '</div>';

