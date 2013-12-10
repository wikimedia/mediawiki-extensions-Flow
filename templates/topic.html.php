<?php

// treat title like unparsed (wiki)text
$title = $this->getContent( $root, 'wikitext', $user );

// pre-register recursive callbacks; will then be fetched all at once when the
// first one's result is requested
$indexDescendantCount = $root->registerDescendantCount();
$indexParticipants = $root->registerParticipants();
$this->registerParsoidLinks( $root );

// topic reply box.
$topicReplyBox = '';
if ( $postActionMenu->isAllowed( 'reply' ) ) {
	// Topic reply box
	$topicReplyBox = Html::openElement( 'div', array(
			'class' => 'flow-topic-reply-container flow-post-container flow-element-container',
			'data-post-id' => $root->getPostId()->getHex(),
			'id' => 'flow-topic-reply-' . $topic->getId()->getHex()
		) ) .
		'<span class="flow-creator">
			<span class="flow-creator-simple" style="display: inline">' .
				htmlentities( $user->getName() ) .
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
				'value' => $root->getPostId()->getHex(),
			) ) .
			Html::element( 'input', array(
				'type' => 'hidden',
				'name' => 'wpEditToken',
				'value' => $editToken,
			) ) .
			Html::textarea( $block->getName() . '[topic-reply-content]', '', array(
				'placeholder' => wfMessage( 'flow-reply-topic-placeholder', $user->getName(), $title )->text(),
				'class' => 'mw-ui-input flow-topic-reply-content',
				'rows' => '10',
			) ) .
			'<div class="flow-post-form-controls">' .
				Html::element( 'input', array(
					'type' => 'submit',
					'value' => wfMessage( 'flow-reply-submit', $this->getCreatorText( $root, $user ) ),
					'class' => 'mw-ui-button mw-ui-constructive flow-topic-reply-submit',
				) ) .
			Html::closeElement( 'div' ) .
		Html::closeElement( 'form' ) .
	Html::closeElement( 'div' );
}

//
// Content starts here
//
$moderationClass = '';

