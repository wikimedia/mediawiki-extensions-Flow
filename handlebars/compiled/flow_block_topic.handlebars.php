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
            'linkWithReturnTo' => 'Flow\TemplateHelper::linkWithReturnTo',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
            'plaintextSnippet' => 'Flow\TemplateHelper::plaintextSnippet',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
            'ifCond' => 'Flow\TemplateHelper::ifCond',
            'tooltip' => 'Flow\TemplateHelper::tooltip',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
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
';},'flow_topic_moderation_flag' => function ($cx, $in) {return '<span class="wikiglyph'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','lock'),Array()), $in, function($cx, $in) {return ' wikiglyph-lock';}).''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','hide'),Array()), $in, function($cx, $in) {return ' wikiglyph-flag';}).''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','delete'),Array()), $in, function($cx, $in) {return ' wikiglyph-trash';}).'"></span>
';},'flow_topic_titlebar_summary' => function ($cx, $in) {return '<div class="flow-topic-summary-container">
	'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'
	'.((LCRun3::ifvar($cx, ((isset($in['summary']) && is_array($in)) ? $in['summary'] : null))) ? '
		<div class="flow-topic-summary">
			'.LCRun3::ch($cx, 'escapeContent', Array(Array(((isset($in['summary']['format']) && is_array($in['summary'])) ? $in['summary']['format'] : null),((isset($in['summary']['content']) && is_array($in['summary'])) ? $in['summary']['content'] : null)),Array()), 'encq').'
		</div>
	' : '').'
</div>
';},'flow_topic_titlebar_content' => function ($cx, $in) {return '<h2 class="flow-topic-title" data-title="'.LCRun3::ch($cx, 'plaintextSnippet', Array(Array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),Array()), 'encq').'">'.LCRun3::ch($cx, 'escapeContent', Array(Array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),Array()), 'encq').'</h2>
<div class="flow-topic-meta">
	'.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? '
		<a href="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
		   data-flow-interactive-handler="activateForm">'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		&bull;
	' : '').'

	'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-comments',((isset($in['reply_count']) && is_array($in)) ? $in['reply_count'] : null)),Array()), 'encq').' &bull;

	'.((LCRun3::ifvar($cx, ((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null))) ? '
		'.LCRun3::ch($cx, 'timestamp', Array(Array(((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null),'flow-active-ago',false,((isset($in['last_updated_readable']) && is_array($in)) ? $in['last_updated_readable'] : null)),Array()), 'encq').'
	' : '
		'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((isset($in['postId']) && is_array($in)) ? $in['postId'] : null),'flow-started-ago',false),Array()), 'encq').'
	').'
</div>
'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '
	<div class="flow-moderated-topic-title flow-ui-text-truncated">'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').''.LCRun3::p($cx, 'flow_topic_moderation_flag', Array(Array($in),Array())).'
		'.LCRun3::ch($cx, 'l10n', Array(Array('post_moderation_state',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),((isset($in['replyToId']) && is_array($in)) ? $in['replyToId'] : null),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null)),Array()), 'encq').'</div>
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
	   data-flow-api-handler="watchTopic"
	   data-flow-api-target="< .flow-topic-watchlist"
	   data-flow-api-method="POST">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-star"></span>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').''.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-unstar"></span>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</a>
