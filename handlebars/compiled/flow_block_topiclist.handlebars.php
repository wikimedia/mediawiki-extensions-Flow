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
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'timestamp' => 'Flow\TemplateHelper::timestampHelper',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'post' => 'Flow\TemplateHelper::post',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'concat' => 'Flow\TemplateHelper::concat',
            'linkWithReturnTo' => 'Flow\TemplateHelper::linkWithReturnTo',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
            'ifCond' => 'Flow\TemplateHelper::ifCond',
            'tooltip' => 'Flow\TemplateHelper::tooltip',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
        'partials' => Array('flow_board_navigation' => function ($cx, $in) {return '
<div class="flow-board-navigation flow-load-interactive" data-flow-load-handler="boardNavigation">
	<div class="flow-error-container">
		
	</div>
	<div class="flow-board-navigation-inner">
		
		<a href="javascript:void(0);"
		   class="flow-board-navigator-active flow-board-navigator-first flow-ui-tooltip-target"
		   data-tooltip-pointing="down"
		   title="'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['sortby']) && is_array($in)) ? $in['sortby'] : null),'===','updated'),Array()), $in, function($cx, $in) {return ''.LCRun3::ch($cx, 'l10n', Array(Array('flow-sorting-tooltip-recent'),Array()), 'encq').'';}, function($cx, $in) {return ''.LCRun3::ch($cx, 'l10n', Array(Array('flow-sorting-tooltip-newest'),Array()), 'encq').'';}).'"
		   data-flow-interactive-handler="menuToggle"
		   data-flow-menu-target="< .flow-board-navigation .flow-menu">'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['sortby']) && is_array($in)) ? $in['sortby'] : null),'===','updated'),Array()), $in, function($cx, $in) {return '
				'.LCRun3::ch($cx, 'l10n', Array(Array('flow-recent-topics'),Array()), 'encq').'
			';}, function($cx, $in) {return '
				'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newest-topics'),Array()), 'encq').'
			';}).'
			<span class="wikiglyph wikiglyph-caret-down"></span>
		</a>
	</div>
	<div class="flow-board-filter-menu">
		<div class="flow-menu flow-menu-inverted">
			<div class="flow-menu-js-drop flow-menu-js-drop-hidden"><a href="javascript:void(0);" class="flow-board-filter-menu-activator"></a></div>
			'.((LCRun3::ifvar($cx, ((isset($in['links']['board-sort']) && is_array($in['links'])) ? $in['links']['board-sort'] : null))) ? '
				<ul class="mw-ui-button-container flow-list">'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['sortby']) && is_array($in)) ? $in['sortby'] : null),'===','updated'),Array()), $in, function($cx, $in) {return '
					<li><a class="mw-ui-button mw-ui-quiet"
					       href="'.htmlentities((string)((isset($in['links']['board-sort']['newest']) && is_array($in['links']['board-sort'])) ? $in['links']['board-sort']['newest'] : null), ENT_QUOTES, 'UTF-8').'"
					       data-flow-interactive-handler="apiRequest"
					       data-flow-api-target="< .flow-component"
					       data-flow-api-handler="board"><span class="wikiglyph wikiglyph-star-circle"></span> '.LCRun3::ch($cx, 'l10n', Array(Array('flow-newest-topics'),Array()), 'encq').'</a></li>
					';}, function($cx, $in) {return '
					<li><a class="mw-ui-button mw-ui-quiet"
					       href="'.htmlentities((string)((isset($in['links']['board-sort']['updated']) && is_array($in['links']['board-sort'])) ? $in['links']['board-sort']['updated'] : null), ENT_QUOTES, 'UTF-8').'"
					       data-flow-interactive-handler="apiRequest"
					       data-flow-api-target="< .flow-component"
					       data-flow-api-handler="board"><span class="wikiglyph wikiglyph-clock"></span> '.LCRun3::ch($cx, 'l10n', Array(Array('flow-recent-topics'),Array()), 'encq').'</a></li>
					';}).'
				</ul>
			' : '').'
		</div>
	</div>
</div>
';},'flow_errors' => function ($cx, $in) {return '<div class="flow-error-container">
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
';},'flow_anon_warning' => function ($cx, $in) {return '<div class="flow-anon-warning">
	<div class="flow-anon-warning-mobile">
		
		'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
			'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
	</div>

	
	'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array()), $in, function($cx, $in) {return '
		<div class="flow-anon-warning-desktop">
			'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
				'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
		</div>
	';}).'
