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
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'enablePatrollingLink' => 'Flow\TemplateHelper::enablePatrollingLink',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array('flow_patrol_diff' => function ($cx, $in, $sp) {return ''.$sp.''.((LCRun3::ifvar($cx, ((isset($in['revision']['rev_view_links']['markPatrolled']) && is_array($in['revision']['rev_view_links'])) ? $in['revision']['rev_view_links']['markPatrolled'] : null))) ? '<div>
'.$sp.'        <span class="patrollink">
'.$sp.'            [<a class="mw-ui-quiet"
'.$sp.'               href="'.htmlentities((string)((isset($in['revision']['rev_view_links']['markPatrolled']['url']) && is_array($in['revision']['rev_view_links']['markPatrolled'])) ? $in['revision']['rev_view_links']['markPatrolled']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'               title="'.LCRun3::ch($cx, 'l10n', array(array('flow-mark-diff-patrolled-link-title'),array()), 'encq').'"
'.$sp.'               data-role="patrol">'.LCRun3::ch($cx, 'l10n', array(array('flow-mark-diff-patrolled-link-text'),array()), 'encq').'</a>]
'.$sp.'        </span>
'.$sp.'    </div>
'.$sp.'    '.LCRun3::ch($cx, 'enablePatrollingLink', array(array(),array()), 'encq').'' : '').'';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div><a href="'.htmlentities((string)((isset($in['revision']['rev_view_links']['single-view']['url']) && is_array($in['revision']['rev_view_links']['single-view'])) ? $in['revision']['rev_view_links']['single-view']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-diff-revision-link">
	'.LCRun3::ch($cx, 'l10nParse', array(array('flow-compare-revisions-revision-header',((isset($in['revision']['human_timestamp']) && is_array($in['revision'])) ? $in['revision']['human_timestamp'] : null),((isset($in['revision']['author']['name']) && is_array($in['revision']['author'])) ? $in['revision']['author']['name'] : null)),array()), 'encq').'
</a></div>
'.((LCRun3::ifvar($cx, ((isset($in['links']['previous']) && is_array($in['links'])) ? $in['links']['previous'] : null))) ? '	<div><a href="'.htmlentities((string)((isset($in['links']['previous']) && is_array($in['links'])) ? $in['links']['previous'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('flow-previous-diff'),array()), 'encq').'</a></div>
' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['next']) && is_array($in['links'])) ? $in['links']['next'] : null))) ? '	<div><a href="'.htmlentities((string)((isset($in['links']['next']) && is_array($in['links'])) ? $in['links']['next'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('flow-next-diff'),array()), 'encq').'</a>	</div>
' : '').''.LCRun3::p($cx, 'flow_patrol_diff', array(array($in),array())).'';
}
?>