if ( $root->isModerated() ) {
	$moderationClass .= ' flow-topic-moderated';
	$moderationClass .= ' flow-topic-moderated-'.$root->getModerationState();
}

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full' . $moderationClass,
	'id' => 'flow-topic-' . $topic->getId()->getHex(),
	'data-topic-id' => $topic->getId()->getHex(),
	'data-creator-name' => $this->getCreatorText( $root, $user ),
	'data-title' => $root->isModerated() ? '' : $title,
) );
?>
<div class="flow-element-container">
	<div class="flow-titlebar mw-ui-button">
		<?php
		if ( $postActionMenu->isAllowed( 'edit-title' ) ) {
			echo Html::element(
				'a',
				array(
					'href' => $this->generateUrl( $root->getPostId(), 'edit-title' ),
					'class' => 'flow-edit-topic-link flow-icon flow-icon-top-aligned',
					'title' => wfMessage( 'flow-topic-action-edit-title' )->text(),
				),
				wfMessage( 'flow-topic-action-edit-title' )->text()
			);
		}
		?>

		<div class="flow-topic-title">
			<?php if ( $root->isModerated() ):
				echo Html::rawElement(
					'h2',
					array( 'class' => 'flow-topic-moderated flow-topic-moderated-' . $root->getModerationState() ),
					/* Passing no user always gets the 'moderated by Foo' message */
					$this->getContent( $root, 'wikitext' )
				);
			else: 
				echo Html::element( 'h2', array( 'class' => 'flow-realtitle' ), $title );
			endif ?>
		</div>

		<?php if ( $postActionMenu->isAllowedAny( 'hide-topic', 'delete-topic', 'suppress-topic', 'restore-topic' ) ): ?>
		<div class="flow-tipsy flow-actions">
			<a class="flow-tipsy-link" href="#"><?php echo wfMessage( 'flow-topic-actions' )->escaped(); ?></a>
			<div class="flow-tipsy-flyout">
				<ul>
					<?php if ( $postActionMenu->isAllowed( 'hide-topic' ) ) {
						echo '<li class="flow-action-hide">', $postActionMenu->getButton(
							'hide-topic',
							wfMessage( 'flow-topic-action-hide-topic' )->escaped(),
							'mw-ui-button flow-hide-topic-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'delete-topic' ) ) {
						echo '<li class="flow-action-delete">', $postActionMenu->getButton(
							'delete-topic',
							wfMessage( 'flow-topic-action-delete-topic' )->escaped(),
							'mw-ui-button flow-delete-topic-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'suppress-topic' ) ) {
						echo '<li class="flow-action-suppress">', $postActionMenu->getButton(
							'suppress-topic',
							wfMessage( 'flow-topic-action-suppress-topic' )->escaped(),
							'mw-ui-button flow-suppress-topic-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'restore-topic' ) ) {
						echo '<li class="flow-action-restore">', $postActionMenu->getButton(
							'restore-topic',
							wfMessage( 'flow-topic-action-restore-topic' )->escaped(),
							'mw-ui-button flow-restore-topic-link'
						), '</li>';
					} ?>
<!--
					<li class="flow-action-close">
						<a href="#" class="mw-ui-button">@todo: Close topic</a>
					</li>
-->
				</ul>
			</div>
		</div>
		<?php
			endif;

			echo $this->render( 'flow:timestamp.html.php', array(
				'historicalLink' => $postActionMenu->actionUrl( 'topic-history' ),
				'timestamp' => $topic->getLastModifiedObj(),
			), true );
		?>

		<?php
/*
			// @todo: there's no watchlist functionality yet; this blurb is just to position it correctly already
			$watchlistActive = false; // @todo: true if already watchlisted, false if not
			echo Html::element(
				'a',
				array(
					'class' => 'flow-icon-watchlist flow-icon flow-icon-top-aligned'
						. ( $watchlistActive ? ' flow-icon-watchlist-active' : '' ),
					'title' => wfMessage( 'flow-topic-action-watchlist' )->text(),
					'href' => '#',
					'onclick' => "alert( '@todo: Not yet implemented!' ); return false;"
				),
				wfMessage( 'flow-topic-action-watchlist' )->text()
			);
*/
		?>

		<ul class="flow-topic-posts-meta">
			<li class="flow-topic-participants">
				<?php echo $this->printParticipants( $root, $indexParticipants ); ?>
			</li>
			<li class="flow-topic-comments">
				<a href="#<?php echo 'flow-topic-reply-' . $topic->getId()->getHex(); ?>" class="flow-reply-link" data-topic-id="<?php echo $topic->getId()->getHex() ?>">
					<?php
						// get total number of posts in topic
						// @todo: the number of comments should not be a part of the link
						$comments = $root->getRecursiveResult( $indexDescendantCount );
						echo wfMessage( 'flow-topic-comments', $comments )->escaped();
					?>
				</a>
			</li>
		</ul>
		<ul class="flow-topic-posts-meta-minimal">
			<?php
			$userCount = count( $root->getRecursiveResult( $indexParticipants ) );
			echo wfMessage( 'flow-topic-meta-minimal', $comments, $userCount )->escaped(); ?>
		</ul>

		<?php
			echo Html::element(
				'a',
				array(
					'class' => 'flow-icon-permalink flow-icon flow-icon-bottom-aligned',
					'title' => wfMessage( 'flow-topic-action-view' )->text(),
					'href' => $this->generateUrl( $topic ),
				),
				wfMessage( 'flow-topic-action-view' )->text()
			);
		?>
	</div>
</div>
<?php
echo '<div class="flow-topic-children-container">';
foreach( $root->getChildren() as $child ) {
	echo $this->renderPost( $child, $block );
}
echo "$topicReplyBox</div></div>";