</div>';},'flow_form_buttons' => function ($cx, $in) {return '<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right flow-js"

        
>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-preview'),Array()), 'encq').'</button>

<button data-flow-interactive-handler="cancelForm"
        data-role="cancel"
        type="reset"
        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"

        
        
>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>
';},'flow_newtopic_form' => function ($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['newtopic']) && is_array($in['actions'])) ? $in['actions']['newtopic'] : null))) ? '
	<form action="'.htmlentities((string)((isset($in['actions']['newtopic']['url']) && is_array($in['actions']['newtopic'])) ? $in['actions']['newtopic']['url'] : null), ENT_QUOTES, 'UTF-8').'" method="POST" class="flow-newtopic-form" data-flow-initial-state="collapsed">
		'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'

		'.LCRun3::hbch($cx, 'ifAnonymous', Array(Array(),Array()), $in, function($cx, $in) {return '
			'.LCRun3::p($cx, 'flow_anon_warning', Array(Array($in),Array())).'
		';}).'

		<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['scopes'][0]['editToken']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topiclist_replyTo" value="'.htmlentities((string)((isset($in['workflowId']) && is_array($in)) ? $in['workflowId'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input name="topiclist_topic" class="mw-ui-input mw-ui-input-large"
			required
			type="text"
			placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-start-placeholder'),Array()), 'encq').'"
			data-role="title"

			
			data-flow-interactive-handler-focus="activateNewTopic"
		/>
		<textarea name="topiclist_content"
			data-flow-preview-template="flow_topic"
			class="mw-ui-input flow-form-collapsible mw-ui-input-large"
			'.((LCRun3::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null))) ? 'style="display:none;"' : '').'
			placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-content-placeholder',((isset($cx['scopes'][0]['title']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['title'] : null)),Array()), 'encq').'"
			data-role="content"
			required
		></textarea>

		<div class="flow-form-actions flow-form-collapsible"
			'.((LCRun3::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null))) ? 'style="display:none;"' : '').'>
			<button data-role="submit" data-flow-api-handler="newTopic"
				data-flow-interactive-handler="apiRequest"
				data-flow-eventlog-action="save-attempt"
				class="mw-ui-button mw-ui-constructive mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-save'),Array()), 'encq').'</button>
			'.LCRun3::p($cx, 'flow_form_buttons', Array(Array($in),Array())).'
			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-new-topic'),Array()), 'encq').'</small>
		</div>
	</form>
' : '').'
';},'flow_topic_moderation_flag' => function ($cx, $in) {return '<span class="wikiglyph'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','lock'),Array()), $in, function($cx, $in) {return ' wikiglyph-lock';}).''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','hide'),Array()), $in, function($cx, $in) {return ' wikiglyph-flag';}).''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','delete'),Array()), $in, function($cx, $in) {return ' wikiglyph-trash';}).'"></span>
';},'flow_post_moderation_state' => function ($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['replyToId']) && is_array($in)) ? $in['replyToId'] : null))) ? ''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'-post-content'),Array()), 'raw'),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null)),Array()), 'encq').'
' : ''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'-title-content'),Array()), 'raw'),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null)),Array()), 'encq').'
').'';},'flow_topic_titlebar_summary' => function ($cx, $in) {return '<div class="flow-topic-summary-container">
	'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'
	'.((LCRun3::ifvar($cx, ((isset($in['summary']) && is_array($in)) ? $in['summary'] : null))) ? '
		<div class="flow-topic-summary">
			'.LCRun3::ch($cx, 'escapeContent', Array(Array(((isset($in['summary']['format']) && is_array($in['summary'])) ? $in['summary']['format'] : null),((isset($in['summary']['content']) && is_array($in['summary'])) ? $in['summary']['content'] : null)),Array()), 'encq').'
		</div>
		<br class="flow-ui-clear"/>
	' : '').'
</div>
';},'flow_topic_titlebar_content' => function ($cx, $in) {return '<h2 class="flow-topic-title">'.LCRun3::ch($cx, 'escapeContent', Array(Array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),Array()), 'encq').'</h2>
<div class="flow-topic-meta">
	'.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? '
		<a href="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
		   data-flow-interactive-handler="activateForm"

		   
		   data-flow-eventlog-schema="FlowReplies"
		   data-flow-eventlog-action="initiate"
		   data-flow-eventlog-entrypoint="reply-top"
		   data-flow-eventlog-forward="
		       < .flow-topic .flow-reply-form:last [data-role=\'cancel\'],
		       < .flow-topic .flow-reply-form:last [data-role=\'action\'][name=\'preview\'],
		       < .flow-topic .flow-reply-form:last [data-role=\'submit\']
		   "
		>'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		&bull;
	' : '').'

	'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-comments',((isset($in['reply_count']) && is_array($in)) ? $in['reply_count'] : null)),Array()), 'encq').' &bull;

	'.((LCRun3::ifvar($cx, ((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null))) ? '
		'.LCRun3::ch($cx, 'timestamp', Array(Array(((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null)),Array()), 'encq').'
	' : '
		'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),Array()), 'encq').'
	').'
