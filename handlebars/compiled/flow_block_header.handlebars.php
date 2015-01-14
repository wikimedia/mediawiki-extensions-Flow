<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'mustsec' => false,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' => array(),
        'helpers' => array(            'html' => 'Flow\TemplateHelper::htmlHelper',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in) {return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_header_detail' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board-header-detail-view">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['revision']['content']) && is_array($in['revision'])) ? $in['revision']['content'] : null))) ? '		'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['revision']['content']['format']) && is_array($in['revision']['content'])) ? $in['revision']['content']['format'] : null),((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null)),array()), 'encq').'
'.$sp.'' : '').'	&nbsp;
'.$sp.'
'.$sp.''.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? '		<div class="flow-board-header-nav">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['revision']['actions']['edit']) && is_array($in['revision']['actions'])) ? $in['revision']['actions']['edit'] : null))) ? '				<a href="'.htmlentities((string)((isset($in['revision']['actions']['edit']['url']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'					data-flow-api-handler="activateEditHeader"
'.$sp.'					data-flow-api-target="< .flow-board-header"
'.$sp.'					data-flow-interactive-handler="apiRequest"
'.$sp.'					class="mw-ui-button mw-ui-progressive  mw-ui-quiet flow-board-header-icon flow-ui-tooltip-target"
'.$sp.'					title="'.htmlentities((string)((isset($in['revision']['actions']['edit']['title']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'">
'.$sp.'						<span class="wikiglyph wikiglyph-pencil"></span>
'.$sp.'				</a>
'.$sp.'' : '').'		</div>
'.$sp.'' : '').'</div>
';},),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return '<div class="flow-board-header">
'.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '	').''.LCRun3::p($cx, 'flow_header_detail', array(array($in),array()), '	').'</div>
';
}
?>