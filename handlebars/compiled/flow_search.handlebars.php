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
        'helpers' => Array(            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<ul>
	<p>Total results: '.htmlentities((string)((isset($in['search']['total']) && is_array($in['search'])) ? $in['search']['total'] : null), ENT_QUOTES, 'UTF-8').'</p>

	'.LCRun3::sec($cx, ((isset($in['search']['rows']) && is_array($in['search'])) ? $in['search']['rows'] : null), $in, true, function($cx, $in) {return '
	<li>
		'.((LCRun3::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null))) ? '
		<a href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'">
			'.((LCRun3::ifvar($cx, ((isset($in['content']) && is_array($in)) ? $in['content'] : null))) ? '
				'.LCRun3::ch($cx, 'escapeContent', Array(Array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),Array()), 'encq').'
			' : '
				@todo: no content available (probably moderated) - how do we deal with this?
			').'
		</a>
		' : '
		<a href="'.htmlentities((string)((isset($in['links']['workflow']['url']) && is_array($in['links']['workflow'])) ? $in['links']['workflow']['url'] : null), ENT_QUOTES, 'UTF-8').'">
			@todo: this is some header
		</a>
		').'
	</li>
	';}).'
<ul>
';
}
?>