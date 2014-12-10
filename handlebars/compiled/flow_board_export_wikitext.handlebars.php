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
        'helpers' => Array(),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<h2>'.htmlentities((string)((isset($in['title']) && is_array($in)) ? $in['title'] : null), ENT_QUOTES, 'UTF-8').'</h2>
<pre>'.htmlentities((string)((isset($in['wikitext']) && is_array($in)) ? $in['wikitext'] : null), ENT_QUOTES, 'UTF-8').'</pre>
';
}
?>