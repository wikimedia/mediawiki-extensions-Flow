<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::html',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board-header">

	'.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null))) ? '
		'.LCRun3::ch($cx, 'html', Array(((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null)), 'encq').'
	' : '
		<p>'.LCRun3::ch($cx, 'l10n', Array('flow-header-empty'), 'encq').'</p>
	').'

	<div class="flow-board-header-nav">
		'.((LCRun3::ifvar($cx, ((is_array($in['revision']['actions']) && isset($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['url'])) ? $in['revision']['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-board-header-icon flow-ui-tooltip-target" href="/" title="'.htmlentities(((is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['title'])) ? $in['revision']['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-pencil"></span></a>
		' : '').'
	</div>
</div>
';
}
?>