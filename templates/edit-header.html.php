<?php
/**
 * Variables passed in:
 *
 *	$block - instance of Flow\Block\Block
 *	$content - The content of the header, as wikitext
 *	$formUrl - String url for form submission
 *	$revisionId - The id of the most recent header for this block
 */

$errors = array();
if ( $block->hasErrors() ) {
	foreach ( $block->getErrors() as $error ) {
		$errors[] = $block->getErrorMessage( $error )->escaped();
	}
}

?>

<div id="flow-header">
	<div class="flow-edit-header-form flow-element-container">
		<form method="POST" action="<?= htmlspecialchars( $formUrl ) ?>" class="flow-header-form">
			<?php if ( $errors ): ?>
				<ul><li><?= implode( '</li><li>', $errors ) ?></li></ul>
			<?php endif; ?>
			<input type="hidden" name="wpEditToken" value="<?= htmlspecialchars( $editToken ) ?>">
			<?php if ( $revisionId ): ?>
				<input type="hidden"
				       name="<?= htmlspecialchars( $block->getName() ) ?>[prev_revision]"
					   value="<?= htmlspecialchars( $revisionId ) ?>">
			<?php endif ?>
			<textarea name="<?= htmlspecialchars( $block->getName() ) ?>[content]"
			          class="mw-ui-input" rows="10"
					  data-header-id="<?= htmlspecialchars( $revisionId ) ?>"
			><?= htmlspecialchars( $content ) ?></textarea>
			<div class="flow-edit-header-controls">
				<input type="submit" class="mw-ui-button mw-ui-constructive"
					   value="<?= wfMessage( 'flow-edit-header-submit' )->escaped() ?>">
			</div>
		</form>
	</div>
</div>
