<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' => array(),
        'helpers' => array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
            'enablePatrollingLink' => 'Flow\TemplateHelper::enablePatrollingLink',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in)use($sp){return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_patrol_action' => function ($cx, $in, $sp) {return ''.$sp.''.((LCRun3::ifvar($cx, ((isset($in['revision']['rev_view_links']['markPatrolled']) && is_array($in['revision']['rev_view_links'])) ? $in['revision']['rev_view_links']['markPatrolled'] : null))) ? '<div class="patrollink" data-mw="interface">
'.$sp.'        [<a class="mw-ui-quiet"
'.$sp.'           href="'.htmlentities((string)((isset($in['revision']['rev_view_links']['markPatrolled']['url']) && is_array($in['revision']['rev_view_links']['markPatrolled'])) ? $in['revision']['rev_view_links']['markPatrolled']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'           title="'.LCRun3::ch($cx, 'l10n', array(array('flow-mark-revision-patrolled-link-title'),array()), 'encq').'"
'.$sp.'           data-role="patrol">'.LCRun3::ch($cx, 'l10n', array(array('flow-mark-revision-patrolled-link-text'),array()), 'encq').'</a>]
'.$sp.'    </div>
'.$sp.'    '.LCRun3::ch($cx, 'enablePatrollingLink', array(array(),array()), 'encq').'' : '').'';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="flow-board">
'.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '	').'
'.((LCRun3::ifvar($cx, ((isset($in['revision']) && is_array($in)) ? $in['revision'] : null))) ? '		<div class="flow-revision-permalink-warning plainlinks">
'.((LCRun3::ifvar($cx, ((isset($in['revision']['previousRevisionId']) && is_array($in['revision'])) ? $in['revision']['previousRevisionId'] : null))) ? '				'.LCRun3::ch($cx, 'l10nParse', array(array('flow-revision-permalink-warning-post',((isset($in['revision']['human_timestamp']) && is_array($in['revision'])) ? $in['revision']['human_timestamp'] : null),((isset($in['revision']['rev_view_links']['board']['title']) && is_array($in['revision']['rev_view_links']['board'])) ? $in['revision']['rev_view_links']['board']['title'] : null),((isset($in['revision']['root']['content']) && is_array($in['revision']['root'])) ? $in['revision']['root']['content'] : null),((isset($in['revision']['rev_view_links']['hist']['url']) && is_array($in['revision']['rev_view_links']['hist'])) ? $in['revision']['rev_view_links']['hist']['url'] : null),((isset($in['revision']['rev_view_links']['diff']['url']) && is_array($in['revision']['rev_view_links']['diff'])) ? $in['revision']['rev_view_links']['diff']['url'] : null)),array()), 'encq').'
' : '				'.LCRun3::ch($cx, 'l10nParse', array(array('flow-revision-permalink-warning-post-first',((isset($in['revision']['human_timestamp']) && is_array($in['revision'])) ? $in['revision']['human_timestamp'] : null),((isset($in['revision']['rev_view_links']['board']['title']) && is_array($in['revision']['rev_view_links']['board'])) ? $in['revision']['rev_view_links']['board']['title'] : null),((isset($in['revision']['root']['content']) && is_array($in['revision']['root'])) ? $in['revision']['root']['content'] : null),((isset($in['revision']['rev_view_links']['hist']['url']) && is_array($in['revision']['rev_view_links']['hist'])) ? $in['revision']['rev_view_links']['hist']['url'] : null),((isset($in['revision']['rev_view_links']['diff']['url']) && is_array($in['revision']['rev_view_links']['diff'])) ? $in['revision']['rev_view_links']['diff']['url'] : null)),array()), 'encq').'
').'		</div>
		<div class="flow-revision-content mw-parser-output">
			'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['revision']['content']['format']) && is_array($in['revision']['content'])) ? $in['revision']['content']['format'] : null),((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null)),array()), 'encq').'
		</div>

'.LCRun3::p($cx, 'flow_patrol_action', array(array($in),array()), '		').'' : '').'</div>
<div class="flow-single-topic-siderail"></div>
';
}
?>