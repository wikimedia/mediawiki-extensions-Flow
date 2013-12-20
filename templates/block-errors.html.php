<?php if ( $block->hasErrors() ): ?>
	<ul>
	<?php foreach ( $block->getErrors() as $error ): ?>
		<li><?= $block->getErrorMessage( $error )->escaped() ?></li>
	<?php endforeach ?>
	</ul>
<?php endif ?>
