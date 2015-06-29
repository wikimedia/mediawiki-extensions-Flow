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
        'helpers' => array(            'html' => 'Flow\TemplateHelper::htmlHelper',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'diffRevision' => 'Flow\TemplateHelper::diffRevision',
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
';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="flow-board">
'.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '	').'	<div class="flow-compare-revisions-header plainlinks">
		'.LCRun3::ch($cx, 'l10nParse', array(array('flow-compare-revisions-header-postsummary',((isset($in['revision']['new']['rev_view_links']['board']['title']) && is_array($in['revision']['new']['rev_view_links']['board'])) ? $in['revision']['new']['rev_view_links']['board']['title'] : null),((isset($in['revision']['new']['properties']['post-of-summary']) && is_array($in['revision']['new']['properties'])) ? $in['revision']['new']['properties']['post-of-summary'] : null),((isset($in['revision']['new']['rev_view_links']['board']['url']) && is_array($in['revision']['new']['rev_view_links']['board'])) ? $in['revision']['new']['rev_view_links']['board']['url'] : null),((isset($in['revision']['new']['rev_view_links']['root']['url']) && is_array($in['revision']['new']['rev_view_links']['root'])) ? $in['revision']['new']['rev_view_links']['root']['url'] : null),((isset($in['revision']['new']['rev_view_links']['hist']['url']) && is_array($in['revision']['new']['rev_view_links']['hist'])) ? $in['revision']['new']['rev_view_links']['hist']['url'] : null)),array()), 'encq').'
	</div>
	<div class="flow-compare-revisions">
		'.LCRun3::ch($cx, 'diffRevision', array(array(((isset($in['revision']) && is_array($in)) ? $in['revision'] : null)),array()), 'encq').'
	</div>
</div>
';
}
?>