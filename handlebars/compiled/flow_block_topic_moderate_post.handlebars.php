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
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'post' => 'Flow\TemplateHelper::post',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'moderationAction' => 'Flow\TemplateHelper::moderationAction',
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
        'partials' => array('flow_errors' => function ($cx, $in) {return '<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
		<ul>
'.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in) {return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
';}).'		</ul>
	</div>
' : '').'</div>
';},'flow_moderate_post' => function ($cx, $in) {return '<form method="POST" action="'.LCRun3::ch($cx, 'moderationAction', array(array(((isset($in['actions']) && is_array($in)) ? $in['actions'] : null),((isset($cx['sp_vars']['root']['submitted']['moderationState']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['moderationState'] : null)),array()), 'encq').'">
'.LCRun3::p($cx, 'flow_errors', array(array($in),array())).'	<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	<textarea name="topic_reason"
	          required
	          data-flow-expandable="true"
	          class="mw-ui-input"
	          data-role="content"
	          placeholder="'.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-moderation-placeholder-',((isset($cx['sp_vars']['root']['submitted']['moderationState']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['moderationState'] : null),'-post'),array()), 'raw')),array()), 'encq').'"
	          autofocus>'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']['reason']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['reason'] : null))) ? ''.htmlentities((string)((isset($cx['sp_vars']['root']['submitted']['reason']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['reason'] : null), ENT_QUOTES, 'UTF-8').'' : '').'</textarea>
	<div class="flow-form-actions flow-form-collapsible">
		<button data-flow-interactive-handler="apiRequest"
		        data-flow-api-handler="moderatePost"
		        class="mw-ui-button mw-ui-constructive"
		        data-role="submit">'.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-moderation-confirm-',((isset($cx['sp_vars']['root']['submitted']['moderationState']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['moderationState'] : null),'-post'),array()), 'raw')),array()), 'encq').'</button>
		<a data-flow-interactive-handler="cancelForm"
		   class="mw-ui-button mw-ui-destructive mw-ui-quiet"
		   href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'">'.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'</a>
	</div>
</form>
';},'flow_post_author' => function ($cx, $in) {return '<span class="flow-author">
'.((LCRun3::ifvar($cx, ((isset($in['links']) && is_array($in)) ? $in['links'] : null))) ? ''.((LCRun3::ifvar($cx, ((isset($in['links']['userpage']) && is_array($in['links'])) ? $in['links']['userpage'] : null))) ? '			<a href="'.htmlentities((string)((isset($in['links']['userpage']['url']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   '.((!LCRun3::ifvar($cx, ((isset($in['name']) && is_array($in)) ? $in['name'] : null))) ? 'title="'.htmlentities((string)((isset($in['links']['userpage']['title']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['title'] : null), ENT_QUOTES, 'UTF-8').'"' : '').'
			   class="'.((!LCRun3::ifvar($cx, ((isset($in['links']['userpage']['exists']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['exists'] : null))) ? 'new ' : '').'mw-userlink">
' : '').''.((LCRun3::ifvar($cx, ((isset($in['name']) && is_array($in)) ? $in['name'] : null))) ? ''.htmlentities((string)((isset($in['name']) && is_array($in)) ? $in['name'] : null), ENT_QUOTES, 'UTF-8').'' : ''.LCRun3::ch($cx, 'l10n', array(array('flow-anonymous'),array()), 'encq').'').''.((LCRun3::ifvar($cx, ((isset($in['links']['userpage']) && is_array($in['links'])) ? $in['links']['userpage'] : null))) ? '</a>' : '').'<span class="mw-usertoollinks flow-pipelist">
			('.((LCRun3::ifvar($cx, ((isset($in['links']['talk']) && is_array($in['links'])) ? $in['links']['talk'] : null))) ? '<span><a href="'.htmlentities((string)((isset($in['links']['talk']['url']) && is_array($in['links']['talk'])) ? $in['links']['talk']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				    class="'.((!LCRun3::ifvar($cx, ((isset($in['links']['talk']['exists']) && is_array($in['links']['talk'])) ? $in['links']['talk']['exists'] : null))) ? 'new ' : '').'"
				    title="'.htmlentities((string)((isset($in['links']['talk']['title']) && is_array($in['links']['talk'])) ? $in['links']['talk']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('talkpagelinktext'),array()), 'encq').'</a></span>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['contribs']) && is_array($in['links'])) ? $in['links']['contribs'] : null))) ? '<span><a href="'.htmlentities((string)((isset($in['links']['contribs']['url']) && is_array($in['links']['contribs'])) ? $in['links']['contribs']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities((string)((isset($in['links']['contribs']['title']) && is_array($in['links']['contribs'])) ? $in['links']['contribs']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('contribslink'),array()), 'encq').'</a></span>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['block']) && is_array($in['links'])) ? $in['links']['block'] : null))) ? '<span><a class="'.((!LCRun3::ifvar($cx, ((isset($in['links']['block']['exists']) && is_array($in['links']['block'])) ? $in['links']['block']['exists'] : null))) ? 'new ' : '').'"
				   href="'.htmlentities((string)((isset($in['links']['block']['url']) && is_array($in['links']['block'])) ? $in['links']['block']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['links']['block']['title']) && is_array($in['links']['block'])) ? $in['links']['block']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', array(array('blocklink'),array()), 'encq').'</a></span>' : '').')
		</span>
