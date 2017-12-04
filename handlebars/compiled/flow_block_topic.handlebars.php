function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board">
'.$sp.'	<div class="flow-topics">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()),0, '		').'
'.$sp.''.LR::p($cx, 'flow_topiclist_loop', array(array($in),array()),0, '		').'	</div>
'.$sp.'</div>
';}