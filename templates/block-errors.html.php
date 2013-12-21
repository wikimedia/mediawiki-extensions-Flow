<?php
/**
 * Variables passed in:
 *
 *	$block - Instance of Flow\Block\Block
 */
if ( $block->hasErrors() ) {
	$errors = array();
	foreach ( $block->getErrors() as $error ) {
		$errors[] = $block->getErrorMessage( $error )->escaped();
	}
	echo "<ul><li>", implode( "</li><li>", $errors ), "</li></ul>";
}
