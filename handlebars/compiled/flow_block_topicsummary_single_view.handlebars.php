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
        'helpers' => array(            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array(),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return '<div class="flow-revision-permalink-warning plainlinks">
'.((LCRun3::ifvar($cx, ((isset($in['revision']['previousRevisionId']) && is_array($in['revision'])) ? $in['revision']['previousRevisionId'] : null))) ? '		'.LCRun3::ch($cx, 'l10nParse', array(array('flow-revision-permalink-warning-postsummary',((isset($in['revision']['human_timestamp']) && is_array($in['revision'])) ? $in['revision']['human_timestamp'] : null),((isset($in['revision']['rev_view_links']['board']['title']) && is_array($in['revision']['rev_view_links']['board'])) ? $in['revision']['rev_view_links']['board']['title'] : null),((isset($in['revision']['root']['content']) && is_array($in['revision']['root'])) ? $in['revision']['root']['content'] : null),((isset($in['revision']['rev_view_links']['hist']['url']) && is_array($in['revision']['rev_view_links']['hist'])) ? $in['revision']['rev_view_links']['hist']['url'] : null),((isset($in['revision']['rev_view_links']['diff']['url']) && is_array($in['revision']['rev_view_links']['diff'])) ? $in['revision']['rev_view_links']['diff']['url'] : null)),array()), 'encq').'
' : '		'.LCRun3::ch($cx, 'l10nParse', array(array('flow-revision-permalink-warning-postsummary-first',((isset($in['evision']['human_timestamp']) && is_array($in['evision'])) ? $in['evision']['human_timestamp'] : null),((isset($in['revision']['rev_view_links']['board']['title']) && is_array($in['revision']['rev_view_links']['board'])) ? $in['revision']['rev_view_links']['board']['title'] : null),((isset($in['revision']['root']['content']) && is_array($in['revision']['root'])) ? $in['revision']['root']['content'] : null),((isset($in['revision']['rev_view_links']['hist']['url']) && is_array($in['revision']['rev_view_links']['hist'])) ? $in['revision']['rev_view_links']['hist']['url'] : null),((isset($in['revision']['rev_view_links']['diff']['url']) && is_array($in['revision']['rev_view_links']['diff'])) ? $in['revision']['rev_view_links']['diff']['url'] : null)),array()), 'encq').'
').'</div>
'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['revision']['content']['format']) && is_array($in['revision']['content'])) ? $in['revision']['content']['format'] : null),((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null)),array()), 'encq').'
';
}
?>