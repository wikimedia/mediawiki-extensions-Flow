<div class="flow-topic-container flow-topic-full">
	<div class="flow-edit-title-form flow-element-container">
		<form method="POST" action="<?php echo $this->editTitleUrl->escaped() ?>">
			<?php echo $this->errors()->block( $this->block )->escaped() ?>
			<input type="hidden" name="wpEditToken" value="<?php echo $this->editToken->escaped() ?>">
			<input name="<?php echo $this->block->getName()->escaped() ?>[content]" value="<?php echo $this->content->escaped() ?>"
			       class="flow-edit-title-textbox mw-ui-input">
			<div class="flow-edit-title-controls">
				<input type="submit" class="mw-ui-button mw-ui-constructive"
				       value="<?php echo wfMessage( 'flow-edit-title-submit' )->escaped() ?>">
			</div>
		</form>
	</div>
</div>
