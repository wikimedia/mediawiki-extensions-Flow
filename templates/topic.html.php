<?php

// treat title like unparsed (wiki)text
$title = $this->getContent( $root, 'wikitext' );
$title = trim( $title );

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
			'data-post-id' => $root->getPostId()->getAlphadecimal(),
			'id' => 'flow-topic-reply-' . $topic->getId()->getAlphadecimal()
		) ) .
		'<span class="flow-creator">' .
			$this->userToolLinks( $user->getId(), $user->getName() ) .
		'</span>' .
		Html::openElement( 'form', array(
			'method' => 'POST',
			'action' => $this->generateUrl( $block->getWorkflow(), 'reply' ),
			'class' => 'flow-topic-reply-form',
		) ) .
			Html::element( 'input', array(
				'type' => 'hidden',
				'name' => $block->getName() . '_replyTo',
				'value' => $root->getPostId()->getAlphadecimal(),
			) ) .
			Html::element( 'input', array(
				'type' => 'hidden',
				'name' => 'wpEditToken',
				'value' => $editToken,
			) ) .
			Html::textarea( $block->getName() . '_topic-reply-content', '', array(
				'placeholder' => wfMessage( 'flow-reply-topic-placeholder', $user->getName(), $title )->text(),
				'class' => 'mw-ui-input flow-reply-content',
				'rows' => '10',
			) ) .
			'<div class="flow-form-controls">' .
				Html::rawElement( 'div', array(
					'class' => 'flow-terms-of-use plainlinks'
				),  Flow\TermsOfUse::getReplyTerms() ) .
				Html::element( 'input', array(
					'type' => 'submit',
					'value' => wfMessage( 'flow-reply-submit', $this->getCreatorText( $root ) ),
					'class' => 'mw-ui-button mw-ui-constructive flow-reply-submit',
				) ) .
				Html::element( 'div', array( 'class' => 'clear' ) ) .
			Html::closeElement( 'div' ) .
		Html::closeElement( 'form' ) .
	Html::closeElement( 'div' );
}

//
// Content starts here
//
$moderationClass = '';