</div>
'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '
	<div class="flow-moderated-topic-title flow-ui-text-truncated">'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').''.LCRun3::p($cx, 'flow_topic_moderation_flag', Array(Array($in),Array())).'
		'.LCRun3::p($cx, 'flow_post_moderation_state', Array(Array($in),Array())).'
	</div>
	<div class="flow-moderated-topic-reason">
		'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-moderated-reason-prefix'),Array()), 'encq').'
		'.LCRun3::ch($cx, 'escapeContent', Array(Array(((isset($in['moderateReason']['format']) && is_array($in['moderateReason'])) ? $in['moderateReason']['format'] : null),((isset($in['moderateReason']['content']) && is_array($in['moderateReason'])) ? $in['moderateReason']['content'] : null)),Array()), 'encq').'
	</div>
' : '').'
<span class="flow-reply-count"><span class="wikiglyph wikiglyph-speech-bubble"></span><span class="flow-reply-count-number">'.htmlentities((string)((isset($in['reply_count']) && is_array($in)) ? $in['reply_count'] : null), ENT_QUOTES, 'UTF-8').'</span></span>

'.LCRun3::p($cx, 'flow_topic_titlebar_summary', Array(Array($in),Array())).'
';},'flow_topic_titlebar_watch' => function ($cx, $in) {return '<div class="flow-topic-watchlist flow-watch-link">
	'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'

	<a href="'.((LCRun3::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null))) ? ''.htmlentities((string)((isset($in['links']['unwatch-topic']['url']) && is_array($in['links']['unwatch-topic'])) ? $in['links']['unwatch-topic']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['links']['watch-topic']['url']) && is_array($in['links']['watch-topic'])) ? $in['links']['watch-topic']['url'] : null), ENT_QUOTES, 'UTF-8').'').'"
	   class="mw-ui-anchor mw-ui-constructive '.((!LCRun3::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null))) ? 'mw-ui-quiet' : '').'
	   '.((LCRun3::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null))) ? 'flow-watch-link-unwatch' : 'flow-watch-link-watch').'"
	   data-flow-api-handler="watchItem"
	   data-flow-api-target="< .flow-topic-watchlist"
	   data-flow-api-method="POST">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-star"></span>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').''.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-unstar"></span>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</a>
