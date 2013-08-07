<?php

echo wfMessage( 'flow-post-history' )->escaped();
echo '<ul>';
foreach ( $history as $revision ) {
	echo '<li>'
		. $revision->getRevisionId()->getHex() . ' : '
		. $revision->getUserText() . ' : '
		. $revision->getComment()
		. '</li>';
}
echo '</ul>';
