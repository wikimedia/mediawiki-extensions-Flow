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
        'helpers' => Array(            'block' => 'Flow\TemplateHelper::block',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return ''.LCRun3::sec($cx, ((isset($in['blocks']) && is_array($in)) ? $in['blocks'] : null), $in, true, function($cx, $in) {return '
	'.LCRun3::ch($cx, 'block', Array(Array($in),Array()), 'encq').'
';}).'';
}
?>