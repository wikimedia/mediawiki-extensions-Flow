<?php
// submit button text will be different if there's a more recent change already
// Checking validationErrors isn't a great solution, need to figure out how to
// construct this such
if ( $this->isEditConflict->__raw() ) {
	$submitMessage = "flow-edit-title-submit-overwrite";
	$submitClass = "mw-ui-button mw-ui-destructive";
} else {
	$submitMessage = "flow-edit-title-submit";
	$submitClass = "mw-ui-button mw-ui-constructive";
}
?>
<div class="flow-topic-container flow-topic-full">
	<div class="flow-edit-title-form flow-element-container">
		<form method="POST" action="<?php echo $this->editTitleUrl->escaped() ?>">
			<?php echo $this->validationErrors->escaped() ?>
			<?php /* echo $this->antispam()->formField()->escaped() // not implemented yet */ ?>
			<input type="hidden" name="wpEditToken" value="<?php echo $this->editToken->escaped() ?>">
			<input type="hidden"
			       name="<?php echo $this->name->escaped() ?>_prev_revision"
			       value="<?php echo $this->revisionId->escaped() ?>">
			<input name="<?php echo $this->name->escaped() ?>_content"
			       class="flow-edit-title-textbox mw-ui-input"
			       value="<?php echo $this->wikiTextContent->escaped() ?>">
			<div class="flow-edit-title-controls">
				<div class="flow-terms-of-use plainlinks">
					<?php echo wfMessage( "flow-terms-of-use-reply" )->parse() ?>
				</div>
				<input type="submit" class="<?php echo $this->escape( $submitClass )->escaped() ?>"
				       value="<?php echo wfMessage( $submitMessage )->escaped() ?>">
				<div class="ui-helper-clearfix"></div>
			</div>
		</form>
	</div>
</div>