' : '').'</span>
';},'flow_post_moderation_state' => function ($cx, $in) {return '<span class="plainlinks">'.((LCRun3::ifvar($cx, ((isset($in['replyToId']) && is_array($in)) ? $in['replyToId'] : null))) ? ''.LCRun3::ch($cx, 'l10nParse', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'-post-content'),array()), 'raw'),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null),((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null)),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10nParse', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'-title-content'),array()), 'raw'),((isset($in['moderator']['name']) && is_array($in['moderator'])) ? $in['moderator']['name'] : null),((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null)),array()), 'encq').'').'</span>
';},'flow_post_meta_actions' => function ($cx, $in) {return '<div class="flow-post-meta">
	<span class="flow-post-meta-actions">
'.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? '			<a href="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet flow-ui-hide-last-reply"
			   data-flow-interactive-handler="activateReplyPost"

			   data-flow-eventlog-schema="FlowReplies"
			   data-flow-eventlog-action="initiate"
			   data-flow-eventlog-entrypoint="reply-post"
			   data-flow-eventlog-forward="
				   < .flow-post:not([data-flow-post-max-depth=\'1\']) .flow-reply-form [data-role=\'cancel\'],
				   < .flow-post:not([data-flow-post-max-depth=\'1\']) .flow-reply-form [data-role=\'action\'][name=\'preview\'],
				   < .flow-post:not([data-flow-post-max-depth=\'1\']) .flow-reply-form [data-role=\'submit\']
			   "
			>'.htmlentities((string)((isset($in['actions']['reply']['text']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['text'] : null), ENT_QUOTES, 'UTF-8').'</a>
' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '			<a href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-api-handler="activateEditPost"
			   data-flow-api-target="< .flow-post-main"
			   data-flow-interactive-handler="apiRequest"
			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet">
				'.LCRun3::ch($cx, 'l10n', array(array('flow-post-action-edit-post'),array()), 'encq').'
			</a>
' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['thank']) && is_array($in['actions'])) ? $in['actions']['thank'] : null))) ? '			<a class="mw-ui-anchor mw-ui-constructive mw-ui-quiet mw-thanks-flow-thank-link"
			   href="'.htmlentities((string)((isset($in['actions']['thank']['url']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['thank']['title']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['actions']['thank']['text']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['text'] : null), ENT_QUOTES, 'UTF-8').'</a>
' : '').'	</span>

	<span class="flow-post-timestamp">
'.((LCRun3::ifvar($cx, ((isset($in['isOriginalContent']) && is_array($in)) ? $in['isOriginalContent'] : null))) ? '			<a href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp-anchor">
				'.LCRun3::ch($cx, 'uuidTimestamp', array(array(((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), 'encq').'
			</a>
' : '			<span>
'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['creator']['name']) && is_array($in['creator'])) ? $in['creator']['name'] : null),'===',((isset($in['lastEditUser']['name']) && is_array($in['lastEditUser'])) ? $in['lastEditUser']['name'] : null)),array()), $in, false, function($cx, $in) {return '					'.LCRun3::ch($cx, 'l10n', array(array('flow-edited'),array()), 'encq').'
';}, function($cx, $in) {return '					'.LCRun3::ch($cx, 'l10n', array(array('flow-edited-by',((isset($in['lastEditUser']['name']) && is_array($in['lastEditUser'])) ? $in['lastEditUser']['name'] : null)),array()), 'encq').'
';}).'			</span>
			<a href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp-anchor">'.LCRun3::ch($cx, 'uuidTimestamp', array(array(((isset($in['lastEditId']) && is_array($in)) ? $in['lastEditId'] : null)),array()), 'encq').'</a>
').'	</span>
</div>
';},'flow_moderation_actions_list' => function ($cx, $in) {return '<section>'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','topic'),array()), $in, false, function($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '<li class="flow-js">'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
				   href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="apiRequest"
				   data-flow-api-handler="activateEditTitle"
				   data-flow-api-target="< .flow-topic-titlebar"
				>'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-pencil"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-edit-title'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['topic-history']) && is_array($in['links'])) ? $in['links']['topic-history'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
				   href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['links']['topic-history']['title']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-clock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-history'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
				   href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-link"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-view'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['summarize']) && is_array($in['actions'])) ? $in['actions']['summarize'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-progressive mw-ui-quiet"
				   data-flow-interactive-handler="apiRequest"
				   data-flow-api-handler="activateSummarizeTopic"
				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
				   href="'.htmlentities((string)((isset($in['actions']['summarize']['url']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['actions']['summarize']['title']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-stripe-toc"></span> ' : '').''.((LCRun3::ifvar($cx, ((isset($in['summary']) && is_array($in)) ? $in['summary'] : null))) ? ''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-resummarize-topic'),array()), 'raw')),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-summarize-topic'),array()), 'raw')),array()), 'encq').'').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','post'),array()), $in, false, function($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['links']['post']) && is_array($in['links'])) ? $in['links']['post'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
				   href="'.htmlentities((string)((isset($in['links']['post']['url']) && is_array($in['links']['post'])) ? $in['links']['post']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['links']['post']['title']) && is_array($in['links']['post'])) ? $in['links']['post']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-link"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-view'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).'</section>

<section>'.((LCRun3::ifvar($cx, ((isset($in['actions']['hide']) && is_array($in['actions'])) ? $in['actions']['hide'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['actions']['hide']['url']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['hide']['title']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-interactive-handler="moderationDialog"
			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
			   data-role="hide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-hide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['unhide']['title']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-interactive-handler="moderationDialog"
			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
			   data-role="unhide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unhide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['delete']) && is_array($in['actions'])) ? $in['actions']['delete'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['actions']['delete']['url']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['delete']['title']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-interactive-handler="moderationDialog"
			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
			   data-role="delete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-trash"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-delete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['undelete']) && is_array($in['actions'])) ? $in['actions']['undelete'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['actions']['undelete']['url']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['undelete']['title']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-interactive-handler="moderationDialog"
			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
			   data-role="undelete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-trash"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-undelete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['suppress']) && is_array($in['actions'])) ? $in['actions']['suppress'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['actions']['suppress']['url']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['suppress']['title']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-interactive-handler="moderationDialog"
			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
			   data-role="suppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-block"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-suppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions'])) ? $in['actions']['unsuppress'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
			   href="'.htmlentities((string)((isset($in['actions']['unsuppress']['url']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities((string)((isset($in['actions']['unsuppress']['title']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-interactive-handler="moderationDialog"
			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
			   data-role="unsuppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-block"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unsuppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
				   data-flow-interactive-handler="moderationDialog"
				   data-flow-template="flow_moderate_topic.partial"
				   data-role="lock"
				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
				   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-lock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
				   data-flow-interactive-handler="moderationDialog"
				   data-flow-template="flow_moderate_topic.partial"
				   data-role="unlock"
				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
				   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-unlock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}, function($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
				   data-flow-interactive-handler="apiRequest"
				   data-flow-api-handler="activateLockTopic"
				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
				   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-lock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet"
				   data-flow-interactive-handler="apiRequest"
				   data-flow-api-handler="activateLockTopic"
				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
				   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="wikiglyph wikiglyph-unlock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).'</section>
';},'flow_post_actions' => function ($cx, $in) {return '<div class="flow-menu flow-menu-hoverable">
	<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
	<ul class="mw-ui-button-container flow-list">
'.LCRun3::p($cx, 'flow_moderation_actions_list', array(array($in),array('moderationType'=>'post','moderationTarget'=>'post','moderationTemplate'=>'post','moderationContainerClass'=>'flow-menu','moderationMwUiClass'=>'mw-ui-button','moderationIcons'=>true))).'	</ul>
</div>
';},'flow_post_inner' => function ($cx, $in) {return '<div
'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '		class="flow-post-main flow-post-moderated flow-click-interactive flow-element-collapsible flow-element-collapsed"
		data-flow-interactive-handler="collapserCollapsibleToggle"
		tabindex="0"
' : '		class="flow-post-main"
').'>
'.LCRun3::p($cx, 'flow_errors', array(array($in),array())).'
'.LCRun3::wi($cx, ((isset($in['creator']) && is_array($in)) ? $in['creator'] : null), $in, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_post_author', array(array($in),array())).'';}).'
'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '		<div class="flow-moderated-post-content">
'.LCRun3::p($cx, 'flow_post_moderation_state', array(array($in),array())).'		</div>
' : '').'
	<div class="flow-post-content">
		'.LCRun3::ch($cx, 'escapeContent', array(array(((isset($in['content']['format']) && is_array($in['content'])) ? $in['content']['format'] : null),((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)),array()), 'encq').'
	</div>

