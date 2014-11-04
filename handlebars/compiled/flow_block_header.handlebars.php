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
        'helpers' => Array(            'html' => 'Flow\TemplateHelper::htmlHelper',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array('flow_errors' => function ($cx, $in) {return '<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((isset($cx['scopes'][0]['errors']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((isset($cx['scopes'][0]['errors']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.LCRun3::ch($cx, 'html', Array(Array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),Array()), 'encq').'</li>
			';}).'
		</ul>
	</div>
' : '').'
</div>
';},'flow_header_detail' => function ($cx, $in) {return '<div class="flow-board-header-detail-view">
	'.((LCRun3::ifvar($cx, ((isset($in['revision']['content']) && is_array($in['revision'])) ? $in['revision']['content'] : null))) ? '
		'.LCRun3::ch($cx, 'escapeContent', Array(Array(((isset($in['revision']['content']['format']) && is_array($in['revision']['content'])) ? $in['revision']['content']['format'] : null),((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null)),Array()), 'encq').'
	' : '').'
	&nbsp;

	'.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? '
		<div class="flow-board-header-nav">
			'.((LCRun3::ifvar($cx, ((isset($in['revision']['actions']['edit']) && is_array($in['revision']['actions'])) ? $in['revision']['actions']['edit'] : null))) ? '
				<a href="'.htmlentities((string)((isset($in['revision']['actions']['edit']['url']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
					data-flow-api-handler="activateEditHeader"
					data-flow-api-target="< .flow-board-header"
					data-flow-interactive-handler="apiRequest"
					class="mw-ui-button mw-ui-progressive  mw-ui-quiet flow-board-header-icon flow-ui-tooltip-target"
					title="'.htmlentities((string)((isset($in['revision']['actions']['edit']['title']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'">
						<span class="wikiglyph wikiglyph-pencil"></span>
				</a>
			' : '').'
		</div>
	' : '').'
</div>
';},),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board-header">
	'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'
	'.LCRun3::p($cx, 'flow_header_detail', Array(Array($in),Array())).'
</div>
';
}
?>