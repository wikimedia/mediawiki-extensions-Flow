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
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'timestamp' => 'Flow\TemplateHelper::timestampHelper',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'post' => 'Flow\TemplateHelper::post',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'concat' => 'Flow\TemplateHelper::concat',
            'linkWithReturnTo' => 'Flow\TemplateHelper::linkWithReturnTo',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
            'ifCond' => 'Flow\TemplateHelper::ifCond',
            'tooltip' => 'Flow\TemplateHelper::tooltip',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
        'partials' => array('flow_board_navigation' => function ($cx, $in, $sp) {return ''.$sp.'
'.$sp.'<div class="flow-board-navigation flow-load-interactive" data-flow-load-handler="boardNavigation">
'.$sp.'	<div class="flow-error-container">
'.$sp.'	</div>
'.$sp.'	<div class="flow-board-navigation-inner">
'.$sp.'		<a href="javascript:void(0);"
'.$sp.'		   class="flow-board-navigator-last flow-ui-tooltip-target"
'.$sp.'		   data-tooltip-pointing="right"
'.$sp.'		   title="'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['sortby']) && is_array($in)) ? $in['sortby'] : null),'===','updated'),array()), $in, false, function($cx, $in)use($sp){return ''.LCRun3::ch($cx, 'l10n', array(array('flow-sorting-tooltip-recent'),array()), 'encq').'';}, function($cx, $in)use($sp){return ''.LCRun3::ch($cx, 'l10n', array(array('flow-sorting-tooltip-newest'),array()), 'encq').'';}).'"
'.$sp.'		   data-flow-interactive-handler="menuToggle"
'.$sp.'		   data-flow-menu-target="< .flow-board-navigation .flow-board-sort-menu">'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['sortby']) && is_array($in)) ? $in['sortby'] : null),'===','updated'),array()), $in, false, function($cx, $in)use($sp){return '				'.LCRun3::ch($cx, 'l10n', array(array('flow-recent-topics'),array()), 'encq').'
'.$sp.'';}, function($cx, $in)use($sp){return '				'.LCRun3::ch($cx, 'l10n', array(array('flow-newest-topics'),array()), 'encq').'
'.$sp.'';}).'			<span class="mw-ui-icon mw-ui-icon-after mw-ui-icon-caretDown"></span>
'.$sp.'		</a>
'.$sp.'
'.$sp.'		<a href=""
'.$sp.'		   data-flow-interactive-handler="apiRequest"
'.$sp.'		   data-flow-api-target="< .flow-board-navigation .flow-board-toc-menu .flow-list"
'.$sp.'		   data-flow-api-handler="topicList" 
'.$sp.'		   data-flow-menu-target="< .flow-board-navigation .flow-board-toc-menu"
'.$sp.'		   class="flow-board-navigator-active flow-board-navigator-first">
'.$sp.'
'.$sp.'			<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-stripeToC"></span>
'.$sp.'
'.$sp.'			<span class="flow-load-interactive" data-flow-load-handler="boardNavigationTitle">'.LCRun3::ch($cx, 'l10n', array(array('flow-board-header-browse-topics-link'),array()), 'encq').'</span>
'.$sp.'		</a>
'.$sp.'	</div>
'.$sp.'
'.$sp.'	<div class="flow-board-header-menu">
'.$sp.'		<div class="flow-menu flow-menu-inverted flow-menu-scrollable flow-board-toc-menu flow-load-interactive"
'.$sp.'		     data-flow-load-handler="menu"
'.$sp.'		     data-flow-toc-target=".flow-list">
'.$sp.'			<div class="flow-menu-js-drop flow-menu-js-drop-hidden"><a href="javascript:void(0);" class="flow-board-header-menu-activator"></a></div>
'.$sp.'			<ul class="mw-ui-button-container flow-board-toc-list flow-list flow-load-interactive"
'.$sp.'			    data-flow-load-handler="tocMenu"
'.$sp.'			    data-flow-toc-target="li:not(.flow-load-more):last"
'.$sp.'			    data-flow-template="flow_board_toc_loop.partial">
'.$sp.'			</ul>
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<div class="flow-menu flow-board-sort-menu flow-load-interactive"
'.$sp.'		     data-flow-load-handler="menu">
'.$sp.'			<div class="flow-menu-js-drop flow-menu-js-drop-hidden"><a href="javascript:void(0);" class="flow-board-header-menu-activator"></a></div>
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['links']['board-sort']) && is_array($in['links'])) ? $in['links']['board-sort'] : null))) ? '				<ul class="mw-ui-button-container flow-list">'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['sortby']) && is_array($in)) ? $in['sortby'] : null),'===','updated'),array()), $in, false, function($cx, $in)use($sp){return '					<li><a class="mw-ui-button mw-ui-quiet"
'.$sp.'					       href="'.htmlentities((string)((isset($in['links']['board-sort']['newest']) && is_array($in['links']['board-sort'])) ? $in['links']['board-sort']['newest'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'					       data-flow-interactive-handler="apiRequest"
'.$sp.'					       data-flow-api-target="< .flow-component"
'.$sp.'					       data-flow-api-handler="board"><span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-beta"></span> '.LCRun3::ch($cx, 'l10n', array(array('flow-newest-topics'),array()), 'encq').'</a></li>
'.$sp.'';}, function($cx, $in)use($sp){return '					<li><a class="mw-ui-button mw-ui-quiet"
'.$sp.'					       href="'.htmlentities((string)((isset($in['links']['board-sort']['updated']) && is_array($in['links']['board-sort'])) ? $in['links']['board-sort']['updated'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'					       data-flow-interactive-handler="apiRequest"
'.$sp.'					       data-flow-api-target="< .flow-component"
'.$sp.'					       data-flow-api-handler="board"><span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-clock"></span> '.LCRun3::ch($cx, 'l10n', array(array('flow-recent-topics'),array()), 'encq').'</a></li>
'.$sp.'';}).'				</ul>
'.$sp.'' : '').'		</div>
'.$sp.'	</div>
'.$sp.'</div>
';},'flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in)use($sp){return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_anon_warning' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-anon-warning">
'.$sp.'	<div class="flow-anon-warning-mobile">
'.$sp.''.LCRun3::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in)use($sp){return ''.LCRun3::ch($cx, 'l10nParse', array(array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin'),array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin/signup'),array()), 'raw')),array()), 'encq').'';}).'	</div>
'.$sp.'
'.$sp.''.LCRun3::hbch($cx, 'progressiveEnhancement', array(array(),array()), $in, false, function($cx, $in)use($sp){return '		<div class="flow-anon-warning-desktop">
'.$sp.''.LCRun3::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in)use($sp){return ''.LCRun3::ch($cx, 'l10nParse', array(array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin'),array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin/signup'),array()), 'raw')),array()), 'encq').'';}).'		</div>
'.$sp.'';}).'</div>
';},'flow_form_cancel_button' => function ($cx, $in, $sp) {return ''.$sp.'<button data-flow-interactive-handler="cancelForm"
'.$sp.'        data-role="cancel"
'.$sp.'        type="reset"
'.$sp.'        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"
'.$sp.'
'.$sp.'>
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['msg']) && is_array($in)) ? $in['msg'] : null))) ? ''.LCRun3::ch($cx, 'l10n', array(array(((isset($in['msg']) && is_array($in)) ? $in['msg'] : null)),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'').'</button>
';},'flow_newtopic_form' => function ($cx, $in, $sp) {return ''.$sp.''.((LCRun3::ifvar($cx, ((isset($in['actions']['newtopic']) && is_array($in['actions'])) ? $in['actions']['newtopic'] : null))) ? '	<form action="'.htmlentities((string)((isset($in['actions']['newtopic']['url']) && is_array($in['actions']['newtopic'])) ? $in['actions']['newtopic']['url'] : null), ENT_QUOTES, 'UTF-8').'" method="POST" class="flow-newtopic-form" data-flow-initial-state="'.((LCRun3::ifvar($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null))) ? 'expanded' : 'collapsed').'">
'.$sp.''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '		').'
'.$sp.''.LCRun3::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in)use($sp){return ''.LCRun3::p($cx, 'flow_anon_warning', array(array($in),array()), '			').'';}).'
'.$sp.'		<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.'		<input type="hidden" name="topiclist_replyTo" value="'.htmlentities((string)((isset($in['workflowId']) && is_array($in)) ? $in['workflowId'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.'		<input name="topiclist_topic" class="mw-ui-input mw-ui-input-large"
'.$sp.'			required
'.$sp.'			'.((LCRun3::ifvar($cx, ((isset($in['submitted']['topic']) && is_array($in['submitted'])) ? $in['submitted']['topic'] : null))) ? 'value="'.htmlentities((string)((isset($in['submitted']['topic']) && is_array($in['submitted'])) ? $in['submitted']['topic'] : null), ENT_QUOTES, 'UTF-8').'"' : '').'
'.$sp.'			type="text"
'.$sp.'			placeholder="'.LCRun3::ch($cx, 'l10n', array(array('flow-newtopic-start-placeholder'),array()), 'encq').'"
'.$sp.'			data-role="title"
'.$sp.'
'.$sp.'			data-flow-interactive-handler-focus="activateNewTopic"
'.$sp.'		/>
'.$sp.'		<div class="flow-editor">
'.$sp.'			<textarea name="topiclist_content"
'.$sp.'			          class="mw-ui-input flow-form-collapsible mw-ui-input-large'.((LCRun3::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null))) ? ' flow-form-collapsible-collapsed' : '').'"
'.$sp.'			          placeholder="'.LCRun3::ch($cx, 'l10n', array(array('flow-newtopic-content-placeholder',((isset($cx['sp_vars']['root']['title']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['title'] : null)),array()), 'encq').'"
'.$sp.'			          data-role="content"
'.$sp.'			          required
'.$sp.'			>'.((LCRun3::ifvar($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null))) ? ''.htmlentities((string)((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'</textarea>
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<div class="flow-form-actions flow-form-collapsible'.((LCRun3::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null))) ? ' flow-form-collapsible-collapsed' : '').'">
'.$sp.'			<button data-role="submit" data-flow-api-handler="newTopic"
'.$sp.'				data-flow-interactive-handler="apiRequest"
'.$sp.'				data-flow-eventlog-action="save-attempt"
'.$sp.'				class="mw-ui-button mw-ui-constructive mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', array(array('flow-newtopic-save'),array()), 'encq').'</button>
'.$sp.''.LCRun3::p($cx, 'flow_form_cancel_button', array(array($in),array()), '			').'			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-new-topic'),array()), 'encq').'</small>
'.$sp.'		</div>
'.$sp.'	</form>
'.$sp.'' : '').'';},'flow_topic_moderation_flag' => function ($cx, $in, $sp) {return ''.$sp.'<span class="mw-ui-icon mw-ui-icon-before'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','lock'),array()), $in, false, function($cx, $in)use($sp){return ' mw-ui-icon-check';}).''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','hide'),array()), $in, false, function($cx, $in)use($sp){return ' mw-ui-icon-flag';}).''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','delete'),array()), $in, false, function($cx, $in)use($sp){return ' mw-ui-icon-remove';}).'"></span>
';},'flow_post_moderation_state' => function ($cx, $in, $sp) {return ''.$sp.'<span class="plainlinks">'.((LCRun3::ifvar($cx, ((isset($in['replyToId']) && is_array($in)) ? $in['replyToId'] : null))) ? ''.LCRun3::ch($cx, 'l10nParse', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'-post-content'),array()), 'raw'),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null),((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null)),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10nParse', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'-title-content'),array()), 'raw'),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null),((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null)),array()), 'encq').'').'</span>
';},'flow_topic_titlebar_summary' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic-summary-container">
'.$sp.''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '	').''.((LCRun3::ifvar($cx, ((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null))) ? '		<div class="flow-topic-summary">
'.$sp.'			<div class="flow-topic-summary-content">
'.$sp.'				'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['revision']['content']['format']) && is_array($in['revision']['content'])) ? $in['revision']['content']['format'] : null),((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null)),array()), 'encq').'
'.$sp.'			</div>
'.$sp.'		</div>
'.$sp.'		<br class="flow-ui-clear"/>
'.$sp.'' : '').'</div>
';},'flow_topic_titlebar_content' => function ($cx, $in, $sp) {return ''.$sp.'<h2 class="flow-topic-title flow-load-interactive '.((LCRun3::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))) ? 'flow-click-interactive' : '').'"
'.$sp.'	data-flow-topic-title="'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),array()), 'encq').'"
'.$sp.'	data-flow-load-handler="topicTitle"
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))) ? '	data-flow-interactive-handler="collapserCollapsibleToggle"
'.$sp.'' : '').'		>
'.$sp.'	'.((LCRun3::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check"></span> ' : '').''.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),array()), 'encq').'</h2>
'.$sp.'<div class="flow-topic-meta">
'.$sp.'	'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-comments',((isset($in['reply_count']) && is_array($in)) ? $in['reply_count'] : null)),array()), 'encq').' &bull;
'.$sp.'
'.$sp.'	<a href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp-anchor">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null))) ? '			'.LCRun3::ch($cx, 'timestamp', array(array(((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null)),array()), 'encq').'
'.$sp.'' : '			'.LCRun3::ch($cx, 'uuidTimestamp', array(array(((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), 'encq').'
'.$sp.'').'	</a>
'.$sp.'</div>
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['isModeratedNotLocked']) && is_array($in)) ? $in['isModeratedNotLocked'] : null))) ? '	<div class="flow-moderated-topic-title flow-ui-text-truncated">'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').''.LCRun3::p($cx, 'flow_topic_moderation_flag', array(array($in),array())).'
'.$sp.''.LCRun3::p($cx, 'flow_post_moderation_state', array(array($in),array()), '		').'	</div>
'.$sp.'	<div class="flow-moderated-topic-reason">
'.$sp.'		'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-moderated-reason-prefix'),array()), 'encq').'
'.$sp.'		'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['moderateReason']['format']) && is_array($in['moderateReason'])) ? $in['moderateReason']['format'] : null),((isset($in['moderateReason']['content']) && is_array($in['moderateReason'])) ? $in['moderateReason']['content'] : null)),array()), 'encq').'
'.$sp.'	</div>
'.$sp.'' : '').'<span class="flow-reply-count"><span class="flow-reply-count-number">'.htmlentities((string)((isset($in['reply_count']) && is_array($in)) ? $in['reply_count'] : null), ENT_QUOTES, 'UTF-8').'</span></span>
'.$sp.'
'.$sp.''.LCRun3::p($cx, 'flow_topic_titlebar_summary', array(array(((isset($in['summary']) && is_array($in)) ? $in['summary'] : null)),array())).'';},'flow_topic_titlebar_watch' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic-watchlist flow-watch-link">
'.$sp.''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '	').'
'.$sp.'	<a href="'.((LCRun3::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null))) ? ''.htmlentities((string)((isset($in['links']['unwatch-topic']['url']) && is_array($in['links']['unwatch-topic'])) ? $in['links']['unwatch-topic']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['links']['watch-topic']['url']) && is_array($in['links']['watch-topic'])) ? $in['links']['watch-topic']['url'] : null), ENT_QUOTES, 'UTF-8').'').'"
'.$sp.'	   class="mw-ui-anchor mw-ui-constructive '.((!LCRun3::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null))) ? 'mw-ui-quiet' : '').'
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null))) ? 'flow-watch-link-unwatch' : 'flow-watch-link-watch').'"
'.$sp.'	   data-flow-api-handler="watchItem"
'.$sp.'	   data-flow-api-target="< .flow-topic-watchlist"
'.$sp.'	   data-flow-api-method="POST">'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-star" title="'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-action-watchlist-remove'),array()), 'encq').'"></span>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').''.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-unstar" title="'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-action-watchlist-add'),array()), 'encq').'"></span>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</a>
'.$sp.'</div>
';},'flow_moderation_actions_list' => function ($cx, $in, $sp) {return ''.$sp.'<section>'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','topic'),array()), $in, false, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '<li class="flow-js">'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-progressive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="activateEditTitle"
'.$sp.'				   data-flow-api-target="< .flow-topic-titlebar"
'.$sp.'				>'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-edit-title'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['topic-history']) && is_array($in['links'])) ? $in['links']['topic-history'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'				   href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['links']['topic-history']['title']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-clock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-history'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'				   href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-view'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['summarize']) && is_array($in['actions'])) ? $in['actions']['summarize'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-progressive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="activateSummarizeTopic"
'.$sp.'				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['summarize']['url']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['summarize']['title']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-stripeToC mw-ui-icon-stripeToC-progressive-hover"></span> ' : '').''.((LCRun3::ifvar($cx, ((isset($in['summary']) && is_array($in)) ? $in['summary'] : null))) ? ''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-resummarize-topic'),array()), 'raw')),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-summarize-topic'),array()), 'raw')),array()), 'encq').'').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','post'),array()), $in, false, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '<li>
'.$sp.'				<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-progressive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   data-flow-api-handler="activateEditPost"
'.$sp.'				   data-flow-api-target="< .flow-post-main"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				>'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array('flow-post-action-edit-post'),array()), 'encq').'</a>
'.$sp.'			</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['post']) && is_array($in['links'])) ? $in['links']['post'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'				   href="'.htmlentities((string)((isset($in['links']['post']['url']) && is_array($in['links']['post'])) ? $in['links']['post']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['links']['post']['title']) && is_array($in['links']['post'])) ? $in['links']['post']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-post-action-view'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).'</section>
'.$sp.'
'.$sp.'<section>'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['undo']) && is_array($in['actions'])) ? $in['actions']['undo'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['undo']['url']) && is_array($in['actions']['undo'])) ? $in['actions']['undo']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				>'.htmlentities((string)((isset($in['actions']['undo']['title']) && is_array($in['actions']['undo'])) ? $in['actions']['undo']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).''.((LCRun3::ifvar($cx, ((isset($in['actions']['hide']) && is_array($in['actions'])) ? $in['actions']['hide'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['hide']['url']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['hide']['title']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="hide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-hide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['unhide']['title']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="unhide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unhide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['delete']) && is_array($in['actions'])) ? $in['actions']['delete'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['delete']['url']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['delete']['title']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="delete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-remove mw-ui-icon-remove-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-delete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['undelete']) && is_array($in['actions'])) ? $in['actions']['undelete'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['undelete']['url']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['undelete']['title']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="undelete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-remove mw-ui-icon-remove-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-undelete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['suppress']) && is_array($in['actions'])) ? $in['actions']['suppress'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['suppress']['url']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['suppress']['title']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="suppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-suppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions'])) ? $in['actions']['unsuppress'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['unsuppress']['url']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['unsuppress']['title']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="unsuppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unsuppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="lockTopic"
'.$sp.'				   data-flow-api-method="POST"
'.$sp.'				   data-role="lock"
'.$sp.'				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="lockTopic"
'.$sp.'				   data-flow-api-method="POST"
'.$sp.'				   data-role="unlock"
'.$sp.'				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="lockAndSummarizeTopic"
'.$sp.'				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
'.$sp.'				   data-flow-api-method="POST"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="lockAndSummarizeTopic"
'.$sp.'				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
'.$sp.'				   data-flow-api-method="POST"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).'</section>
';},'flow_topic_titlebar' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic-titlebar">
'.$sp.''.LCRun3::p($cx, 'flow_topic_titlebar_content', array(array($in),array()), '	').'
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['watchable']) && is_array($in)) ? $in['watchable'] : null))) ? ''.LCRun3::p($cx, 'flow_topic_titlebar_watch', array(array($in),array()), '		').'' : '').'	<div class="flow-menu flow-menu-hoverable">
'.$sp.'		<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ellipsis"></span></a></div>
'.$sp.'		<ul class="mw-ui-button-container flow-list">
'.$sp.''.LCRun3::p($cx, 'flow_moderation_actions_list', array(array($in),array('moderationType'=>'topic','moderationTarget'=>'title','moderationTemplate'=>'topic','moderationContainerClass'=>'flow-menu','moderationMwUiClass'=>'mw-ui-button','moderationIcons'=>true)), '			').'		</ul>
'.$sp.'	</div>
'.$sp.'</div>
';},'flow_reply_form' => function ($cx, $in, $sp) {return ''.$sp.''.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? '	<form class="flow-post flow-reply-form"
'.$sp.'	      method="POST"
'.$sp.'	      action="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'	      id="flow-reply-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'	      data-flow-initial-state="collapsed"
'.$sp.'	>
'.$sp.'		<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['rootBlock']['editToken']) && is_array($cx['sp_vars']['root']['rootBlock'])) ? $cx['sp_vars']['root']['rootBlock']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.'		<input type="hidden" name="topic_replyTo" value="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '		').'
'.$sp.''.LCRun3::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in)use($sp){return ''.LCRun3::p($cx, 'flow_anon_warning', array(array($in),array()), '			').'';}).'
'.$sp.'		<div class="flow-editor">
'.$sp.'			<textarea id="flow-post-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content"
'.$sp.'			          name="topic_content"
'.$sp.'			          required
'.$sp.'			          data-flow-expandable="true"
'.$sp.'			          class="mw-ui-input flow-click-interactive"
'.$sp.'			          type="text"
'.$sp.'			          placeholder="'.LCRun3::ch($cx, 'l10n', array(array('flow-reply-topic-title-placeholder',((isset($in['properties']['topic-of-post']) && is_array($in['properties'])) ? $in['properties']['topic-of-post'] : null)),array()), 'encq').'"
'.$sp.'			          data-role="content"
'.$sp.'
'.$sp.'			          data-flow-interactive-handler-focus="activateReplyTopic"
'.$sp.'			>'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['submitted'] : null))) ? ''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['submitted']['postId']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in)use($sp){return ''.htmlentities((string)((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'';}).'' : '').'</textarea>
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<div class="flow-form-actions flow-form-collapsible">
'.$sp.'			<button data-role="submit"
'.$sp.'			        class="mw-ui-button mw-ui-constructive"
'.$sp.'			        data-flow-interactive-handler="apiRequest"
'.$sp.'			        data-flow-api-handler="submitReply"
'.$sp.'			        data-flow-api-target="< .flow-topic"
'.$sp.'			        data-flow-eventlog-action="save-attempt"
'.$sp.'			>'.htmlentities((string)((isset($in['actions']['reply']['text']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['text'] : null), ENT_QUOTES, 'UTF-8').'</button>
'.$sp.''.LCRun3::p($cx, 'flow_form_cancel_button', array(array($in),array()), '			').'			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-reply'),array()), 'encq').'</small>
'.$sp.'		</div>
'.$sp.'	</form>
'.$sp.'' : '').'';},'flow_topic' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic flow-load-interactive'.((LCRun3::ifvar($cx, ((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null))) ? ' flow-topic-moderatestate-'.htmlentities((string)((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null), ENT_QUOTES, 'UTF-8').' ' : '').''.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? ' flow-topic-moderated ' : '').''.((LCRun3::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))) ? 'flow-element-collapsible flow-element-collapsed' : '').'"
'.$sp.'     id="flow-topic-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'     data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'     data-flow-load-handler="topic"
'.$sp.'     data-flow-toc-scroll-target=".flow-topic-titlebar"
'.$sp.'     data-flow-topic-timestamp-updated="'.htmlentities((string)((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'>
'.$sp.''.LCRun3::p($cx, 'flow_topic_titlebar', array(array($in),array()), '	').'
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['posts']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['posts'] : null))) ? ''.LCRun3::sec($cx, ((isset($in['replies']) && is_array($in)) ? $in['replies'] : null), $in, true, function($cx, $in)use($sp){return ''.LCRun3::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in)use($sp){return '				<!-- eachPost topic -->
'.$sp.'				'.LCRun3::ch($cx, 'post', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), 'encq').'
'.$sp.'';}).'';}).'' : '').'
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? ''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['submitted']['postId']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in)use($sp){return ''.LCRun3::p($cx, 'flow_reply_form', array(array($in),array()), '			').'';}, function($cx, $in)use($sp){return ''.LCRun3::hbch($cx, 'progressiveEnhancement', array(array(),array('type'=>'replace','target'=>'~ a')), $in, false, function($cx, $in)use($sp){return ''.LCRun3::p($cx, 'flow_reply_form', array(array($in),array()), '				').'';}).'			<a href="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   class="flow-ui-input-replacement-anchor mw-ui-input"
'.$sp.'			>'.LCRun3::ch($cx, 'l10n', array(array('flow-reply-topic-title-placeholder',((isset($in['properties']['topic-of-post']) && is_array($in['properties'])) ? $in['properties']['topic-of-post'] : null)),array()), 'encq').'</a>
'.$sp.'';}).'' : '').'</div>
';},'flow_topiclist_loop' => function ($cx, $in, $sp) {return ''.$sp.''.LCRun3::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), $in, true, function($cx, $in)use($sp){return ''.LCRun3::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in)use($sp){return ''.LCRun3::p($cx, 'flow_topic', array(array($in),array()), '		').'';}).'';}).'';},'flow_load_more' => function ($cx, $in, $sp) {return ''.$sp.''.((LCRun3::ifvar($cx, ((isset($in['loadMoreObject']) && is_array($in)) ? $in['loadMoreObject'] : null))) ? '	<div class="flow-load-more">
'.$sp.'		<div class="flow-error-container">
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<a data-flow-interactive-handler="apiRequest"
'.$sp.'		   data-flow-api-handler="'.htmlentities((string)((isset($in['loadMoreApiHandler']) && is_array($in)) ? $in['loadMoreApiHandler'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'		   data-flow-api-target="< .flow-load-more"
'.$sp.'		   data-flow-load-handler="loadMore"
'.$sp.'		   data-flow-scroll-target="'.htmlentities((string)((isset($in['loadMoreTarget']) && is_array($in)) ? $in['loadMoreTarget'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'		   data-flow-scroll-container="'.htmlentities((string)((isset($in['loadMoreContainer']) && is_array($in)) ? $in['loadMoreContainer'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'		   data-flow-template="'.htmlentities((string)((isset($in['loadMoreTemplate']) && is_array($in)) ? $in['loadMoreTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'		   href="'.htmlentities((string)((isset($in['loadMoreObject']['url']) && is_array($in['loadMoreObject'])) ? $in['loadMoreObject']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'		   title="'.htmlentities((string)((isset($in['loadMoreObject']['title']) && is_array($in['loadMoreObject'])) ? $in['loadMoreObject']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'		   class="mw-ui-button mw-ui-progressive flow-load-interactive flow-ui-fallback-element"><span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-article"></span> '.LCRun3::ch($cx, 'l10n', array(array('flow-load-more'),array()), 'encq').'</a>
'.$sp.'	</div>
'.$sp.'' : '	<div class="flow-no-more">
'.$sp.'		'.LCRun3::ch($cx, 'l10n', array(array('flow-no-more-fwd'),array()), 'encq').'
'.$sp.'	</div>
'.$sp.'').'';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return ''.LCRun3::p($cx, 'flow_board_navigation', array(array($in),array())).'
<div class="flow-board" data-flow-sortby="'.htmlentities((string)((isset($in['sortby']) && is_array($in)) ? $in['sortby'] : null), ENT_QUOTES, 'UTF-8').'">
	<div class="flow-newtopic-container">
		<div class="flow-nojs">
			<a class="mw-ui-input mw-ui-input-large flow-ui-input-replacement-anchor"
				href="'.htmlentities((string)((isset($in['links']['newtopic']) && is_array($in['links'])) ? $in['links']['newtopic'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('flow-newtopic-start-placeholder'),array()), 'encq').'</a>
		</div>

		<div class="flow-js">
'.LCRun3::p($cx, 'flow_newtopic_form', array(array($in),array('isOnFlowBoard'=>true)), '			').'		</div>
	</div>

	<div class="flow-topics">
'.LCRun3::p($cx, 'flow_topiclist_loop', array(array($in),array()), '		').'
'.LCRun3::p($cx, 'flow_load_more', array(array($in),array('loadMoreApiHandler'=>'loadMoreTopics','loadMoreTarget'=>'window','loadMoreContainer'=>'< .flow-topics','loadMoreTemplate'=>'flow_topiclist_loop.partial','loadMoreObject'=>((isset($in['links']['pagination']['fwd']) && is_array($in['links']['pagination'])) ? $in['links']['pagination']['fwd'] : null))), '		').'	</div>
</div>
';
}
?>