'.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? ''.LCRun3::p($cx, 'flow_post_meta_actions', array(array($in),array())).''.LCRun3::p($cx, 'flow_post_actions', array(array($in),array())).'' : '').'</div>
';},'flow_anon_warning' => function ($cx, $in) {return '<div class="flow-anon-warning">
	<div class="flow-anon-warning-mobile">
'.LCRun3::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in) {return ''.LCRun3::ch($cx, 'l10nParse', array(array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin'),array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin/signup'),array()), 'raw')),array()), 'encq').'';}).'	</div>

'.LCRun3::hbch($cx, 'progressiveEnhancement', array(array(),array()), $in, false, function($cx, $in) {return '		<div class="flow-anon-warning-desktop">
'.LCRun3::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in) {return ''.LCRun3::ch($cx, 'l10nParse', array(array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin'),array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin/signup'),array()), 'raw')),array()), 'encq').'';}).'		</div>
';}).'</div>
';},'flow_form_buttons' => function ($cx, $in) {return '<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right flow-form-action-preview flow-js"

>'.LCRun3::ch($cx, 'l10n', array(array('flow-preview'),array()), 'encq').'</button>

<button data-flow-interactive-handler="cancelForm"
        data-role="cancel"
        type="reset"
        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"

>'.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'</button>
';},'flow_edit_post' => function ($cx, $in) {return '<form class="flow-edit-post-form"
      method="POST"
      action="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
