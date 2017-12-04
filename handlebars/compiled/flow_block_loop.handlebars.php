function ($cx, $in, $sp) {return ''.$sp.''.LR::sec($cx, ((is_array($in) && isset($in['blocks'])) ? $in['blocks'] : null), null, $in, true, function($cx, $in)use($sp){return '	'.LR::encq($cx, LR::hbch($cx, 'block', array(array($in),array()), 'encq', $in)).'
'.$sp.'';}).'<div class="flow-ui-load-overlay"></div>
'.$sp.'<div style="clear: both"></div>
';}