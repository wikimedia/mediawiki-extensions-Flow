<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'diffRevision' => 'Flow\TemplateHelper::diffRevision',
),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-compare-revisions-header plainlinks">
	'.LCRun2::ch('l10nParse', Array('flow-compare-revisions-header-header',((is_array($in['revision']['new']['rev_view_links']['board']) && isset($in['revision']['new']['rev_view_links']['board']['title'])) ? $in['revision']['new']['rev_view_links']['board']['title'] : null),((is_array($in['revision']['new']['author']) && isset($in['revision']['new']['author']['name'])) ? $in['revision']['new']['author']['name'] : null),((is_array($in['revision']['new']['rev_view_links']['board']) && isset($in['revision']['new']['rev_view_links']['board']['url'])) ? $in['revision']['new']['rev_view_links']['board']['url'] : null),((is_array($in['revision']['new']['rev_view_links']['hist']) && isset($in['revision']['new']['rev_view_links']['hist']['url'])) ? $in['revision']['new']['rev_view_links']['hist']['url'] : null)), 'enc', $cx).'
</div>
<div class="flow-compare-revisions">
	'.LCRun2::ch('diffRevision', Array(((is_array($in['revision']) && isset($in['revision']['diff_content'])) ? $in['revision']['diff_content'] : null),((is_array($in['revision']['old']) && isset($in['revision']['old']['human_timestamp'])) ? $in['revision']['old']['human_timestamp'] : null),((is_array($in['revision']['new']) && isset($in['revision']['new']['human_timestamp'])) ? $in['revision']['new']['human_timestamp'] : null),((is_array($in['revision']['old']['author']) && isset($in['revision']['old']['author']['name'])) ? $in['revision']['old']['author']['name'] : null),((is_array($in['revision']['new']['author']) && isset($in['revision']['new']['author']['name'])) ? $in['revision']['new']['author']['name'] : null),((is_array($in['revision']['old']['rev_view_links']['single-view']) && isset($in['revision']['old']['rev_view_links']['single-view']['url'])) ? $in['revision']['old']['rev_view_links']['single-view']['url'] : null),((is_array($in['revision']['new']['rev_view_links']['single-view']) && isset($in['revision']['new']['rev_view_links']['single-view']['url'])) ? $in['revision']['new']['rev_view_links']['single-view']['url'] : null)), 'enc', $cx).'
</div>
';
}
?>