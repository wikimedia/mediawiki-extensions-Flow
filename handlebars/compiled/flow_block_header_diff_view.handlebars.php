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
        'helpers' => array(            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'diffRevision' => 'Flow\TemplateHelper::diffRevision',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array(),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="flow-board">
	<div class="flow-compare-revisions-header plainlinks">
		'.LCRun3::ch($cx, 'l10nParse', array(array('flow-compare-revisions-header-header',((isset($in['revision']['new']['rev_view_links']['board']['title']) && is_array($in['revision']['new']['rev_view_links']['board'])) ? $in['revision']['new']['rev_view_links']['board']['title'] : null),((isset($in['revision']['new']['author']['name']) && is_array($in['revision']['new']['author'])) ? $in['revision']['new']['author']['name'] : null),((isset($in['revision']['new']['rev_view_links']['board']['url']) && is_array($in['revision']['new']['rev_view_links']['board'])) ? $in['revision']['new']['rev_view_links']['board']['url'] : null),((isset($in['revision']['new']['rev_view_links']['hist']['url']) && is_array($in['revision']['new']['rev_view_links']['hist'])) ? $in['revision']['new']['rev_view_links']['hist']['url'] : null)),array()), 'encq').'
	</div>
	<div class="flow-compare-revisions">
		'.LCRun3::ch($cx, 'diffRevision', array(array(((isset($in['revision']) && is_array($in)) ? $in['revision'] : null)),array()), 'encq').'
	</div>
</div>
';
}
?>