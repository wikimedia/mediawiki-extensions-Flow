<?php
echo Html::rawElement(
	'div',
	array(
		'class' => 'flow-compare-revisions-header plainlinks',
	),
	$headerMsg->parse()
);

?>
<div class="flow-compare-revisions">
	<?php
		echo $differenceEngine->getDiff( $getRevisionHeader( $oldRevision ), $getRevisionHeader( $newRevision ) );
	?>
</div>
