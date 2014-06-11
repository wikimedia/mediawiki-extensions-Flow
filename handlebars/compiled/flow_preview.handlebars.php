<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'html' => 'Flow\TemplateHelper::html',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-content-preview">
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['title'])) ? $in['title'] : null))) ? '
		<div class="flow-preview-sub-container flow-topic-title">
			'.htmlentities(((is_array($in) && isset($in['title'])) ? $in['title'] : null), ENT_QUOTES, 'UTF-8').'
		</div>
	' : '').'
	<div class="flow-preview-sub-container">
		'.LCRun3::ch($cx, 'html', Array(((is_array($in) && isset($in['content'])) ? $in['content'] : null)), 'encq').'
	</div>
</div>';
}
?>