<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'post' => 'Flow\TemplateHelper::post',
            'moderationAction' => 'Flow\TemplateHelper::moderationAction',
            'moderationActionText' => 'Flow\TemplateHelper::moderationActionText',
            'escapeContent' => 'Flow\TemplateHelper::escapeContent',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	'.'
	'.LCRun3::sec($cx, ((is_array($in) && isset($in['roots'])) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array(Array($cx['scopes'][0],$in),Array()), $in, function($cx, $in) {return '
			<form method="POST" action="'.LCRun3::ch($cx, 'moderationAction', Array(Array(((is_array($in) && isset($in['actions'])) ? $in['actions'] : null),((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['moderationState'])) ? $cx['scopes'][0]['submitted']['moderationState'] : null)),Array()), 'encq').'">
	<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.LCRun3::ch($cx, 'html', Array(Array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)),Array()), 'encq').'</li>
			';}).'
		</ul>
	</div>
' : '').'
</div>

	<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	<textarea name="topic_reason">'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['reason'])) ? $cx['scopes'][0]['submitted']['reason'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['reason'])) ? $cx['scopes'][0]['submitted']['reason'] : null), ENT_QUOTES, 'UTF-8').'' : '').'</textarea>
	<div class="flow-form-actions flow-form-collapsible">
		<button data-flow-interactive-handler="apiRequest"
		        data-flow-api-handler="moderatePost"
		        class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'moderationActionText', Array(Array(((is_array($in) && isset($in['actions'])) ? $in['actions'] : null),((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['moderationState'])) ? $cx['scopes'][0]['submitted']['moderationState'] : null)),Array()), 'encq').'</button>
		<a data-flow-interactive-handler="cancelForm" class="flow-ui-button flow-ui-destructive flow-ui-quiet" href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</a>
	</div>
</form>

			'.LCRun3::wi($cx, ((is_array($in) && isset($in['revision'])) ? $in['revision'] : null), $in, function($cx, $in) {return '
	<div id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
			 class="flow-post'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? ' flow-post-moderated' : '').'"
			 data-flow-id="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
			 '.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isMaxThreadingDepth'])) ? $in['isMaxThreadingDepth'] : null))) ? '
			 data-flow-post-max-depth="1"
			 ' : '').'>
		<div class="flow-post-main">
			'.LCRun3::wi($cx, ((is_array($in) && isset($in['creator'])) ? $in['creator'] : null), $in, function($cx, $in) {return '
				'.((!LCRun3::ifvar($cx, ((is_array($in) && isset($in['isPreview'])) ? $in['isPreview'] : null))) ? '
	<span class="flow-author">
		'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['links'])) ? $in['links'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['url'])) ? $in['links']['contribs']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['title'])) ? $in['links']['contribs']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['contribs']) && isset($in['links']['contribs']['exists'])) ? $in['links']['contribs']['exists'] : null))) ? 'new ' : '').'mw-userlink flow-ui-tooltip-target">
				'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['name'])) ? $in['name'] : null))) ? '
					'.htmlentities(((is_array($in) && isset($in['name'])) ? $in['name'] : null), ENT_QUOTES, 'UTF-8').'' : '
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-anonymous'),Array()), 'encq').'
				').'
			</a>
			<span class="mw-usertoollinks">(<a href="'.htmlentities(((is_array($in['links']['talk']) && isset($in['links']['talk']['url'])) ? $in['links']['talk']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['talk']) && isset($in['links']['talk']['exists'])) ? $in['links']['talk']['exists'] : null))) ? 'new ' : '').' flow-ui-tooltip-target" title="'.htmlentities(((is_array($in['links']['talk']) && isset($in['links']['talk']['title'])) ? $in['links']['talk']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('talkpagelinktext'),Array()), 'encq').'</a>'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['block'])) ? $in['links']['block'] : null))) ? ' | <a class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['block']) && isset($in['links']['block']['exists'])) ? $in['links']['block']['exists'] : null))) ? 'new ' : '').' flow-ui-tooltip-target" href="'.htmlentities(((is_array($in['links']['block']) && isset($in['links']['block']['url'])) ? $in['links']['block']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['block']) && isset($in['links']['block']['title'])) ? $in['links']['block']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('blocklink'),Array()), 'encq').'</a>' : '').')</span>
		' : '').'
	</span>
' : '
	<span class="flow-author"><a href="#" class="mw-userlink">
		'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['name'])) ? $in['name'] : null))) ? '
			'.htmlentities(((is_array($in) && isset($in['name'])) ? $in['name'] : null), ENT_QUOTES, 'UTF-8').'' : '
			'.LCRun3::ch($cx, 'l10n', Array(Array('flow-anonymous'),Array()), 'encq').'
		').'
	</a></span>
').'

			';}).'

			'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? '
				<div class="flow-moderated-post-content">'.LCRun3::ch($cx, 'l10n', Array(Array('post_moderation_state',((is_array($in) && isset($in['moderateState'])) ? $in['moderateState'] : null),((is_array($in) && isset($in['replyToId'])) ? $in['replyToId'] : null),((is_array($in['moderator']) && isset($in['moderator']['name'])) ? $in['moderator']['name'] : null)),Array()), 'encq').'</div>
			' : '').'

			<div class="flow-post-content">
				'.LCRun3::ch($cx, 'escapeContent', Array(Array(((is_array($in['content']) && isset($in['content']['format'])) ? $in['content']['format'] : null),((is_array($in['content']) && isset($in['content']['content'])) ? $in['content']['content'] : null)),Array()), 'encq').'
			</div>
			'.'
			'.((!LCRun3::ifvar($cx, ((is_array($in) && isset($in['isPreview'])) ? $in['isPreview'] : null))) ? '
				<div class="flow-post-meta">
	<span class="flow-post-meta-actions">
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['reply'])) ? $in['actions']['reply'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['url'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['title'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   class="flow-ui-progressive flow-ui-quiet"
			   data-flow-interactive-handler="activateReplyPost">'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['title'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null))) ? '
			&#8226;
			<a href="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-api-handler="activateEditPost"
			   data-flow-api-target="< .flow-post-main"
			   data-flow-interactive-handler="apiRequest"
			   class="flow-ui-quiet">
				'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-edit-post'),Array()), 'encq').'
			</a>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['thank'])) ? $in['actions']['thank'] : null))) ? '
			&#8226;
			'.'
			<a class="flow-ui-regressive flow-ui-quiet mw-thanks-flow-thank-link"
			   href="'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['url'])) ? $in['actions']['thank']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['title'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['title'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		' : '').'
	</span>
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['previousRevisionId'])) ? $in['previousRevisionId'] : null))) ? '
		<!--span class="wikiglyph wikiglyph-clock"></span-->
		'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null),'edited_ago'),Array()), 'encq').'
		&#8226;
	' : '').'
	'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'time_ago'),Array()), 'encq').'
