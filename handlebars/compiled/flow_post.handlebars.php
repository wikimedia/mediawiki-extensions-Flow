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
';},'flow_post_author' => function ($cx, $in) {return '<span class="flow-author">
	'.((LCRun3::ifvar($cx, ((isset($in['links']) && is_array($in)) ? $in['links'] : null))) ? '
		'.((LCRun3::ifvar($cx, ((isset($in['links']['userpage']) && is_array($in['links'])) ? $in['links']['userpage'] : null))) ? '
			<a href="'.htmlentities((string)((isset($in['links']['userpage']['url']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   '.((!LCRun3::ifvar($cx, ((isset($in['name']) && is_array($in)) ? $in['name'] : null))) ? 'title="'.htmlentities((string)((isset($in['links']['userpage']['title']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['title'] : null), ENT_QUOTES, 'UTF-8').'"' : '').'
			   class="'.((!LCRun3::ifvar($cx, ((isset($in['links']['userpage']['exists']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['exists'] : null))) ? 'new ' : '').'mw-userlink">
		' : '').'
		'.((LCRun3::ifvar($cx, ((isset($in['name']) && is_array($in)) ? $in['name'] : null))) ? ''.htmlentities((string)((isset($in['name']) && is_array($in)) ? $in['name'] : null), ENT_QUOTES, 'UTF-8').'' : ''.LCRun3::ch($cx, 'l10n', Array(Array('flow-anonymous'),Array()), 'encq').'
		').''.((LCRun3::ifvar($cx, ((isset($in['links']['userpage']) && is_array($in['links'])) ? $in['links']['userpage'] : null))) ? '</a>' : '').'<span class="mw-usertoollinks flow-pipelist">'.((LCRun3::ifvar($cx, ((isset($in['links']['talk']) && is_array($in['links'])) ? $in['links']['talk'] : null))) ? '<span><a href="'.htmlentities((string)((isset($in['links']['talk']['url']) && is_array($in['links']['talk'])) ? $in['links']['talk']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				    class="'.((!LCRun3::ifvar($cx, ((isset($in['links']['talk']['exists']) && is_array($in['links']['talk'])) ? $in['links']['talk']['exists'] : null))) ? 'new ' : '').'"
				    title="'.htmlentities((string)((isset($in['links']['talk']['title']) && is_array($in['links']['talk'])) ? $in['links']['talk']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('talkpagelinktext'),Array()), 'encq').'</a></span>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['contribs']) && is_array($in['links'])) ? $in['links']['contribs'] : null))) ? '<span>
					<a href="'.htmlentities((string)((isset($in['links']['contribs']['url']) && is_array($in['links']['contribs'])) ? $in['links']['contribs']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities((string)((isset($in['links']['contribs']['title']) && is_array($in['links']['contribs'])) ? $in['links']['contribs']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('contribslink'),Array()), 'encq').'</a>
				</span>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['block']) && is_array($in['links'])) ? $in['links']['block'] : null))) ? '<span><a class="'.((!LCRun3::ifvar($cx, ((isset($in['links']['block']['exists']) && is_array($in['links']['block'])) ? $in['links']['block']['exists'] : null))) ? 'new ' : '').'"
				   href="'.htmlentities((string)((isset($in['links']['block']['url']) && is_array($in['links']['block'])) ? $in['links']['block']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['links']['block']['title']) && is_array($in['links']['block'])) ? $in['links']['block']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('blocklink'),Array()), 'encq').'</a></span>' : '').'</span>
	' : '').'
