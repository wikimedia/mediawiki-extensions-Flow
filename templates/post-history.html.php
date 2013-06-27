<?php

echo '<ul>';
foreach ( $history as $revision ) {
	echo '<li>'
		. $revision->getRevisionId() . ' : '
		. $revision->getUserText() . ' : '
		. $revision->getComment()
		. '</li>';
}
echo '</ul>';
