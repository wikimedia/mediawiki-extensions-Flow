<div class="flow-compare-revisions-header plainlinks">
	<?php echo $headerMsg->parse(); ?>
</div>
<div class="flow-compare-revisions">
	<?php echo $differenceEngine->getDiff( $oldRevision, $newRevision ); ?>
</div>