</div>
';},'flow_moderation_actions_list' => function ($cx, $in) {return ''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','topic'),Array()), $in, function($cx, $in) {return '
	'.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-edit-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-interactive-handler="apiRequest"
			   data-flow-api-handler="activateEditTitle"
			   data-flow-api-target="< .flow-topic-titlebar"
			>'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-pencil"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-edit-title'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['topic-history']) && is_array($in['links'])) ? $in['links']['topic-history'] : null))) ? '<li>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['links']['topic-history']['title']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-clock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-history'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null))) ? '<li>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-link"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-view'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['summarize']) && is_array($in['actions'])) ? $in['actions']['summarize'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-edit-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-progressive mw-ui-quiet"
			   data-flow-interactive-handler="apiRequest"
			   data-flow-api-handler="activateSummarizeTopic"
			   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
			   href="'.htmlentities((string)((isset($in['actions']['summarize']['url']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['summarize']['title']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-stripe-toc"></span> ' : '').''.((LCRun3::ifvar($cx, ((isset($in['summary']) && is_array($in)) ? $in['summary'] : null))) ? ''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-resummarize-topic'),Array()), 'raw')),Array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-summarize-topic'),Array()), 'raw')),Array()), 'encq').'').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','post'),Array()), $in, function($cx, $in) {return '
	'.((LCRun3::ifvar($cx, ((isset($in['links']['post']) && is_array($in['links'])) ? $in['links']['post'] : null))) ? '<li>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['links']['post']['url']) && is_array($in['links']['post'])) ? $in['links']['post']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['links']['post']['title']) && is_array($in['links']['post'])) ? $in['links']['post']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-link"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-view'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).''.((LCRun3::ifvar($cx, ((isset($in['actions']['hide']) && is_array($in['actions'])) ? $in['actions']['hide'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['hide']['url']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['hide']['title']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="hide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-hide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['unhide']['title']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="unhide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unhide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['delete']) && is_array($in['actions'])) ? $in['actions']['delete'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['delete']['url']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['delete']['title']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="delete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-trash"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-delete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['undelete']) && is_array($in['actions'])) ? $in['actions']['undelete'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['undelete']['url']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['undelete']['title']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="undelete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-trash"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-undelete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['suppress']) && is_array($in['actions'])) ? $in['actions']['suppress'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['suppress']['url']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['suppress']['title']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="suppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-block"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-suppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions'])) ? $in['actions']['unsuppress'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['unsuppress']['url']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['unsuppress']['title']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="unsuppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-block"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unsuppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),Array()), $in, function($cx, $in) {return '
	'.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   data-flow-interactive-handler="moderationDialog"
			   data-template="flow_moderate_topic"
			   data-role="lock"
			   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
			   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-lock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   data-flow-interactive-handler="moderationDialog"
			   data-template="flow_moderate_topic"
			   data-role="unlock"
			   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
			   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-unlock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}, function($cx, $in) {return '
	
	'.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   data-flow-interactive-handler="apiRequest"
			   data-flow-api-handler="activateLockTopic"
			   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
			   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-lock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   data-flow-interactive-handler="apiRequest"
			   data-flow-api-handler="activateLockTopic"
			   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
			   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-unlock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).'';},'flow_topic_titlebar' => function ($cx, $in) {return '<div class="flow-topic-titlebar">
	'.LCRun3::p($cx, 'flow_topic_titlebar_content', Array(Array($in),Array())).'

	'.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? '
		'.((LCRun3::ifvar($cx, ((isset($in['watchable']) && is_array($in)) ? $in['watchable'] : null))) ? '
			'.LCRun3::p($cx, 'flow_topic_titlebar_watch', Array(Array($in),Array())).'
		' : '').'
		<div class="flow-menu">
			<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
			<ul class="mw-ui-button-container flow-list">
				
				'.LCRun3::p($cx, 'flow_moderation_actions_list', Array(Array($in),Array('moderationType'=>'topic','moderationTarget'=>'title','moderationTemplate'=>'topic','moderationContainerClass'=>'flow-menu','moderationMwUiClass'=>'mw-ui-button','moderationIcons'=>true))).'
			</ul>
		</div>
	' : '').'
</div>
';},'flow_reply_form' => function ($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? '
	<form class="flow-post flow-reply-form"
	      method="POST"
	      action="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
	      id="flow-reply-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	      data-flow-initial-state="collapsed"
	>
		<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['scopes'][0]['rootBlock']['editToken']) && is_array($cx['scopes'][0]['rootBlock'])) ? $cx['scopes'][0]['rootBlock']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topic_replyTo" value="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />
		'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'

		'.LCRun3::hbch($cx, 'ifAnonymous', Array(Array(),Array()), $in, function($cx, $in) {return '
			'.LCRun3::p($cx, 'flow_anon_warning', Array(Array($in),Array())).'
		';}).'

		<textarea id="flow-post-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content"
				name="topic_content"
				required
				data-flow-preview-template="flow_post"
				data-flow-expandable="true"
				class="mw-ui-input flow-click-interactive"
				type="text"
				placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-reply-topic-title-placeholder',((isset($in['properties']['topic-of-post']) && is_array($in['properties'])) ? $in['properties']['topic-of-post'] : null)),Array()), 'encq').'"
				data-role="content"

				
				data-flow-interactive-handler-focus="activateReplyTopic"
		>'.((LCRun3::ifvar($cx, ((isset($cx['scopes'][0]['submitted']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['submitted'] : null))) ? ''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($cx['scopes'][0]['submitted']['postId']) && is_array($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return ''.htmlentities((string)((isset($cx['scopes'][0]['submitted']['content']) && is_array($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'';}).'' : '').'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit"
			        class="mw-ui-button mw-ui-constructive"
			        data-flow-interactive-handler="apiRequest"
			        data-flow-api-handler="submitReply"
			        data-flow-api-target="< .flow-topic"
			        data-flow-eventlog-action="save-attempt"
			>'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</button>
			'.LCRun3::p($cx, 'flow_form_buttons', Array(Array($in),Array())).'
			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-reply'),Array()), 'encq').'</small>
		</div>
	</form>
