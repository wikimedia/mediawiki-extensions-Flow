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
            'oouify' => 'Flow\TemplateHelper::oouify',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array('flow_header_title' => function ($cx, $in, $sp) {return ''.$sp.'<h2 class="flow-board-header-title">
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-speechBubbles"></span>
'.$sp.'	'.LCRun3::ch($cx, 'l10n', array(array('flow-board-header'),array()), 'encq').'
'.$sp.'</h2>
';},'flow_header_edit_restrictions' => function ($cx, $in, $sp) {return ''.$sp.''.((!LCRun3::ifvar($cx, ((isset($in['revision']['actions']['edit']) && is_array($in['revision']['actions'])) ? $in['revision']['actions']['edit'] : null))) ? '	<p class="flow-board-header-restricted">
'.$sp.'		'.LCRun3::ch($cx, 'oouify', array(array('lock'),array('type'=>'IconWidget','classes'=>'flow-board-header-restricted-icon')), 'raw').'
'.$sp.'
'.$sp.'		<span class="flow-board-header-restricted-label">'.LCRun3::ch($cx, 'l10n', array(array('flow-board-description-can-not-edit'),array()), 'encq').'</span>
'.$sp.'        </p>
'.$sp.'' : '').'';},'flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in)use($sp){return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_header_detail' => function ($cx, $in, $sp) {return ''.$sp.''.LCRun3::ch($cx, 'oouify', array(array(((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null),((isset($in['revision']['actions']['edit']['url']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['url'] : null),((isset($in['revision']['actions']['edit']['title']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['title'] : null)),array('type'=>'BoardDescriptionWidget','name'=>'flow-board-description')), 'raw').'
'.$sp.'<a href="javascript:void(0);"
'.$sp.'	class="mw-ui-button mw-ui-quiet side-rail-toggle-button"
'.$sp.'	data-flow-interactive-handler="toggleSideRail">
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-only mw-ui-icon-close pull-right collapse-button"
'.$sp.'		  title="'.LCRun3::ch($cx, 'l10n', array(array('flow-board-collapse-description'),array()), 'encq').'"></span>
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-only mw-ui-icon-speechBubbles pull-right expand-button"
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
'.LCRun3::p($cx, 'flow_header_title', array(array($in),array()), '	').''.LCRun3::p($cx, 'flow_header_edit_restrictions', array(array($in),array()), '	').''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '	').''.LCRun3::p($cx, 'flow_header_detail', array(array($in),array()), '	').''.LCRun3::p($cx, 'flow_header_categories', array(array($in),array()), '	').''.LCRun3::p($cx, 'flow_header_footer', array(array($in),array()), '	').'</div>
';
}
?>