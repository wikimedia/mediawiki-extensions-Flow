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

	'.((LCRun2::ifvar(((is_array($in) && isset($in['revision'])) ? $in['revision'] : null))) ? '
		'.LCRun2::ch('html', Array(((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null)), 'enc', $cx).'
	' : '
		<p>'.LCRun2::ch('l10n', Array('flow-header-empty'), 'enc', $cx).'</p>
	').'

	<div class="flow-board-header-nav">
		'.((LCRun2::ifvar(((is_array($in['revision']['actions']) && isset($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['url'])) ? $in['revision']['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-board-header-icon flow-ui-tooltip-target" href="/" title="'.htmlentities(((is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['title'])) ? $in['revision']['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-pencil"></span></a>
		' : '').'
	</div>
</div>
';
}
?>