if ( $root->isModerated() ) {
	// moderated posts start out collapsed
	$moderationClass .= ' flow-topic-closed flow-topic-moderated';
	$moderationClass .= ' flow-topic-closed flow-topic-moderated-'.$root->getModerationState();
}

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full' . $moderationClass,
	'id' => 'flow-topic-' . $topic->getId()->getAlphadecimal(),
	'data-topic-id' => $topic->getId()->getAlphadecimal(),
	'data-creator-name' => $this->getCreatorText( $root ),
) );
?>
<div class="flow-element-container">
	<div class="flow-titlebar mw-ui-button">
		<div class="flow-topic-title">
			<?php
				echo Html::element( 'h2',
					array( 'class' => 'flow-realtitle' ),
					$title
				), $postView->createModifiedTipsyLink( $block );
				echo $postView->createModifiedTipsyHtml( $block );
			?>

			<?php if ( $root->isModerated() ):
				echo Html::rawElement(
					'h2',
					array( 'class' => 'flow-moderated-title' ),
					$this->getModeratedContent( $root )
				);
			endif ?>
		</div>
		<?php if ( $postActionMenu->isAllowedAny( 'history', 'view', 'hide-topic', 'delete-topic', 'suppress-topic', 'restore-topic', 'edit-title', 'close-topic', 'edit-topic-summary' ) ): ?>
		<div class="flow-tipsy flow-actions">
			<a class="flow-tipsy-link" href="#"><?php echo wfMessage( 'flow-topic-actions' )->escaped(); ?></a>
			<div class="flow-tipsy-flyout">
				<ul>
					<?php if ( $postActionMenu->isAllowed( 'edit-title' ) ) {
						echo '<li class="flow-action-edit-title">', $postActionMenu->getButton(
							'edit-title',
							wfMessage( 'flow-topic-action-edit-title' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-edit-topic-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'view' ) ) {
						echo '<li class="flow-action-permalink">', $postActionMenu->getButton(
							'view',
							wfMessage( 'flow-topic-action-view' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-action-permalink-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'history' ) ) {
						echo '<li class="flow-action-topic-history">', $postActionMenu->getButton(
							'history',
							wfMessage( 'flow-topic-action-history' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-action-topic-history-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'edit-topic-summary' ) ) {
						$summaryLink = $summary ? 'flow-topic-action-resummarize-topic' : 'flow-topic-action-summarize-topic';
						echo '<li class="flow-action-summarize">', $postActionMenu->getButton(
							'edit-topic-summary',
							wfMessage( $summaryLink )->escaped(),
							'mw-ui-button mw-ui-quiet flow-summarize-topic-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowedAny( 'hide-topic', 'delete-topic', 'suppress-topic', 'restore-topic', 'close-topic' ) ) { ?>
						<li><hr /></li>
					<?php } ?>

					<?php if ( $postActionMenu->isAllowed( 'hide-topic' ) ) {
						echo '<li class="flow-action-hide">', $postActionMenu->getButton(
							'hide-topic',
							wfMessage( 'flow-topic-action-hide-topic' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-hide-topic-link'
						), '</li>';
					} ?>
					<?php if ( $root->getModerationState() === \Flow\Model\AbstractRevision::MODERATED_HIDDEN && $postActionMenu->isAllowed( 'restore-topic' ) ) {
						echo '<li class="flow-action-unhide">', $postActionMenu->getButton(
							'restore-topic',
							wfMessage( 'flow-topic-action-unhide-topic' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-unhide-topic-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'delete-topic' ) ) {
						echo '<li class="flow-action-delete">', $postActionMenu->getButton(
							'delete-topic',
							wfMessage( 'flow-topic-action-delete-topic' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-delete-topic-link'
						), '</li>';
					} ?>
					<?php if ( $root->getModerationState() === \Flow\Model\AbstractRevision::MODERATED_DELETED && $postActionMenu->isAllowed( 'restore-topic' ) ) {
						echo '<li class="flow-action-undelete">', $postActionMenu->getButton(
							'restore-topic',
							wfMessage( 'flow-topic-action-undelete-topic' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-undelete-topic-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'suppress-topic' ) ) {
						echo '<li class="flow-action-suppress">', $postActionMenu->getButton(
							'suppress-topic',
							wfMessage( 'flow-topic-action-suppress-topic' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-suppress-topic-link'
						), '</li>';
					} ?>
					<?php if ( $root->getModerationState() === \Flow\Model\AbstractRevision::MODERATED_SUPPRESSED && $postActionMenu->isAllowed( 'restore-topic' ) ) {
						echo '<li class="flow-action-unsuppress">', $postActionMenu->getButton(
							'restore-topic',
							wfMessage( 'flow-topic-action-unsuppress-topic' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-unsuppress-topic-link'
						), '</li>';
					} ?>
					<?php if ( $postActionMenu->isAllowed( 'close-topic' ) ) {
						echo '<li class="flow-action-close">', $postActionMenu->getButton(
							'close-topic',
							wfMessage( 'flow-topic-action-close-topic' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-close-topic-link'
						), '</li>';
					} ?>
					<?php if ( $root->getModerationState() === \Flow\Model\AbstractRevision::MODERATED_CLOSED && $postActionMenu->isAllowed( 'restore-topic' ) ) {
						echo '<li class="flow-action-reopen">', $postActionMenu->getButton(
							'restore-topic',
							wfMessage( 'flow-topic-action-reopen-topic' )->escaped(),
							'mw-ui-button mw-ui-quiet flow-reopen-topic-link'
						), '</li>';
					} ?>
				</ul>
			</div>
		</div>
		<?php
			endif;
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

		<?php if ( !$root->isModerated() ): ?>
		<ul class="flow-topic-posts-meta">
			<li class="flow-topic-participants">
				<?php echo $this->printParticipants( $root, $indexParticipants ); ?>
			</li>
			<li class="flow-topic-comments">
				<a href="#<?php echo 'flow-topic-reply-' . $topic->getId()->getAlphadecimal(); ?>" class="flow-reply-link flow-topic-comments-link" data-topic-id="<?php echo $topic->getId()->getAlphadecimal() ?>">
					<?php
						// get total number of posts in topic
						// @todo: the number of comments should not be a part of the link
						$comments = $root->getRecursiveResult( $indexDescendantCount );
						echo wfMessage( 'flow-topic-comments' )
							->numParams( $comments )
							->params( $user->getName() )
							->escaped();
					?>
				</a>
				<?php
					echo $this->render( 'flow:timestamp.html.php', array(
						'timestamp' => $topic->getLastModifiedObj(),
					), true );
				?>
			</li>
		</ul>
		<p class="flow-topic-posts-meta-minimal">
			<?php echo count( $root->getRecursiveResult( $indexParticipants ) ); ?>
		</p>
		<?php endif; ?>
		<?php
		if ( $summary ) {
		?>
			<div class="flow-topic-summary">
				<?php echo $this->getContent( $summary, 'html' ); ?>
			</div>
		<?php
		}
		?>
	</div>
</div>
<?php
echo '<div class="flow-topic-children-container">';
foreach( $root->getChildren() as $child ) {
	echo $this->renderPost( $child, $block );
}
echo "$topicReplyBox</div></div>";