</div>

				<div class="flow-menu">
	<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
	<ul class="flow-ui-button-container">
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['post'])) ? $in['links']['post'] : null))) ? '
			<li>
				<a class="flow-ui-button flow-ui-quiet flow-ui-thin"
				   href="'.htmlentities(((is_array($in['links']['post']) && isset($in['links']['post']['url'])) ? $in['links']['post']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['links']['post']) && isset($in['links']['post']['title'])) ? $in['links']['post']['title'] : null), ENT_QUOTES, 'UTF-8').'">
					<span class="wikiglyph wikiglyph-link"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-view'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null))) ? '
			<li>
				<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin"
				   href="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['title'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">
					<span class="wikiglyph wikiglyph-eye-lock"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('TODO-Lock'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="flow-ui-button flow-ui-quiet flow-ui-thin"
				   href="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="hide">
					<span class="wikiglyph wikiglyph-flag"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-hide-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['unhide'])) ? $in['actions']['unhide'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin"
				   href="'.htmlentities(((is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['url'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['title'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="restore">
					<span class="wikiglyph wikiglyph-flag"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-unhide-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="flow-ui-button flow-ui-quiet flow-ui-thin"
				   href="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="delete">
					<span class="wikiglyph wikiglyph-trash"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-delete-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['undelete'])) ? $in['actions']['undelete'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin"
				   href="'.htmlentities(((is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['url'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['title'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="restore">
					<span class="wikiglyph wikiglyph-eye-lid"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-undelete-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin"
				   href="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['title'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="suppress">
					<span class="wikiglyph wikiglyph-block"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-suppress-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['unsuppress'])) ? $in['actions']['unsuppress'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin"
				   href="'.htmlentities(((is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['url'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['title'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="restore">
					<span class="wikiglyph wikiglyph-eye-lid"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-unsuppress-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
	</ul>
</div>

			' : '').'
		</div>

		'.'
		'.((!LCRun3::ifvar($cx, ((is_array($in) && isset($in['isPreview'])) ? $in['isPreview'] : null))) ? '
			<div class="flow-replies">
	'.LCRun3::sec($cx, ((is_array($in) && isset($in['replies'])) ? $in['replies'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array(Array(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['rootBlock'])) ? $cx['scopes'][0]['rootBlock'] : null),$in),Array()), $in, function($cx, $in) {return '
			<!-- eachPost nested replies -->
			'.LCRun3::ch($cx, 'post', Array(Array(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['rootBlock'])) ? $cx['scopes'][0]['rootBlock'] : null),$in),Array()), 'encq').'
		';}).'
	';}).'
</div>

		' : '').'
	</div>
';}).'

		';}).'
	';}).'
</div>
';
}
?>