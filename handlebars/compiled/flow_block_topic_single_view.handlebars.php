<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-revision-permalink-warning plainlinks">
	'.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['previousRevisionId'])) ? $in['revision']['previousRevisionId'] : null))) ? '
		'.LCRun3::ch($cx, 'l10nParse', Array('flow-revision-permalink-warning-post',((is_array($in['revision']) && isset($in['revision']['human_timestamp'])) ? $in['revision']['human_timestamp'] : null),((is_array($in['revision']['rev_view_links']['board']) && isset($in['revision']['rev_view_links']['board']['title'])) ? $in['revision']['rev_view_links']['board']['title'] : null),((is_array($in['revision']['root']) && isset($in['revision']['root']['content'])) ? $in['revision']['root']['content'] : null),((is_array($in['revision']['rev_view_links']['hist']) && isset($in['revision']['rev_view_links']['hist']['url'])) ? $in['revision']['rev_view_links']['hist']['url'] : null),((is_array($in['revision']['rev_view_links']['diff']) && isset($in['revision']['rev_view_links']['diff']['url'])) ? $in['revision']['rev_view_links']['diff']['url'] : null)), 'encq').'
	' : '
		'.LCRun3::ch($cx, 'l10nParse', Array('flow-revision-permalink-warning-post-first',((is_array($in['revision']) && isset($in['revision']['human_timestamp'])) ? $in['revision']['human_timestamp'] : null),((is_array($in['revision']['rev_view_links']['board']) && isset($in['revision']['rev_view_links']['board']['title'])) ? $in['revision']['rev_view_links']['board']['title'] : null),((is_array($in['revision']['root']) && isset($in['revision']['root']['content'])) ? $in['revision']['root']['content'] : null),((is_array($in['revision']['rev_view_links']['hist']) && isset($in['revision']['rev_view_links']['hist']['url'])) ? $in['revision']['rev_view_links']['hist']['url'] : null),((is_array($in['revision']['rev_view_links']['diff']) && isset($in['revision']['rev_view_links']['diff']['url'])) ? $in['revision']['rev_view_links']['diff']['url'] : null)), 'encq').'
	').'
</div>
<div'.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['isTopicTitle'])) ? $in['revision']['isTopicTitle'] : null))) ? ' class="flow-topics"' : '').'>
	'.LCRun3::ch($cx, 'escapeContent', Array(((is_array($in['revision']) && isset($in['revision']['contentFormat'])) ? $in['revision']['contentFormat'] : null),((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null)), 'encq').'
</div>


';
}
?>