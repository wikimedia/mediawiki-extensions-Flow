<?php use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in = null, $options = null) {
    $helpers = array(            'html' => 'Flow\TemplateHelper::htmlHelper',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'diffRevision' => 'Flow\TemplateHelper::diffRevision',
);
    $partials = array('flow_errors' => function ($cx, $in, $sp) {$inary=is_array($in);return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, (isset($cx['sp_vars']['root']['errors']) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors flow-errorbox mw-message-box mw-message-box-error">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, (isset($cx['sp_vars']['root']['errors']) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){$inary=is_array($in);return '				<li>'.LR::encq($cx, LR::hbch($cx, 'html', array(array((($inary && isset($in['message'])) ? $in['message'] : null)),array()), 'encq', $in)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';});
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'jslen' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'lambda' => false,
            'mustlok' => false,
            'mustlam' => false,
            'mustsec' => false,
            'echo' => false,
            'partnc' => false,
            'knohlp' => false,
            'debug' => isset($options['debug']) ? $options['debug'] : 1,
        ),
        'constants' => array(),
        'helpers' => isset($options['helpers']) ? array_merge($helpers, $options['helpers']) : $helpers,
        'partials' => isset($options['partials']) ? array_merge($partials, $options['partials']) : $partials,
        'scopes' => array(),
        'sp_vars' => isset($options['data']) ? array_merge(array('root' => $in), $options['data']) : array('root' => $in),
        'blparam' => array(),
        'partialid' => 0,
        'runtime' => '\LightnCandy\Runtime',
    );
    
    $inary=is_array($in);
    return '<div class="flow-board">
'.LR::p($cx, 'flow_errors', array(array($in),array()),0, '	').'	<div class="flow-compare-revisions-header plainlinks">
		'.LR::encq($cx, LR::hbch($cx, 'l10nParse', array(array('flow-compare-revisions-header-postsummary',((isset($in['revision']['new']['rev_view_links']['board']) && is_array($in['revision']['new']['rev_view_links']['board']) && isset($in['revision']['new']['rev_view_links']['board']['title'])) ? $in['revision']['new']['rev_view_links']['board']['title'] : null),((isset($in['revision']['new']['properties']) && is_array($in['revision']['new']['properties']) && isset($in['revision']['new']['properties']['post-of-summary'])) ? $in['revision']['new']['properties']['post-of-summary'] : null),((isset($in['revision']['new']['rev_view_links']['board']) && is_array($in['revision']['new']['rev_view_links']['board']) && isset($in['revision']['new']['rev_view_links']['board']['url'])) ? $in['revision']['new']['rev_view_links']['board']['url'] : null),((isset($in['revision']['new']['rev_view_links']['root']) && is_array($in['revision']['new']['rev_view_links']['root']) && isset($in['revision']['new']['rev_view_links']['root']['url'])) ? $in['revision']['new']['rev_view_links']['root']['url'] : null),((isset($in['revision']['new']['rev_view_links']['hist']) && is_array($in['revision']['new']['rev_view_links']['hist']) && isset($in['revision']['new']['rev_view_links']['hist']['url'])) ? $in['revision']['new']['rev_view_links']['hist']['url'] : null)),array()), 'encq', $in)).'
	</div>
	<div class="flow-compare-revisions">
		'.LR::encq($cx, LR::hbch($cx, 'diffRevision', array(array((($inary && isset($in['revision'])) ? $in['revision'] : null)),array()), 'encq', $in)).'
	</div>
</div>
';
};