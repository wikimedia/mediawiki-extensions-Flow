function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board">
'.$sp.'
'.$sp.''.LR::sec($cx, ((is_array($in) && isset($in['roots'])) ? $in['roots'] : null), null, $in, true, function($cx, $in)use($sp){return ''.LR::hbbch($cx, 'eachPost', array(array((isset($cx['sp_vars']['root']) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_edit_topic_title', array(array($in),array()),0, '			').'';}).'';}).'</div>
';}