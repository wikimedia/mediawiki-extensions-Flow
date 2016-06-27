<?php use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'html' => 'Flow\TemplateHelper::htmlHelper',
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
    
    return '<div class="'.LR::encq($cx, ((isset($in['extraClass']) && is_array($in)) ? $in['extraClass'] : null)).' flow-ui-tooltip '.LR::encq($cx, ((isset($in['contextClass']) && is_array($in)) ? $in['contextClass'] : null)).' '.LR::encq($cx, ((isset($in['positionClass']) && is_array($in)) ? $in['positionClass'] : null)).' '.LR::encq($cx, ((isset($in['blockClass']) && is_array($in)) ? $in['blockClass'] : null)).' plainlinks">'.LR::encq($cx, LR::hbch($cx, 'html', array(array(((isset($in['content']) && is_array($in)) ? $in['content'] : null)),array()), 'encq', $in)).'<span class="flow-ui-tooltip-triangle"></span>
</div>
';
};
?>