<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'block' => 'Flow\TemplateHelper::block',
),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-component" data-flow-component="board" data-flow-id="'.htmlentities(((is_array($in) && isset($in['workflow'])) ? $in['workflow'] : null), ENT_QUOTES, 'UTF-8').'">
	'.LCRun2::sec(((is_array($in) && isset($in['blocks'])) ? $in['blocks'] : null), $cx, $in, true, function($cx, $in) {return '
		'.LCRun2::ch('block', Array($in), 'enc', $cx).'
	';}).'
</div>';
}
?>