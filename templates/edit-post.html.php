<?php
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
			<form method="POST" action="<?php echo $this->editPostUrl->escaped() ?>">
				<?php echo $this->errors()->block( $this->block )->escaped() ?>
				<input type="hidden" name="wpEditToken" value="<?php echo $this->editToken->escaped() ?>">
				<input type="hidden" name="<?php echo $this->block->getName()->escaped() ?>[postId]" value="<?php echo $this->postId->escaped() ?>">
				<textarea name="<?php echo $this->block->getName()->escaped() ?>[content]"
				          class="mw-ui-input" rows="10"><?php echo $this->content->escaped() ?></textarea>
				<div class="flow-post-form-controls">
					<input type="submit" class="mw-ui-button mw-ui-constructive"
					       value="<?php echo wfMessage( 'flow-edit-post-submit' )->escaped() ?>">
				</div>
			</form>
		</div>
	</div>
</div>
