<?php
// Includes functions defined in TemplatingFunctions.php
namespace Flow\Templating;

/**
 * Variables expected:
 *
 *	block - Instance of Flow\Block\Block
 *	content - Wikitext of the post being edited
 *	editPostUrl - Url to submit edited post content to
 *	postId - Id of the post being edited
 */
?>
<div class="flow-topic-container flow-topic-full">
	<div class="flow-post-container">
		<div class="flow-edit-post-form flow-element-container">
			<form method="POST" action="<?= $this->editPostUrl->escaped() ?>">
				<?= $this->errors()->block( $this->block )->escaped() ?>
				<input type="hidden" name="wpEditToken" value="<?= $this->editToken->escaped() ?>">
				<input type="hidden" name="<?= $this->block->getName()->escaped() ?>[postId]" value="<?= $this->postId->escaped() ?>">
				<textarea name="<?= $this->block->getName()->escaped() ?>[content]"
				          class="mw-ui-input" rows="10"><?= $this->content->escaped() ?></textarea>
				<div class="flow-post-form-controls">
					<input type="submit" class="mw-ui-button mw-ui-constructive"
					       value="<?= wfMessage( 'flow-edit-post-submit' )->escaped() ?>">
				</div>
			</form>
		</div>
	</div>
</div>