</span>
';},'flow_post_moderation_state' => function ($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['replyToId']) && is_array($in)) ? $in['replyToId'] : null))) ? ''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'-post-content'),Array()), 'raw'),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null)),Array()), 'encq').'
' : ''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'-title-content'),Array()), 'raw'),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null)),Array()), 'encq').'
').'';},'flow_post_meta_actions' => function ($cx, $in) {return '<div class="flow-post-meta">
	<span class="flow-post-meta-actions">
		'.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? '
			<a href="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
			   data-flow-interactive-handler="activateReplyPost"

			   
			   data-flow-eventlog-schema="FlowReplies"
			   data-flow-eventlog-action="initiate"
			   data-flow-eventlog-entrypoint="reply-post"
			   data-flow-eventlog-forward="
				   < .flow-post:not([data-flow-post-max-depth=\'1\']) .flow-reply-form [data-role=\'cancel\'],
				   < .flow-post:not([data-flow-post-max-depth=\'1\']) .flow-reply-form [data-role=\'action\'][name=\'preview\'],
				   < .flow-post:not([data-flow-post-max-depth=\'1\']) .flow-reply-form [data-role=\'submit\']
			   "
			>'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		' : '').'
		'.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '
			<a href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-api-handler="activateEditPost"
			   data-flow-api-target="< .flow-post-main"
			   data-flow-interactive-handler="apiRequest"
			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet">
				'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-edit-post'),Array()), 'encq').'
			</a>
		' : '').'
		'.((LCRun3::ifvar($cx, ((isset($in['actions']['thank']) && is_array($in['actions'])) ? $in['actions']['thank'] : null))) ? '
			
			<a class="mw-ui-anchor mw-ui-constructive mw-ui-quiet mw-thanks-flow-thank-link"
			   href="'.htmlentities((string)((isset($in['actions']['thank']['url']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['thank']['title']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['actions']['thank']['title']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		' : '').'
	</span>

	<span class="flow-post-timestamp">
		'.((LCRun3::ifvar($cx, ((isset($in['isOriginalContent']) && is_array($in)) ? $in['isOriginalContent'] : null))) ? '
			'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),Array()), 'encq').'
		' : '
			<span>
				'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['creator']['name']) && is_array($in['creator'])) ? $in['creator']['name'] : null),'===',((isset($in['lastEditUser']['name']) && is_array($in['lastEditUser'])) ? $in['lastEditUser']['name'] : null)),Array()), $in, function($cx, $in) {return '
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-edited'),Array()), 'encq').'
				';}, function($cx, $in) {return '
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-edited-by',((isset($in['lastEditUser']['name']) && is_array($in['lastEditUser'])) ? $in['lastEditUser']['name'] : null)),Array()), 'encq').'
				';}).'
			</span>
			<a href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp-anchor">'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((isset($in['lastEditId']) && is_array($in)) ? $in['lastEditId'] : null)),Array()), 'encq').'</a>
		').'
	</span>
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
		   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="hide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-hide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['unhide']['title']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="unhide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unhide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['delete']) && is_array($in['actions'])) ? $in['actions']['delete'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['delete']['url']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['delete']['title']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="delete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-trash"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-delete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['undelete']) && is_array($in['actions'])) ? $in['actions']['undelete'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['undelete']['url']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['undelete']['title']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="undelete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-trash"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-undelete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['suppress']) && is_array($in['actions'])) ? $in['actions']['suppress'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['suppress']['url']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['suppress']['title']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="suppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-block"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-suppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions'])) ? $in['actions']['unsuppress'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['actions']['unsuppress']['url']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.htmlentities((string)((isset($in['actions']['unsuppress']['title']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-flow-interactive-handler="moderationDialog"
		   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'"
		   data-role="unsuppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-block"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unsuppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),Array()), $in, function($cx, $in) {return '
	'.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   data-flow-interactive-handler="moderationDialog"
			   data-flow-template="flow_moderate_topic"
			   data-role="lock"
			   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
			   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-lock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li class="'.htmlentities((string)((isset($in['moderationContainerClass']) && is_array($in)) ? $in['moderationContainerClass'] : null), ENT_QUOTES, 'UTF-8').'-moderation-action">'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   data-flow-interactive-handler="moderationDialog"
			   data-flow-template="flow_moderate_topic"
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
			   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-unlock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),Array()), 'raw')),Array()), 'encq').'</a>'.htmlentities((string)((isset($in['null']) && is_array($in)) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).'';},'flow_post_actions' => function ($cx, $in) {return '<div class="flow-menu flow-menu-hoverable">
	<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
	<ul class="mw-ui-button-container flow-list">
		
		'.LCRun3::p($cx, 'flow_moderation_actions_list', Array(Array($in),Array('moderationType'=>'post','moderationTarget'=>'post','moderationTemplate'=>'post','moderationContainerClass'=>'flow-menu','moderationMwUiClass'=>'mw-ui-button','moderationIcons'=>true))).'
	</ul>
</div>
';},'flow_post_inner' => function ($cx, $in) {return '<div
	'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '
		class="flow-post-main flow-post-moderated flow-click-interactive flow-element-collapsible flow-element-collapsed"
		data-flow-interactive-handler="collapserCollapsibleToggle"
		tabindex="0"
	' : '
		class="flow-post-main"
	').'
>
	'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'

	'.LCRun3::wi($cx, ((isset($in['creator']) && is_array($in)) ? $in['creator'] : null), $in, function($cx, $in) {return '
		'.LCRun3::p($cx, 'flow_post_author', Array(Array($in),Array())).'
	';}).'

	'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '
		<div class="flow-moderated-post-content">
			'.LCRun3::p($cx, 'flow_post_moderation_state', Array(Array($in),Array())).'
		</div>
	' : '').'

	<div class="flow-post-content">
		'.LCRun3::ch($cx, 'escapeContent', Array(Array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),Array()), 'encq').'
	</div>

	
	'.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? '
		'.LCRun3::p($cx, 'flow_post_meta_actions', Array(Array($in),Array())).'
		'.LCRun3::p($cx, 'flow_post_actions', Array(Array($in),Array())).'
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
';},'flow_edit_post' => function ($cx, $in) {return '<form class="flow-edit-post-form"
      method="POST"
      action="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
>
	'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'
	<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['scopes'][0]['rootBlock']['editToken']) && is_array($cx['scopes'][0]['rootBlock'])) ? $cx['scopes'][0]['rootBlock']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	<input type="hidden" name="topic_prev_revision" value="'.htmlentities((string)((isset($in['revisionId']) && is_array($in)) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
	'.LCRun3::hbch($cx, 'ifAnonymous', Array(Array(),Array()), $in, function($cx, $in) {return '
		'.LCRun3::p($cx, 'flow_anon_warning', Array(Array($in),Array())).'
	';}).'

	<textarea name="topic_content" class="mw-ui-input flow-form-collapsible"
		data-flow-preview-template="flow_post"
		data-role="content">'.((LCRun3::ifvar($cx, ((isset($cx['scopes'][0]['rootBlock']['submitted']['content']) && is_array($cx['scopes'][0]['rootBlock']['submitted'])) ? $cx['scopes'][0]['rootBlock']['submitted']['content'] : null))) ? ''.htmlentities((string)((isset($cx['scopes'][0]['rootBlock']['submitted']['content']) && is_array($cx['scopes'][0]['rootBlock']['submitted'])) ? $cx['scopes'][0]['rootBlock']['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null), ENT_QUOTES, 'UTF-8').'').'</textarea>

	<div class="flow-form-actions flow-form-collapsible">
		<button class="mw-ui-button mw-ui-constructive"
		        data-flow-api-handler="submitEditPost">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-edit-post-submit'),Array()), 'encq').'</button>
		'.LCRun3::p($cx, 'flow_form_buttons', Array(Array($in),Array())).'
		<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-edit'),Array()), 'encq').'</small>
	</div>
