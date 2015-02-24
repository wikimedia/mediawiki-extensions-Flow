<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'mustsec' => false,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' => array(),
        'helpers' => array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array(),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return '<div><a href="'.htmlentities((string)((isset($in['link']) && is_array($in)) ? $in['link'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-diff-revision-link">
	'.LCRun3::ch($cx, 'l10nParse', array(array('flow-compare-revisions-revision-header',((isset($in['timestamp']) && is_array($in)) ? $in['timestamp'] : null),((isset($in['author']) && is_array($in)) ? $in['author'] : null)),array()), 'encq').'
</a></div>
'.((LCRun3::ifvar($cx, ((isset($in['previous']) && is_array($in)) ? $in['previous'] : null))) ? '	<div><a href="'.htmlentities((string)((isset($in['previous']) && is_array($in)) ? $in['previous'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('flow-previous-diff'),array()), 'encq').'</a></div>
' : '').''.((LCRun3::ifvar($cx, ((isset($in['next']) && is_array($in)) ? $in['next'] : null))) ? '	<div><a href="'.htmlentities((string)((isset($in['next']) && is_array($in)) ? $in['next'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('flow-next-diff'),array()), 'encq').'</a></div>
' : '').'';
}
?>