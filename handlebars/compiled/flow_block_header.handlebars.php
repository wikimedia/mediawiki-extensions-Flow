<?php use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'oouify' => 'Flow\TemplateHelper::oouify',
);
    $partials = array('flow_header_title' => function ($cx, $in, $sp) {return ''.$sp.'<h2 class="flow-board-header-title">
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-speechBubbles"></span>
'.$sp.'	'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-board-header'),array()), 'encq', $in)).'
'.$sp.'</h2>
';},
'flow_header_edit_restrictions' => function ($cx, $in, $sp) {return ''.$sp.''.((!LR::ifvar($cx, ((isset($in['revision']['actions']['edit']) && is_array($in['revision']['actions'])) ? $in['revision']['actions']['edit'] : null), false)) ? '	<p class="flow-board-header-restricted">
'.$sp.'		'.LR::raw($cx, LR::hbch($cx, 'oouify', array(array('lock'),array('type'=>'IconWidget','classes'=>'flow-board-header-restricted-icon')), 'raw', $in)).'
'.$sp.'
'.$sp.'		<span class="flow-board-header-restricted-label">'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-board-description-can-not-edit'),array()), 'encq', $in)).'</span>
'.$sp.'        </p>
'.$sp.'' : '').'';},
'flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, LR::hbch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq', $in)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_header_detail' => function ($cx, $in, $sp) {return ''.$sp.''.LR::raw($cx, LR::hbch($cx, 'oouify', array(array(((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null),((isset($in['revision']['actions']['edit']['url']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['url'] : null),((isset($in['revision']['actions']['edit']['title']) && is_array($in['revision']['actions']['edit'])) ? $in['revision']['actions']['edit']['title'] : null)),array('type'=>'BoardDescriptionWidget','name'=>'flow-board-description')), 'raw', $in)).'
'.$sp.'<a href="javascript:void(0);"
'.$sp.'	class="mw-ui-button mw-ui-quiet side-rail-toggle-button"
'.$sp.'	data-flow-interactive-handler="toggleSideRail">
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-close pull-right collapse-button"
'.$sp.'		  title="'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-board-collapse-description'),array()), 'encq', $in)).'"></span>
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-speechBubbles pull-right expand-button"
'.$sp.'		  title="'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-board-expand-description'),array()), 'encq', $in)).'"></span>
'.$sp.'</a>
';},
'flow_header_categories' => function ($cx, $in, $sp) {return ''.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['categories']['items']) && is_array($cx['sp_vars']['root']['categories'])) ? $cx['sp_vars']['root']['categories']['items'] : null), false)) ? '<div id="catlinks" class="catlinks flow-board-header-category-view-nojs">
'.$sp.'	<div id="mw-normal-catlinks" class="mw-normal-catlinks">'.LR::encq($cx, LR::hbch($cx, 'html', array(array(((isset($cx['sp_vars']['root']['categories']['link']) && is_array($cx['sp_vars']['root']['categories'])) ? $cx['sp_vars']['root']['categories']['link'] : null)),array()), 'encq', $in)).'<ul class="flow-board-header-category-list">'.LR::sec($cx, ((isset($cx['sp_vars']['root']['categories']['items']) && is_array($cx['sp_vars']['root']['categories'])) ? $cx['sp_vars']['root']['categories']['items'] : null), null, $in, true, function($cx, $in)use($sp){return '<li class="flow-board-header-category-item">'.LR::encq($cx, LR::hbch($cx, 'html', array(array($in),array()), 'encq', $in)).'</li>';}).'</ul>
'.$sp.'	</div>
'.$sp.'</div>
'.$sp.'' : '').'';},
'flow_header_footer' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board-header-footer">
'.$sp.'  <hr />
'.$sp.'  <p>
'.$sp.'    '.LR::encq($cx, LR::hbch($cx, 'html', array(array(((isset($in['copyrightMessage']) && is_array($in)) ? $in['copyrightMessage'] : null)),array()), 'encq', $in)).'
'.$sp.'  </p>
'.$sp.'</div>
';});
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'lambda' => false,
            'mustlok' => false,
            'mustlam' => false,
            'echo' => false,
            'partnc' => false,
            'knohlp' => false,
            'debug' => isset($options['debug']) ? $options['debug'] : 1,
        ),
        'constants' => array(),
        'helpers' => isset($options['helpers']) ? array_merge($helpers, $options['helpers']) : $helpers,
        'partials' => isset($options['partials']) ? array_merge($partials, $options['partials']) : $partials,
        'scopes' => array(),
        'sp_vars' => isset($options['data']) ? array_merge(array('root' => $in), $options['data']) : array('root' => $in),
        'blparam' => array(),
        'runtime' => '\LightnCandy\Runtime',
    );
    
    return '<div class="flow-board-header flow-load-interactive" data-flow-load-handler="loadSideRail">
'.LR::p($cx, 'flow_header_title', array(array($in),array()), '	').''.LR::p($cx, 'flow_header_edit_restrictions', array(array($in),array()), '	').''.LR::p($cx, 'flow_errors', array(array($in),array()), '	').''.LR::p($cx, 'flow_header_detail', array(array($in),array()), '	').''.LR::p($cx, 'flow_header_categories', array(array($in),array()), '	').''.LR::p($cx, 'flow_header_footer', array(array($in),array()), '	').'</div>
';
};
?>