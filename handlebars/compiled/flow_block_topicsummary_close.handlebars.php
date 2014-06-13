<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(),
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
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
		<ul>
			'.LCRun3::sec($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
			';}).'
		</ul>
	' : '').'
</div>
';
}
?>