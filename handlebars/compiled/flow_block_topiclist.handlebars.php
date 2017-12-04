function ($cx, $in, $sp) {return ''.$sp.''.LR::p($cx, 'flow_board_navigation', array(array($in),array()),0).'
'.$sp.'<div class="flow-board" data-flow-sortby="'.LR::encq($cx, ((is_array($in) && isset($in['sortby'])) ? $in['sortby'] : null)).'">
'.$sp.'	<div class="flow-newtopic-container">
'.$sp.'		<div class="flow-nojs">
'.$sp.'			<a class="mw-ui-input mw-ui-input-large flow-ui-input-replacement-anchor"
'.$sp.'				href="'.LR::encq($cx, ((isset($in['links']) && is_array($in['links']) && isset($in['links']['newtopic'])) ? $in['links']['newtopic'] : null)).'">'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-newtopic-start-placeholder'),array()), 'encq', $in)).'</a>
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<div class="flow-js">
'.$sp.''.LR::p($cx, 'flow_newtopic_form', array(array($in),array('isOnFlowBoard'=>true)),0, '			').'		</div>
'.$sp.'	</div>
'.$sp.'
'.$sp.'	<div class="flow-topics">
'.$sp.''.LR::p($cx, 'flow_topiclist_loop', array(array($in),array()),0, '		').'
'.$sp.''.LR::p($cx, 'flow_load_more', array(array($in),array('loadMoreApiHandler'=>'loadMoreTopics','loadMoreTarget'=>'window','loadMoreContainer'=>'< .flow-topics','loadMoreTemplate'=>'flow_topiclist_loop.partial','loadMoreObject'=>((isset($in['links']['pagination']) && is_array($in['links']['pagination']) && isset($in['links']['pagination']['fwd'])) ? $in['links']['pagination']['fwd'] : null))),0, '		').'	</div>
'.$sp.'</div>
';}