>
'.LCRun3::p($cx, 'flow_errors', array(array($in),array())).'	<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['rootBlock']['editToken']) && is_array($cx['sp_vars']['root']['rootBlock'])) ? $cx['sp_vars']['root']['rootBlock']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	<input type="hidden" name="topic_prev_revision" value="'.htmlentities((string)((isset($in['revisionId']) && is_array($in)) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
'.LCRun3::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_anon_warning', array(array($in),array())).'';}).'
	<textarea name="topic_content" class="mw-ui-input flow-form-collapsible"
		data-flow-preview-template="flow_post"
		data-flow-preview-title="'.htmlentities((string)((isset($in['articleTitle']) && is_array($in)) ? $in['articleTitle'] : null), ENT_QUOTES, 'UTF-8').'"
		data-role="content">'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['rootBlock']['submitted']['content']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['content'] : null))) ? ''.htmlentities((string)((isset($cx['sp_vars']['root']['rootBlock']['submitted']['content']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null), ENT_QUOTES, 'UTF-8').'').'</textarea>

	<div class="flow-form-actions flow-form-collapsible">
		<button class="mw-ui-button mw-ui-constructive"
		        data-flow-api-handler="submitEditPost">'.LCRun3::ch($cx, 'l10n', array(array('flow-post-action-edit-post-submit'),array()), 'encq').'</button>
'.LCRun3::p($cx, 'flow_form_buttons', array(array($in),array())).'		<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-edit'),array()), 'encq').'</small>
	</div>
