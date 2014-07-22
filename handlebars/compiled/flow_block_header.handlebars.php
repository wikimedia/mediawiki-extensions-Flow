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
        'helpers' => Array(            'html' => 'Flow\TemplateHelper::htmlHelper',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board-header">
	<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.LCRun3::ch($cx, 'html', Array(Array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)),Array()), 'encq').'</li>
			';}).'
		</ul>
	</div>
' : '').'
</div>

	<div class="flow-board-header-detail-view">
		'.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null))) ? '
			'.LCRun3::ch($cx, 'escapeContent', Array(Array(((is_array($in['revision']['content']) && isset($in['revision']['content']['format'])) ? $in['revision']['content']['format'] : null),((is_array($in['revision']['content']) && isset($in['revision']['content']['content'])) ? $in['revision']['content']['content'] : null)),Array()), 'encq').'
		' : '').'
		&nbsp;

		'.((!LCRun3::ifvar($cx, ((is_array($in) && isset($in['isPreview'])) ? $in['isPreview'] : null))) ? '
			<div class="flow-board-header-nav">
				'.((LCRun3::ifvar($cx, ((is_array($in['revision']['actions']) && isset($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit'] : null))) ? '
					<a href="'.htmlentities(((is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['url'])) ? $in['revision']['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						data-flow-api-handler="activateEditHeader"
						data-flow-api-target="< .flow-board-header"
						data-flow-interactive-handler="apiRequest"
						class="mw-ui-button mw-ui-progressive  mw-ui-quiet flow-board-header-icon flow-ui-tooltip-target"
						title="'.htmlentities(((is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['title'])) ? $in['revision']['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-pencil"></span>
					</a>
				' : '').'
			</div>
		' : '').'
	</div>
</div>
';
}
?>