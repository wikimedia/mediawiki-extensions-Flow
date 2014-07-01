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
    return '<!--
	post action could also be renderred in no-js mode, we need to a template
	so the rendering will proceed without error, we also need this to show
	the error of unsuccessful form submission
-->
<div class="flow-close-topic-error">
	<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.LCRun3::ch($cx, 'html', Array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)), 'encq').'</li>
			';}).'
		</ul>
	</div>
' : '').'
</div>

</div>
';
}
?>