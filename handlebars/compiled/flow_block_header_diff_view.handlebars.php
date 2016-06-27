<?php use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'diffRevision' => 'Flow\TemplateHelper::diffRevision',
);
    $partials = array();
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'lambda' => false,
            'mustlok' => false,
            'mustlam' => false,
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
        'runtime' => '\LightnCandy\Runtime',
    );
    
    return '<div class="flow-board">
	<div class="flow-compare-revisions-header plainlinks">
		'.LR::encq($cx, LR::hbch($cx, 'l10nParse', array(array('flow-compare-revisions-header-header',((isset($in['revision']['new']['rev_view_links']['board']['title']) && is_array($in['revision']['new']['rev_view_links']['board'])) ? $in['revision']['new']['rev_view_links']['board']['title'] : null),((isset($in['revision']['new']['author']['name']) && is_array($in['revision']['new']['author'])) ? $in['revision']['new']['author']['name'] : null),((isset($in['revision']['new']['rev_view_links']['board']['url']) && is_array($in['revision']['new']['rev_view_links']['board'])) ? $in['revision']['new']['rev_view_links']['board']['url'] : null),((isset($in['revision']['new']['rev_view_links']['hist']['url']) && is_array($in['revision']['new']['rev_view_links']['hist'])) ? $in['revision']['new']['rev_view_links']['hist']['url'] : null)),array()), 'encq', $in)).'
	</div>
	<div class="flow-compare-revisions">
		'.LR::encq($cx, LR::hbch($cx, 'diffRevision', array(array(((isset($in['revision']) && is_array($in)) ? $in['revision'] : null)),array()), 'encq', $in)).'
	</div>
</div>
';
};
?>