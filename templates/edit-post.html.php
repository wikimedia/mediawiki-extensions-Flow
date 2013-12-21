<?php
/**
 * Variables passed in
 *
 *	$block - Instance of Flow\Block\Block
 *	$content - Wikitext of the post being edited
 *	$editPostUrl - Url to submit edited post content to
 *	$postId - Id of the post being edited
 */
?>
<div class="flow-topic-container flow-topic-full">
	<div class="flow-post-container">
		<div class="flow-edit-post-form flow-element-container">
			<form method="POST" action="<?= htmlspecialchars( $editPostUrl ) ?>">
				<?= $this->render( 'flow:block-errors.html.php', compact( 'block' ) ) ?>
				<input type="hidden" name="wpEditToken" value="<?= htmlspecialchars( $editToken ) ?>">
				<input type="hidden" name="<?= htmlspecialchars( $block->getName() ) ?>[postId]"
				       value="<?= htmlspecialchars( $postId ) ?>">
				<textarea name="<?= htmlspecialchars( $block->getName() ) ?>[content]"
				          class="mw-ui-input" rows="10"><?= htmlspecialchars( $content ) ?></textarea>
				<div class="flow-post-form-controls">
					<input type="submit" class="mw-ui-button mw-ui-constructive"
					       value="<?= wfMessage( 'flow-edit-post-submit' )->escaped() ?>">
				</div>
			</form>
		</div>
	</div>
</div>
