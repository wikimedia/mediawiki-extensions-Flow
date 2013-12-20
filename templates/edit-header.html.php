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
		<form method="POST" action="<?= $this->formUrl->escaped() ?>" class="flow-header-form">
			<?= $this->errors()->block( $this->block )->escaped() ?>
			<input type="hidden" name="wpEditToken" value="<?= $this->editToken->escaped() ?>">
			<?php if ( $this->hasPrevRevision ): ?>
				<input type="hidden" name="<?= $this->block->getName()->escaped() ?>[prev_revision]"
				       value="<?= $this->revisionId->escaped() ?>">
			<?php endif ?>
			<textarea name="<?= $this->block->getName()->escaped() ?>[content]" class="mw-ui-input" rows="10"
			          data-header-id="<?= $this->revisionId->escaped() ?>"><?= $this->content->escaped() ?></textarea>
			<div class="flow-edit-header-controls">
				<input type="submit" class="mw-ui-button mw-ui-constructive"
				       value="<?= wfMessage( 'flow-edit-header-submit' )->escaped() ?>">
			</div>
		</form>
	</div>
</div>
