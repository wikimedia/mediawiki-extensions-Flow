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
	<p>Total results: '.htmlentities(((is_array($in['search']) && isset($in['search']['total'])) ? $in['search']['total'] : null), ENT_QUOTES, 'UTF-8').'</p>

	'.LCRun3::sec($cx, ((is_array($in['search']) && isset($in['search']['rows'])) ? $in['search']['rows'] : null), $in, true, function($cx, $in) {return '
	<li>
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null))) ? '
		<a href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'">
			'.LCRun3::ch($cx, 'escapeContent', Array(Array(((is_array($in['content']) && isset($in['content']['format'])) ? $in['content']['format'] : null),((is_array($in['content']) && isset($in['content']['content'])) ? $in['content']['content'] : null)),Array()), 'encq').'
		</a>
		' : '
		<a href="'.htmlentities(((is_array($in['links']['workflow']) && isset($in['links']['workflow']['url'])) ? $in['links']['workflow']['url'] : null), ENT_QUOTES, 'UTF-8').'">
			@todo: this is some header
		</a>
		').'
	</li>
	';}).'
<ul>';
}
?>