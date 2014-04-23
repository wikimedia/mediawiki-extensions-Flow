<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::html',
),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-board-header">
	'.LCRun2::wi(((is_array($in) && isset($in['revision'])) ? $in['revision'] : null), $cx, $in, function($cx, $in) {return '
		'.((LCRun2::ifvar(((is_array($in) && isset($in['content'])) ? $in['content'] : null))) ? '
			'.LCRun2::ch('html', Array(((is_array($in) && isset($in['content'])) ? $in['content'] : null)), 'enc', $cx).'
		' : '
			<p>'.LCRun2::ch('l10n', Array('No_header'), 'enc', $cx).'</p>
		').'
	';}).'
	<div class="flow-board-header-nav">
		<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-board-header-icon flow-ui-tooltip-target" href="/" title="'.LCRun2::ch('l10n', Array('Edit'), 'enc', $cx).'"><span class="wikicon wikicon-pencil"></span></a>
	</div>
</div>';
}
?>