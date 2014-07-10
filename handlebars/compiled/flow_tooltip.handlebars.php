<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'html' => 'Flow\TemplateHelper::htmlHelper',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="'.htmlentities(((is_array($in) && isset($in['extraClass'])) ? $in['extraClass'] : null), ENT_QUOTES, 'UTF-8').' flow-ui-tooltip '.htmlentities(((is_array($in) && isset($in['contextClass'])) ? $in['contextClass'] : null), ENT_QUOTES, 'UTF-8').' '.htmlentities(((is_array($in) && isset($in['positionClass'])) ? $in['positionClass'] : null), ENT_QUOTES, 'UTF-8').' '.htmlentities(((is_array($in) && isset($in['blockClass'])) ? $in['blockClass'] : null), ENT_QUOTES, 'UTF-8').' plainlinks">'.LCRun3::ch($cx, 'html', Array(Array(((is_array($in) && isset($in['content'])) ? $in['content'] : null)),Array()), 'encq').'<span class="flow-ui-tooltip-triangle"></span>
</div>
';
}
?>