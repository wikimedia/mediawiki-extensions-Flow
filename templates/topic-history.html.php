<?php

echo '<h2>' . wfMessage( 'flow-topic-history' )->escaped() . '</h2>';
echo '<ul class="flow-topic-history">';
foreach ( $history as $revision ) {
	echo '<li>'
		. $revision->getRevisionId()->getHex() . ' : '
		. $revision->getUserText() . ' : '
		. wfMessage( $revision->getChangeType() )->parse()
		. '</li>';
}
echo '</ul>';
