function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board">
'.$sp.'	<div class="flow-compare-revisions-header plainlinks">
'.$sp.'		'.LR::encq($cx, LR::hbch($cx, 'l10nParse', array(array('flow-compare-revisions-header-header',((isset($in['revision']['new']['rev_view_links']['board']) && is_array($in['revision']['new']['rev_view_links']['board']) && isset($in['revision']['new']['rev_view_links']['board']['title'])) ? $in['revision']['new']['rev_view_links']['board']['title'] : null),((isset($in['revision']['new']['author']) && is_array($in['revision']['new']['author']) && isset($in['revision']['new']['author']['name'])) ? $in['revision']['new']['author']['name'] : null),((isset($in['revision']['new']['rev_view_links']['board']) && is_array($in['revision']['new']['rev_view_links']['board']) && isset($in['revision']['new']['rev_view_links']['board']['url'])) ? $in['revision']['new']['rev_view_links']['board']['url'] : null),((isset($in['revision']['new']['rev_view_links']['hist']) && is_array($in['revision']['new']['rev_view_links']['hist']) && isset($in['revision']['new']['rev_view_links']['hist']['url'])) ? $in['revision']['new']['rev_view_links']['hist']['url'] : null)),array()), 'encq', $in)).'
'.$sp.'	</div>
'.$sp.'	<div class="flow-compare-revisions">
'.$sp.'		'.LR::encq($cx, LR::hbch($cx, 'diffRevision', array(array(((is_array($in) && isset($in['revision'])) ? $in['revision'] : null)),array()), 'encq', $in)).'
'.$sp.'	</div>
'.$sp.'</div>
';}