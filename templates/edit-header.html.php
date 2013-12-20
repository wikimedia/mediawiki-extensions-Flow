<?php
/**
 * Variables expected:
 *
 *	block - instance of Flow\Block\Block
 *	content - The content of the header, as wikitext
 *	formUrl - String url for form submission
 *	hasPrevRevision - Boolean true when a previous revision exists
 *	revisionId - The id of the prevision revision (optional)
 */

?>
<div id="flow-header">
	<div class="flow-edit-header-form flow-element-container">
		<form method="POST" action="<?php echo $this->formUrl->escaped() ?>" class="flow-header-form">
			<?php echo $this->errors()->block( $this->block )->escaped() ?>
			<input type="hidden" name="wpEditToken" value="<?php echo $this->editToken->escaped() ?>">
			<?php if ( $this->hasPrevRevision ): ?>
				<input type="hidden" name="<?php echo $this->block->getName()->escaped() ?>[prev_revision]"
				       value="<?php echo $this->revisionId->escaped() ?>">
			<?php endif ?>
			<textarea name="<?php echo $this->block->getName()->escaped() ?>[content]" class="mw-ui-input" rows="10"
			          data-header-id="<?php echo $this->revisionId->escaped() ?>"><?php echo $this->content->escaped() ?></textarea>
			<div class="flow-edit-header-controls">
				<input type="submit" class="mw-ui-button mw-ui-constructive"
				       value="<?php echo wfMessage( 'flow-edit-header-submit' )->escaped() ?>">
			</div>
		</form>
	</div>
</div>
