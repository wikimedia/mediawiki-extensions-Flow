<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'mustsec' => false,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'html' => 'Flow\TemplateHelper::htmlHelper',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="'.htmlentities((string)((isset($in['extraClass']) && is_array($in)) ? $in['extraClass'] : null), ENT_QUOTES, 'UTF-8').' flow-ui-tooltip '.htmlentities((string)((isset($in['contextClass']) && is_array($in)) ? $in['contextClass'] : null), ENT_QUOTES, 'UTF-8').' '.htmlentities((string)((isset($in['positionClass']) && is_array($in)) ? $in['positionClass'] : null), ENT_QUOTES, 'UTF-8').' '.htmlentities((string)((isset($in['blockClass']) && is_array($in)) ? $in['blockClass'] : null), ENT_QUOTES, 'UTF-8').' plainlinks">'.LCRun3::ch($cx, 'html', Array(Array(((isset($in['content']) && is_array($in)) ? $in['content'] : null)),Array()), 'encq').'<span class="flow-ui-tooltip-triangle"></span>
</div>
';
}
?>
