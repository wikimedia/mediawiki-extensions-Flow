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
        'helpers' => array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'partials' => array('flow_load_more' => function ($cx, $in) {return ''.LCRun3::ifv($cx, ((isset($in['loadMoreObject']) && is_array($in)) ? $in['loadMoreObject'] : null), $in, function($cx, $in) {return '	<div class="flow-load-more">
		<div class="flow-error-container">
		</div>

		<a data-flow-interactive-handler="apiRequest"
		   data-flow-api-handler="'.htmlentities((string)((isset($in['loadMoreApiHandler']) && is_array($in)) ? $in['loadMoreApiHandler'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-api-target="< .flow-load-more"
		   data-flow-load-handler="loadMore"
		   data-flow-scroll-target="'.htmlentities((string)((isset($in['loadMoreTarget']) && is_array($in)) ? $in['loadMoreTarget'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-scroll-container="'.htmlentities((string)((isset($in['loadMoreContainer']) && is_array($in)) ? $in['loadMoreContainer'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-template="'.htmlentities((string)((isset($in['loadMoreTemplate']) && is_array($in)) ? $in['loadMoreTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   href="'.htmlentities((string)((isset($in['loadMoreObject']['url']) && is_array($in['loadMoreObject'])) ? $in['loadMoreObject']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['loadMoreObject']['title']) && is_array($in['loadMoreObject'])) ? $in['loadMoreObject']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   class="mw-ui-button mw-ui-progressive flow-load-interactive flow-ui-fallback-element"><span class="wikiglyph wikiglyph-article"></span> '.LCRun3::ch($cx, 'l10n', array(array('flow-load-more'),array()), 'encq').'</a>
	</div>
';}, function($cx, $in) {return '	<div class="flow-no-more">
		'.LCRun3::ch($cx, 'l10n', array(array('flow-no-more-fwd'),array()), 'encq').'
	</div>
';}).'';},),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return ''.LCRun3::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), $in, true, function($cx, $in) {return ''.LCRun3::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in) {return '		<li class="flow-menu-section"><a class="mw-ui-button mw-ui-quiet mw-ui-progressive"
		       href="javascript:void(0);"
		       data-flow-interactive-handler="jumpToTopic"
		       data-flow-id="'.htmlentities((string)$cx['scopes'][count($cx['scopes'])-1], ENT_QUOTES, 'UTF-8').'">
			<span class="wikiglyph wikiglyph-stripe-expanded"></span>
			'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),array()), 'encq').'</a></li>
';}).'';}).'
'.LCRun3::ifv($cx, ((isset($in['links']['pagination']['fwd']) && is_array($in['links']['pagination'])) ? $in['links']['pagination']['fwd'] : null), $in, function($cx, $in) {return ''.LCRun3::unl($cx, ((isset($in['noLoadMore']) && is_array($in)) ? $in['noLoadMore'] : null), $in, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_load_more', array(array($in),array('loadMoreApiHandler'=>'topicList','loadMoreTarget'=>'< .flow-list','loadMoreContainer'=>'< .flow-list','loadMoreTemplate'=>'flow_board_toc_loop','loadMoreObject'=>((isset($in['links']['pagination']['fwd']) && is_array($in['links']['pagination'])) ? $in['links']['pagination']['fwd'] : null)))).'';}).'';}).'';
}
?>