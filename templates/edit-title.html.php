<div class="flow-topic-container flow-topic-full">
	<div class="flow-edit-title-form flow-element-container">
		<form method="POST" action="<?= $this->editTitleUrl->escaped() ?>">
			<?= $this->errors()->block( $this->block )->escaped() ?>
			<input type="hidden" name="wpEditToken" value="<?= $this->editToken->escaped() ?>">
			<input name="<?= $this->block->getName()->escaped() ?>[content]" value="<?= $this->content->escaped() ?>"
			       class="flow-edit-title-textbox mw-ui-input">
			<div class="flow-edit-title-controls">
				<input type="submit" class="mw-ui-button mw-ui-constructive"
				       value="<?= wfMessage( 'flow-edit-title-submit' )->escaped() ?>">
			</div>
		</form>
	</div>
</div>

