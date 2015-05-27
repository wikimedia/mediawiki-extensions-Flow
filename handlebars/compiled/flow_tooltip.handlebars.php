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
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array(),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="'.htmlentities((string)((isset($in['extraClass']) && is_array($in)) ? $in['extraClass'] : null), ENT_QUOTES, 'UTF-8').' flow-ui-tooltip '.htmlentities((string)((isset($in['contextClass']) && is_array($in)) ? $in['contextClass'] : null), ENT_QUOTES, 'UTF-8').' '.htmlentities((string)((isset($in['positionClass']) && is_array($in)) ? $in['positionClass'] : null), ENT_QUOTES, 'UTF-8').' '.htmlentities((string)((isset($in['blockClass']) && is_array($in)) ? $in['blockClass'] : null), ENT_QUOTES, 'UTF-8').' plainlinks">'.LCRun3::ch($cx, 'html', array(array(((isset($in['content']) && is_array($in)) ? $in['content'] : null)),array()), 'encq').'<span class="flow-ui-tooltip-triangle"></span>
</div>
';
}
?>