' : '').'
';},'flow_topic' => function ($cx, $in) {return '<div class="flow-topic
            '.((LCRun3::ifvar($cx, ((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null))) ? 'flow-topic-moderatestate-'.htmlentities((string)((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null), ENT_QUOTES, 'UTF-8').'' : '').'
            '.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? 'flow-topic-moderated' : '').'
            "
     id="flow-topic-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
     data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
>
	'.LCRun3::p($cx, 'flow_topic_titlebar', Array(Array($in),Array())).'

	'.((LCRun3::ifvar($cx, ((isset($cx['scopes'][0]['posts']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['posts'] : null))) ? '
		'.LCRun3::sec($cx, ((isset($in['replies']) && is_array($in)) ? $in['replies'] : null), $in, true, function($cx, $in) {return '
			'.LCRun3::hbch($cx, 'eachPost', Array(Array($cx['scopes'][0],$in),Array()), $in, function($cx, $in) {return '
				<!-- eachPost topic -->
				'.LCRun3::ch($cx, 'post', Array(Array($cx['scopes'][0],$in),Array()), 'encq').'
			';}).'
		';}).'
	' : '').'

	'.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? '
		'.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? '
			'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($cx['scopes'][0]['submitted']['postId']) && is_array($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return '
				'.LCRun3::p($cx, 'flow_reply_form', Array(Array($in),Array())).'
			';}, function($cx, $in) {return '
				'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array('type'=>'replace','target'=>'~ a')), $in, function($cx, $in) {return '
					'.LCRun3::p($cx, 'flow_reply_form', Array(Array($in),Array())).'
				';}).'
				<a href="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   class="flow-ui-input-replacement-anchor mw-ui-input"
				>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-reply-topic-title-placeholder',((isset($in['properties']['topic-of-post']) && is_array($in['properties'])) ? $in['properties']['topic-of-post'] : null)),Array()), 'encq').'</a>
			';}).'
		' : '').'
	' : '').'
</div>
';},'flow_topiclist_loop' => function ($cx, $in) {return ''.LCRun3::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
	
	'.LCRun3::hbch($cx, 'eachPost', Array(Array($cx['scopes'][0],$in),Array()), $in, function($cx, $in) {return '
		'.LCRun3::p($cx, 'flow_topic', Array(Array($in),Array())).'
	';}).'
';}).'';},'flow_no_more' => function ($cx, $in) {return '<div class="flow-no-more">
	'.LCRun3::ch($cx, 'l10n', Array(Array('flow-no-more-fwd'),Array()), 'encq').'
</div>';},'flow_load_more' => function ($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['links']['pagination']['fwd']) && is_array($in['links']['pagination'])) ? $in['links']['pagination']['fwd'] : null))) ? '
	<div class="flow-load-more">
		<div class="flow-error-container">
			
		</div>

		<a data-flow-interactive-handler="apiRequest"
		   data-flow-api-handler="loadMore"
		   data-flow-load-handler="loadMore"
		   href="'.htmlentities((string)((isset($in['links']['pagination']['fwd']['url']) && is_array($in['links']['pagination']['fwd'])) ? $in['links']['pagination']['fwd']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['links']['pagination']['fwd']['title']) && is_array($in['links']['pagination']['fwd'])) ? $in['links']['pagination']['fwd']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   class="mw-ui-button mw-ui-progressive flow-load-interactive flow-ui-fallback-element"><span class="wikiglyph wikiglyph-article"></span> '.LCRun3::ch($cx, 'l10n', Array(Array('flow-load-more'),Array()), 'encq').'</a>
	</div>
' : '
	'.LCRun3::p($cx, 'flow_no_more', Array(Array($in),Array())).'
').'
';},),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return ''.LCRun3::p($cx, 'flow_board_navigation', Array(Array($in),Array())).'

<div class="flow-board">
	<div class="flow-newtopic-container">
		
		<div class="flow-nojs">
			<a class="mw-ui-input mw-ui-input-large flow-ui-input-replacement-anchor"
				href="'.htmlentities((string)((isset($in['links']['newtopic']) && is_array($in['links'])) ? $in['links']['newtopic'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-start-placeholder'),Array()), 'encq').'</a>
		</div>

		<div class="flow-js">
			'.LCRun3::p($cx, 'flow_newtopic_form', Array(Array($in),Array('isOnFlowBoard'=>true))).'
		</div>
	</div>

	<div class="flow-topics">
		'.LCRun3::p($cx, 'flow_topiclist_loop', Array(Array($in),Array())).'

		'.LCRun3::p($cx, 'flow_load_more', Array(Array($in),Array())).'
	</div>
</div>
';
}
?>