</form>
';},'flow_reply_form' => function ($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null))) ? '	<form class="flow-post flow-reply-form"
	      method="POST"
	      action="'.htmlentities((string)((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
	      id="flow-reply-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	      data-flow-initial-state="collapsed"
	>
		<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['rootBlock']['editToken']) && is_array($cx['sp_vars']['root']['rootBlock'])) ? $cx['sp_vars']['root']['rootBlock']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topic_replyTo" value="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />
'.LCRun3::p($cx, 'flow_errors', array(array($in),array())).'
'.LCRun3::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_anon_warning', array(array($in),array())).'';}).'
		<textarea id="flow-post-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content"
				name="topic_content"
				required
				data-flow-preview-template="flow_post"
				data-flow-preview-title="'.htmlentities((string)((isset($in['articleTitle']) && is_array($in)) ? $in['articleTitle'] : null), ENT_QUOTES, 'UTF-8').'"
				data-flow-expandable="true"
				class="mw-ui-input flow-click-interactive"
				type="text"
				placeholder="'.LCRun3::ch($cx, 'l10n', array(array('flow-reply-topic-title-placeholder',((isset($in['properties']['topic-of-post']) && is_array($in['properties'])) ? $in['properties']['topic-of-post'] : null)),array()), 'encq').'"
				data-role="content"

				data-flow-interactive-handler-focus="activateReplyTopic"
		>'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['submitted'] : null))) ? ''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['submitted']['postId']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in) {return ''.htmlentities((string)((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'';}).'' : '').'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit"
			        class="mw-ui-button mw-ui-constructive"
			        data-flow-interactive-handler="apiRequest"
			        data-flow-api-handler="submitReply"
			        data-flow-api-target="< .flow-topic"
			        data-flow-eventlog-action="save-attempt"
			>'.htmlentities((string)((isset($in['actions']['reply']['text']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['text'] : null), ENT_QUOTES, 'UTF-8').'</button>
'.LCRun3::p($cx, 'flow_form_buttons', array(array($in),array())).'			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-reply'),array()), 'encq').'</small>
		</div>
	</form>
' : '').'';},'flow_post_replies' => function ($cx, $in) {return '<div class="flow-replies">
'.LCRun3::sec($cx, ((isset($in['replies']) && is_array($in)) ? $in['replies'] : null), $in, true, function($cx, $in) {return ''.LCRun3::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']['rootBlock']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['rootBlock'] : null),$in),array()), $in, false, function($cx, $in) {return '			<!-- eachPost nested replies -->
			'.LCRun3::ch($cx, 'post', array(array(((isset($cx['sp_vars']['root']['rootBlock']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['rootBlock'] : null),$in),array()), 'encq').'
';}).'';}).''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['postId']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in) {return ''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['action']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['action'] : null),'===','reply'),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_reply_form', array(array($in),array())).'';}).'';}).'</div>
';},'flow_post' => function ($cx, $in) {return ''.LCRun3::wi($cx, ((isset($in['revision']) && is_array($in)) ? $in['revision'] : null), $in, function($cx, $in) {return '	<div id="flow-post-'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	     class="flow-post'.((LCRun3::ifvar($cx, ((isset($in['isMaxThreadingDepth']) && is_array($in)) ? $in['isMaxThreadingDepth'] : null))) ? ' flow-post-max-depth' : '').'"
	     data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	>
'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? ''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['showPostId']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['showPostId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_post_inner', array(array($in),array())).'';}, function($cx, $in) {return '				<div class="flow-post-main flow-post-moderated">
					<span class="flow-moderated-post-content">
'.LCRun3::p($cx, 'flow_post_moderation_state', array(array($in),array())).'					</span>
				</div>
';}).'' : ''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['action']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['action'] : null),'===','edit-post'),array()), $in, false, function($cx, $in) {return ''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['postId']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_edit_post', array(array($in),array())).'';}, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_post_inner', array(array($in),array())).'';}).'';}, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_post_inner', array(array($in),array())).'';}).'').'
'.((!LCRun3::ifvar($cx, ((isset($in['isPreview']) && is_array($in)) ? $in['isPreview'] : null))) ? ''.LCRun3::p($cx, 'flow_post_replies', array(array($in),array())).'' : '').'	</div>
';}).'';},),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return '<div class="flow-board">
'.LCRun3::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), $in, true, function($cx, $in) {return ''.LCRun3::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_moderate_post', array(array($in),array())).''.LCRun3::p($cx, 'flow_post', array(array($in),array())).'';}).'';}).'</div>
';
}
?>