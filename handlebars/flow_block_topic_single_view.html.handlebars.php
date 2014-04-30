<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'html' => 'Flow\TemplateHelper::html',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-revision-permalink-warning plainlinks">
	'.((LCRun2::ifvar(((is_array($in['revision']) && isset($in['revision']['previousRevisionId'])) ? $in['revision']['previousRevisionId'] : null))) ? '
		'.LCRun2::ch('l10nParse', Array('flow-revision-permalink-warning-post',((is_array($in['revision']) && isset($in['revision']['human_timestamp'])) ? $in['revision']['human_timestamp'] : null),((is_array($in['revision']['rev_view_links']['board']) && isset($in['revision']['rev_view_links']['board']['title'])) ? $in['revision']['rev_view_links']['board']['title'] : null),((is_array($in['revision']['root']) && isset($in['revision']['root']['content'])) ? $in['revision']['root']['content'] : null),((is_array($in['revision']['rev_view_links']['hist']) && isset($in['revision']['rev_view_links']['hist']['url'])) ? $in['revision']['rev_view_links']['hist']['url'] : null),((is_array($in['revision']['rev_view_links']['diff']) && isset($in['revision']['rev_view_links']['diff']['url'])) ? $in['revision']['rev_view_links']['diff']['url'] : null)), 'enc', $cx).'
	' : '
		'.LCRun2::ch('l10nParse', Array('flow-revision-permalink-warning-post-first',((is_array($in['revision']) && isset($in['revision']['human_timestamp'])) ? $in['revision']['human_timestamp'] : null),((is_array($in['revision']['rev_view_links']['board']) && isset($in['revision']['rev_view_links']['board']['title'])) ? $in['revision']['rev_view_links']['board']['title'] : null),((is_array($in['revision']['root']) && isset($in['revision']['root']['content'])) ? $in['revision']['root']['content'] : null),((is_array($in['revision']['rev_view_links']['hist']) && isset($in['revision']['rev_view_links']['hist']['url'])) ? $in['revision']['rev_view_links']['hist']['url'] : null),((is_array($in['revision']['rev_view_links']['diff']) && isset($in['revision']['rev_view_links']['diff']['url'])) ? $in['revision']['rev_view_links']['diff']['url'] : null)), 'enc', $cx).'
	').'
</div>
'.((LCRun2::ifvar(((is_array($in['revision']) && isset($in['revision']['isTopicTitle'])) ? $in['revision']['isTopicTitle'] : null))) ? '
	<div class="flow-topics">
		'.htmlentities(((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null), ENT_QUOTES, 'UTF-8').'
	</div>
' : '
	<div>
		'.LCRun2::ch('html', Array(((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null)), 'enc', $cx).'
	</div>
').'


';
}
?>