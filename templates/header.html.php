<?php
/**
 * Variables expected:
 *
 *	block - instance of Flow\Block\Block
 *	exists  - Boolean false if no header has been created
 *	content - The main content
 *	editUrl - Url to visit to edit this header
 */
?>

<div id="flow-header" class="flow-element-container">
	<?php if ( $error = $this->block->getErrorMessage( 'content' ) ): ?>
		<p id="flow-header-error"><?= $error->escaped() ?></p>
	<?php endif ?>

	<div id="flow-header-content" class="<?= $this->exists ? 'flow-header-exists' : 'flow-header-empty' ?>">
		<?= $this->exists ? $this->content->escaped() : wfMessage( 'flow-header-empty' )->escaped() ?>
	</div>

	<?php $linkText = wfMessage( 'flow-edit-header-link' )->escaped() ?>
	<a href="<?= $this->editUrl->escaped() ?>" class="flow-header-edit-link flow-icon flow-icon-bottom-aligned"
	   title="<?= $linkText ?>"><?= $linkText ?></a>
</div>
