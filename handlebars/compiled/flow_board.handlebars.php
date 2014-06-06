<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'block' => 'Flow\TemplateHelper::block',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-component" data-flow-component="board" data-flow-id="'.htmlentities(((is_array($in) && isset($in['workflow'])) ? $in['workflow'] : null), ENT_QUOTES, 'UTF-8').'">
	'.LCRun3::sec($cx, ((is_array($in) && isset($in['blocks'])) ? $in['blocks'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::ch($cx, 'block', Array($in), 'encq').'
	';}).'
</div>';
}
?>