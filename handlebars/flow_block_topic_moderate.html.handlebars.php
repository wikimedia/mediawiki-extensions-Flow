<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(),
        'blockhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-board">
	'.((LCRun2::ifvar(((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
		<ul>
		'.LCRun2::sec(((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $cx, $in, true, function($cx, $in) {return '
			<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
		';}).'
		</ul>
	' : '').'

	
	'.LCRun2::bch('eachPost', Array($in,((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)), $cx, $in, function($cx, $in) {return '
		@todo
	';}).'
</div>
';
}
?>