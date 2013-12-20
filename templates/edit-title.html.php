<div class="flow-topic-container flow-topic-full">
	<div class="flow-edit-title-form flow-element-container">
		<form method="POST" action="<?= $editTitleUrl ?>">
			<?= $this->render( 'flow:block-errors.html.php', compact( 'block' ) ) ?>
			<input type="hidden" name="wpEditToken" value="<?= htmlspecialchars( $editToken ) ?>">
			<input name="<?= htmlspecialchars( $block->getName() ) ?>[content]"
			       class="flow-edit-title-textbox mw-ui-input"
				   value="<?= htmlspecialchars( $content ) ?>">
			<div class="flow-edit-title-controls">
				<input type="submit" class="mw-ui-button mw-ui-constructive"
				       value="<?= wfMessage( 'flow-edit-title-submit' )->escaped() ?>">
			</div>
		</form>
	</div>
</div>

