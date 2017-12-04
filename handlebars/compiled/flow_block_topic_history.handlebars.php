function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board-history">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()),0, '	').'
'.$sp.'	<div class="flow-topic-histories">
'.$sp.'		'.((LR::ifvar($cx, ((is_array($in) && isset($in['navbar'])) ? $in['navbar'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'html', array(array(((is_array($in) && isset($in['navbar'])) ? $in['navbar'] : null)),array()), 'encq', $in)).'' : '').'
'.$sp.'
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, ((is_array($in) && isset($in['revisions'])) ? $in['revisions'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::p($cx, 'flow_history_line', array(array($in),array()),0).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'
'.$sp.'		'.((LR::ifvar($cx, ((is_array($in) && isset($in['navbar'])) ? $in['navbar'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'html', array(array(((is_array($in) && isset($in['navbar'])) ? $in['navbar'] : null)),array()), 'encq', $in)).'' : '').'
'.$sp.'	</div>
'.$sp.'</div>
';}