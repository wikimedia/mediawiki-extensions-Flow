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
        'helpers' => Array(            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<ul>
'.LCRun3::sec($cx, ((is_array($in) && isset($in['search'])) ? $in['search'] : null), $in, true, function($cx, $in) {return '
	<li>
		<a href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'">
			'.LCRun3::ch($cx, 'escapeContent', Array(Array(((is_array($in['content']) && isset($in['content']['format'])) ? $in['content']['format'] : null),((is_array($in['content']) && isset($in['content']['content'])) ? $in['content']['content'] : null)),Array()), 'encq').'
		</a>
	</li>
';}).'
<ul>';
}
?>