</div>
';},'flow_topic_titlebar' => function ($cx, $in) {return '<div class="flow-topic-titlebar flow-click-interactive" data-flow-interactive-handler="collapserCollapsibleToggle" tabindex="0">
	'.LCRun3::p($cx, 'flow_topic_titlebar_content', Array(Array($in),Array())).'

	'.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? '
		'.((LCRun3::ifvar($cx, ((isset($in['watchable']) && is_array($in)) ? $in['watchable'] : null))) ? '
			'.LCRun3::p($cx, 'flow_topic_titlebar_watch', Array(Array($in),Array())).'
		' : '').'
		<div class="flow-menu">
			<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
			<ul class="mw-ui-button-container flow-list">
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '
					<li class="flow-menu-edit-action">
						<a class="mw-ui-button mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
						   data-flow-interactive-handler="editTopicTitle"
								>
							<span class="wikiglyph wikiglyph-pencil"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-edit-title'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['links']['topic-history']) && is_array($in['links'])) ? $in['links']['topic-history'] : null))) ? '
					<li>
						<a class="mw-ui-button mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['links']['topic-history']['title']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-clock"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-history'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null))) ? '
					<li>
						<a class="mw-ui-button mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-link"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-view'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['summarize']) && is_array($in['actions'])) ? $in['actions']['summarize'] : null))) ? '
					<li class="flow-menu-edit-action">
						<a class="mw-ui-button mw-ui-progressive mw-ui-quiet"
						   data-flow-interactive-handler="apiRequest"
						   data-flow-api-handler="activateSummarizeTopic"
						   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
						   href="'.htmlentities((string)((isset($in['actions']['summarize']['url']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['summarize']['title']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-stripe-toc"></span>
							'.((LCRun3::ifvar($cx, ((isset($in['summary']) && is_array($in)) ? $in['summary'] : null))) ? '
								'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-resummarize-topic'),Array()), 'encq').'
							' : '
								'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-summarize-topic'),Array()), 'encq').'
							').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['hide']) && is_array($in['actions'])) ? $in['actions']['hide'] : null))) ? '
					<li class="flow-menu-moderation-action">
						<a class="mw-ui-button mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['actions']['hide']['url']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['hide']['title']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
						   data-flow-interactive-handler="moderationDialog"
						   data-template="flow_moderate_topic"
						   data-role="hide">
							<span class="wikiglyph wikiglyph-flag"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-hide-topic'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null))) ? '
					<li class="flow-menu-moderation-action">
						<a class="mw-ui-button mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['unhide']['title']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
						   data-flow-interactive-handler="moderationDialog"
						   data-template="flow_moderate_topic"
						   data-role="unhide">
							<span class="wikiglyph wikiglyph-flag"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-unhide-topic'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['delete']) && is_array($in['actions'])) ? $in['actions']['delete'] : null))) ? '
					<li class="flow-menu-moderation-action">
						<a class="mw-ui-button mw-ui-destructive mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['actions']['delete']['url']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['delete']['title']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
						   data-flow-interactive-handler="moderationDialog"
						   data-template="flow_moderate_topic"
						   data-role="delete">
							<span class="wikiglyph wikiglyph-trash"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-delete-topic'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['undelete']) && is_array($in['actions'])) ? $in['actions']['undelete'] : null))) ? '
					<li class="flow-menu-moderation-action">
						<a class="mw-ui-button mw-ui-destructive mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['actions']['undelete']['url']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['undelete']['title']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
						   data-flow-interactive-handler="moderationDialog"
						   data-template="flow_moderate_topic"
						   data-role="undelete">
							<span class="wikiglyph wikiglyph-trash"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-undelete-topic'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['suppress']) && is_array($in['actions'])) ? $in['actions']['suppress'] : null))) ? '
					<li class="flow-menu-moderation-action">
						<a class="mw-ui-button mw-ui-destructive mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['actions']['suppress']['url']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['suppress']['title']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
						   data-flow-interactive-handler="moderationDialog"
						   data-template="flow_moderate_topic"
						   data-role="suppress">
							<span class="wikiglyph wikiglyph-block"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-suppress-topic'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions'])) ? $in['actions']['unsuppress'] : null))) ? '
					<li class="flow-menu-moderation-action">
						<a class="mw-ui-button mw-ui-destructive mw-ui-quiet"
						   href="'.htmlentities((string)((isset($in['actions']['unsuppress']['url']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['unsuppress']['title']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
						   data-flow-interactive-handler="moderationDialog"
						   data-template="flow_moderate_topic"
						   data-role="unsuppress">
							<span class="wikiglyph wikiglyph-block"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-unsuppress-topic'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '
					<li class="flow-menu-moderation-action">
						<a class="mw-ui-button mw-ui-destructive mw-ui-quiet"
						   data-flow-interactive-handler="apiRequest"
						   data-flow-api-handler="activateLockTopic"
						   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
						   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
						   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-lock"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-lock-topic'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '
					<li class="flow-menu-moderation-action">
						<a class="mw-ui-button mw-ui-destructive mw-ui-quiet"
						   data-flow-interactive-handler="apiRequest"
						   data-flow-api-handler="activateLockTopic"
						   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
						   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
						   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-unlock"></span>
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-unlock-topic'),Array()), 'encq').'
						</a>
					</li>
				' : '').'
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
				class="mw-ui-input"
				type="text"
				placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-reply-topic-title-placeholder',((isset($in['properties']['topic-of-post']) && is_array($in['properties'])) ? $in['properties']['topic-of-post'] : null)),Array()), 'encq').'"
				data-role="content">'.((LCRun3::ifvar($cx, ((isset($cx['scopes'][0]['submitted']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['submitted'] : null))) ? ''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($cx['scopes'][0]['submitted']['postId']) && is_array($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return ''.htmlentities((string)((isset($cx['scopes'][0]['submitted']['content']) && is_array($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'';}).'' : '').'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit"
			        class="mw-ui-button mw-ui-constructive"
			        data-flow-interactive-handler="apiRequest"
			        data-flow-api-handler="submitReply"
			        data-flow-api-target="< .flow-topic">'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</button>
			'.LCRun3::p($cx, 'flow_form_buttons', Array(Array($in),Array())).'
			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-reply'),Array()), 'encq').'</small>
		</div>
	</form>
' : '').'
';},'flow_topic' => function ($cx, $in) {return '<div class="flow-topic flow-element-collapsible
            '.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? 'flow-load-interactive' : '').'
            '.((LCRun3::ifvar($cx, ((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null))) ? 'flow-topic-moderatestate-'.htmlentities((string)((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null), ENT_QUOTES, 'UTF-8').'' : '').'
            '.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? 'flow-topic-moderated
              '.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'!==','lock'),Array()), $in, function($cx, $in) {return 'flow-element-collapsed';}).'
            ' : '').'"
     id="flow-topic-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
     data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
     data-flow-load-handler="collapserState"
     data-flow-collapser-set="topics"
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
';}).'';},),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board flow-disable-topic-collapse">
	<div class="flow-topics">
		'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'

		
		'.LCRun3::p($cx, 'flow_topiclist_loop', Array(Array($in),Array())).'
	</div>
</div>
';
}
?>