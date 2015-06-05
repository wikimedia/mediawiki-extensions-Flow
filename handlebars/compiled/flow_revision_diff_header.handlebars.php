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
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array('flow_patrol_diff' => function ($cx, $in, $sp) {return ''.$sp.''.((LCRun3::ifvar($cx, ((isset($in['patrol']) && is_array($in)) ? $in['patrol'] : null))) ? '<div>
'.$sp.'        <span class="patrollink">
'.$sp.'            <a class="mw-ui-quiet"
'.$sp.'               href="'.htmlentities((string)((isset($in['patrol']['url']) && is_array($in['patrol'])) ? $in['patrol']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'               title="'.LCRun3::ch($cx, 'l10n', array(array('flow-mark-diff-patrolled-link-title'),array()), 'encq').'"
'.$sp.'               data-role="patrol">
'.$sp.'                ['.LCRun3::ch($cx, 'l10n', array(array('flow-mark-diff-patrolled-link-text'),array()), 'encq').']
'.$sp.'            </a>
'.$sp.'        </span>
'.$sp.'    </div>' : '').'';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div><a href="'.htmlentities((string)((isset($in['link']) && is_array($in)) ? $in['link'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-diff-revision-link">
	'.LCRun3::ch($cx, 'l10nParse', array(array('flow-compare-revisions-revision-header',((isset($in['timestamp']) && is_array($in)) ? $in['timestamp'] : null),((isset($in['author']) && is_array($in)) ? $in['author'] : null)),array()), 'encq').'
</a></div>
'.((LCRun3::ifvar($cx, ((isset($in['previous']) && is_array($in)) ? $in['previous'] : null))) ? '	<div><a href="'.htmlentities((string)((isset($in['previous']) && is_array($in)) ? $in['previous'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('flow-previous-diff'),array()), 'encq').'</a></div>
' : '').''.((LCRun3::ifvar($cx, ((isset($in['next']) && is_array($in)) ? $in['next'] : null))) ? '	<div><a href="'.htmlentities((string)((isset($in['next']) && is_array($in)) ? $in['next'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('flow-next-diff'),array()), 'encq').'</a></div>
' : '').''.LCRun3::p($cx, 'flow_patrol_diff', array(array($in),array())).'';
}
?>