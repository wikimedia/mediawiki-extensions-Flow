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
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-export">
	<h3>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-some-message',((isset($in['title']) && is_array($in)) ? $in['title'] : null)),Array()), 'encq').'</h3>
	<p class="exported-wikitext">'.htmlentities((string)((isset($in['wikitext']) && is_array($in)) ? $in['wikitext'] : null), ENT_QUOTES, 'UTF-8').'</p>
</div>
';
}
?>