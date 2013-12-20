<?php
/**
 * Variables passed in:
 *
 *	$block - instance of Flow\Block\Block
 *	$content - Fully escaped header content for display
 *	$editUrl - Url to visit to edit this header
 */
?>

<div id="flow-header" class="flow-element-container">
	<?php if ( $error = $block->getErrorMessage( 'content' ) ): ?>
		<p id="flow-header-error"><?= $error->escaped() ?></p>
	<?php endif ?>

	<div id="flow-header-content" class="<?= $content ? 'flow-header-exists' : 'flow-header-empty' ?>">
		<?= $content ? $content : wfMessage( 'flow-header-empty' )->ecaped() ?>
	</div>

	<a href="<?= htmlspecialchars( $editUrl ) ?>"
	   class="flow-header-edit-link flow-icon flow-icon-bottom-aligned"
	   title="<?= wfMessage( 'flow-edit-header-link' )->escaped() ?>">
		<?= wfMessage( 'flow-edit-header-link' )->escaped() ?>
	</a>
</div>