</form>
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
';},'flow_post_replies' => function ($cx, $in) {return '<div class="flow-replies">
	'.LCRun3::sec($cx, ((isset($in['replies']) && is_array($in)) ? $in['replies'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array(Array(((isset($cx['scopes'][0]['rootBlock']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['rootBlock'] : null),$in),Array()), $in, function($cx, $in) {return '
			<!-- eachPost nested replies -->
			'.LCRun3::ch($cx, 'post', Array(Array(((isset($cx['scopes'][0]['rootBlock']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['rootBlock'] : null),$in),Array()), 'encq').'
		';}).'
	';}).'
	'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($cx['scopes'][0]['rootBlock']['submitted']['postId']) && is_array($cx['scopes'][0]['rootBlock']['submitted'])) ? $cx['scopes'][0]['rootBlock']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($cx['scopes'][0]['rootBlock']['submitted']['action']) && is_array($cx['scopes'][0]['rootBlock']['submitted'])) ? $cx['scopes'][0]['rootBlock']['submitted']['action'] : null),'===','reply'),Array()), $in, function($cx, $in) {return '
			'.LCRun3::p($cx, 'flow_reply_form', Array(Array($in),Array())).'
		';}).'
	';}).'
</div>
';},),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return ''.LCRun3::wi($cx, ((isset($in['revision']) && is_array($in)) ? $in['revision'] : null), $in, function($cx, $in) {return '
	<div id="flow-post-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	     class="flow-post'.((LCRun3::ifvar($cx, ((isset($in['isMaxThreadingDepth']) && is_array($in)) ? $in['isMaxThreadingDepth'] : null))) ? ' flow-post-max-depth' : '').'"
	     data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	>
		'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '
			'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($cx['scopes'][0]['rootBlock']['submitted']['showPostId']) && is_array($cx['scopes'][0]['rootBlock']['submitted'])) ? $cx['scopes'][0]['rootBlock']['submitted']['showPostId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return '
				'.LCRun3::p($cx, 'flow_post_inner', Array(Array($in),Array())).'
			';}, function($cx, $in) {return '
				<div class="flow-post-main flow-post-moderated">
					<span class="flow-moderated-post-content">
						'.LCRun3::p($cx, 'flow_post_moderation_state', Array(Array($in),Array())).'
					</span>
				</div>
			';}).'
		' : '
			'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($cx['scopes'][0]['rootBlock']['submitted']['action']) && is_array($cx['scopes'][0]['rootBlock']['submitted'])) ? $cx['scopes'][0]['rootBlock']['submitted']['action'] : null),'===','edit-post'),Array()), $in, function($cx, $in) {return '
				'.LCRun3::hbch($cx, 'ifCond', Array(Array(((isset($cx['scopes'][0]['rootBlock']['submitted']['postId']) && is_array($cx['scopes'][0]['rootBlock']['submitted'])) ? $cx['scopes'][0]['rootBlock']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return '
					'.LCRun3::p($cx, 'flow_edit_post', Array(Array($in),Array())).'
				';}, function($cx, $in) {return '
					'.LCRun3::p($cx, 'flow_post_inner', Array(Array($in),Array())).'
				';}).'
			';}, function($cx, $in) {return '
				'.LCRun3::p($cx, 'flow_post_inner', Array(Array($in),Array())).'
			';}).'
		').'

		
		'.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? '
			'.LCRun3::p($cx, 'flow_post_replies', Array(Array($in),Array())).'
		' : '').'
	</div>
';}).'
';
}
?>