<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' => array(),
        'helpers' => array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
            'oouify' => 'Flow\TemplateHelper::oouify',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array('flow_header_title' => function ($cx, $in, $sp) {return ''.$sp.'<h2 class="flow-board-header-title">
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-speechBubbles"></span>
'.$sp.'	'.LCRun3::ch($cx, 'l10n', array(array('flow-board-header'),array()), 'encq').'
'.$sp.'</h2>
';},'flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in)use($sp){return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_header_detail_oldsystem' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board-header-detail-view">
'.$sp.'	<div class="flow-board-header-nav">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['revision']['actions']['edit']) && is_array($in['revision']['actions'])) ? $in['revision']['actions']['edit'] : null))) ? '			<a href="'.htmlentities((string)((isset($in['revision']['actions']['edit']['url']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-api-handler="activateEditHeader"
'.$sp.'			   data-flow-api-target="< .flow-board-header"
'.$sp.'			   data-flow-interactive-handler="apiRequest"
'.$sp.'			   class="mw-ui-button mw-ui-progressive  mw-ui-quiet flow-ui-tooltip-target"
'.$sp.'			   title="'.htmlentities((string)((isset($in['revision']['actions']['edit']['title']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'">
'.$sp.'					<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit flow-board-header-icon"></span>'.LCRun3::ch($cx, 'l10n', array(array('flow-edit-header-link'),array()), 'encq').'
'.$sp.'			</a>
'.$sp.'' : '').'	</div>
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['revision']['content']) && is_array($in['revision'])) ? $in['revision']['content'] : null))) ? '		<div class="flow-board-header-content">
'.$sp.'			'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['revision']['content']['format']) && is_array($in['revision']['content'])) ? $in['revision']['content']['format'] : null),((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null)),array()), 'encq').'
'.$sp.'		</div>
'.$sp.'' : '').'
'.$sp.'</div>
'.$sp.'<a href="javascript:void(0);"
'.$sp.'	class="mw-ui-button mw-ui-quiet side-rail-toggle-button"
'.$sp.'	data-flow-interactive-handler="toggleSideRail">
'.$sp.'	<span class="wikiglyph wikiglyph-x pull-right collapse-button"
'.$sp.'		  title="'.LCRun3::ch($cx, 'l10n', array(array('flow-board-collapse-description'),array()), 'encq').'"></span>
'.$sp.'	<span class="wikiglyph wikiglyph-speech-bubbles pull-right expand-button"
'.$sp.'		  title="'.LCRun3::ch($cx, 'l10n', array(array('flow-board-expand-description'),array()), 'encq').'"></span>
'.$sp.'</a>
';},'flow_header_detail' => function ($cx, $in, $sp) {return ''.$sp.''.LCRun3::ch($cx, 'oouify', array(array(((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null),((isset($in['revision']['actions']['edit']['url']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['url'] : null),((isset($in['revision']['actions']['edit']['title']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['title'] : null)),array('label'=>((isset($in['flow-edit-header-link']) && is_array($in)) ? $in['flow-edit-header-link'] : null),'type'=>'BoardDescriptionWidget','name'=>'flow-board-description')), 'raw').'
'.$sp.'<a href="javascript:void(0);"
'.$sp.'	class="mw-ui-button mw-ui-quiet side-rail-toggle-button"
'.$sp.'	data-flow-interactive-handler="toggleSideRail">
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-close pull-right collapse-button"
'.$sp.'		  title="'.LCRun3::ch($cx, 'l10n', array(array('flow-board-collapse-description'),array()), 'encq').'"></span>
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-speechBubbles pull-right expand-button"
'.$sp.'		  title="'.LCRun3::ch($cx, 'l10n', array(array('flow-board-expand-description'),array()), 'encq').'"></span>
'.$sp.'</a>
';},'flow_header_categories' => function ($cx, $in, $sp) {return ''.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['categories']['items']) && is_array($cx['sp_vars']['root']['categories'])) ? $cx['sp_vars']['root']['categories']['items'] : null))) ? '<div id="catlinks" class="catlinks flow-board-header-category-view-nojs">
'.$sp.'	<div id="mw-normal-catlinks" class="mw-normal-catlinks">'.LCRun3::ch($cx, 'html', array(array(((isset($cx['sp_vars']['root']['categories']['link']) && is_array($cx['sp_vars']['root']['categories'])) ? $cx['sp_vars']['root']['categories']['link'] : null)),array()), 'encq').'<ul class="flow-board-header-category-list">'.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['categories']['items']) && is_array($cx['sp_vars']['root']['categories'])) ? $cx['sp_vars']['root']['categories']['items'] : null), $in, true, function($cx, $in)use($sp){return '<li class="flow-board-header-category-item">'.LCRun3::ch($cx, 'html', array(array($in),array()), 'encq').'</li>';}).'</ul>
'.$sp.'	</div>
'.$sp.'</div>
'.$sp.'' : '').'';},'flow_header_footer' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board-header-footer">
'.$sp.'  <hr />
'.$sp.'  <p>
'.$sp.'    '.LCRun3::ch($cx, 'html', array(array(((isset($in['copyrightMessage']) && is_array($in)) ? $in['copyrightMessage'] : null)),array()), 'encq').'
'.$sp.'  </p>
'.$sp.'</div>
';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="flow-board-header flow-load-interactive" data-flow-load-handler="loadSideRail">
'.LCRun3::p($cx, 'flow_header_title', array(array($in),array()), '	').''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '	').''.((LCRun3::ifvar($cx, ((isset($in['oldSystem']) && is_array($in)) ? $in['oldSystem'] : null))) ? ''.LCRun3::p($cx, 'flow_header_detail_oldsystem', array(array($in),array()), '		').'' : ''.LCRun3::p($cx, 'flow_header_detail', array(array($in),array()), '		').'').''.LCRun3::p($cx, 'flow_header_categories', array(array($in),array()), '	').''.LCRun3::p($cx, 'flow_header_footer', array(array($in),array()), '	').'</div>
';
}
?>