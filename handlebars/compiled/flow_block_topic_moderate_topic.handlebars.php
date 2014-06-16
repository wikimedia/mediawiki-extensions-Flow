<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'html' => 'Flow\TemplateHelper::html',
            'post' => 'Flow\TemplateHelper::post',
            'moderationAction' => 'Flow\TemplateHelper::moderationAction',
            'moderationActionText' => 'Flow\TemplateHelper::moderationActionText',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	'.LCRun3::ifv($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, function($cx, $in) {return '
	<ul>
	'.LCRun3::sec($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, true, function($cx, $in) {return '
		<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
	';}).'
	</ul>
';}).'

	
	'.LCRun3::sec($cx, ((is_array($in) && isset($in['roots'])) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array($cx['scopes'][0],$in), $in, function($cx, $in) {return '
			<form method="POST" action="'.LCRun3::ch($cx, 'moderationAction', Array(((is_array($in) && isset($in['actions'])) ? $in['actions'] : null),((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['moderationState'])) ? $cx['scopes'][0]['submitted']['moderationState'] : null)), 'encq').'">
	<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	<textarea name="topic_reason">'.LCRun3::ifv($cx, ((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['reason'])) ? $cx['scopes'][0]['submitted']['reason'] : null), $in, function($cx, $in) {return ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['reason'])) ? $cx['scopes'][0]['submitted']['reason'] : null), ENT_QUOTES, 'UTF-8').'';}).'</textarea>
	<div class="flow-form-actions flow-form-collapsible">
		<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'moderationActionText', Array(((is_array($in) && isset($in['actions'])) ? $in['actions'] : null),((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['moderationState'])) ? $cx['scopes'][0]['submitted']['moderationState'] : null)), 'encq').'</button>
		<button data-flow-interactive-handler="createForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'</button>
	</div>
</form>

			'.LCRun3::wi($cx, ((is_array($in) && isset($in['revision'])) ? $in['revision'] : null), $in, function($cx, $in) {return '
	<div class="flow-post'.LCRun3::ifv($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null), $in, function($cx, $in) {return ' flow-post-moderated';}).'">
		'.LCRun3::wi($cx, ((is_array($in) && isset($in['author'])) ? $in['author'] : null), $in, function($cx, $in) {return '
			<span class="flow-author"><a href="'.htmlentities(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['url'])) ? $in['links']['contribs']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['title'])) ? $in['links']['contribs']['title'] : null), ENT_QUOTES, 'UTF-8').'" class="mw-userlink flow-ui-tooltip-target">'.htmlentities(((is_array($in) && isset($in['name'])) ? $in['name'] : null), ENT_QUOTES, 'UTF-8').'</a> <span class="mw-usertoollinks">(<a href="'.htmlentities(((is_array($in['links']['talk']) && isset($in['links']['talk']['url'])) ? $in['links']['talk']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="new flow-ui-tooltip-target" title="'.htmlentities(((is_array($in['links']['talk']) && isset($in['links']['talk']['title'])) ? $in['links']['talk']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array('talkpagelinktext'), 'encq').'</a>'.LCRun3::ifv($cx, ((is_array($in['links']) && isset($in['links']['block'])) ? $in['links']['block'] : null), $in, function($cx, $in) {return ' | <a class="flow-ui-tooltip-target" href="'.htmlentities(((is_array($in['links']['block']) && isset($in['links']['block']['url'])) ? $in['links']['block']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['block']) && isset($in['links']['block']['title'])) ? $in['links']['block']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array('blocklink'), 'encq').'</a>';}).')</span></span>
		';}).'
		'.LCRun3::ifv($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null), $in, function($cx, $in) {return '
			<div class="flow-moderated-post-content">'.LCRun3::ch($cx, 'l10n', Array('post_moderation_state',((is_array($in) && isset($in['moderateState'])) ? $in['moderateState'] : null),((is_array($in) && isset($in['replyToId'])) ? $in['replyToId'] : null),((is_array($in['moderator']) && isset($in['moderator']['name'])) ? $in['moderator']['name'] : null)), 'encq').'</div>
			<div>@Todo - Add css to toggle between "xxx is hidden by xxx" and real post</div>
		';}).'
		<div class="flow-post-content">
			'.LCRun3::ch($cx, 'html', Array(((is_array($in) && isset($in['content'])) ? $in['content'] : null)), 'encq').'
		</div>
		<div class="flow-post-meta">
			<span class="flow-post-meta-actions">
				'.LCRun3::ifv($cx, ((is_array($in['actions']) && isset($in['actions']['reply'])) ? $in['actions']['reply'] : null), $in, function($cx, $in) {return '
					<a href="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['url'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['title'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-ui-progressive flow-ui-quiet" data-flow-interactive-handler="showPostReplyForm">'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['title'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
				';}).'
				'.LCRun3::ifv($cx, ((is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null), $in, function($cx, $in) {return '
					&#8226;
					<a href="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-ui-regressive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-post-action-edit-post'), 'encq').'</a>
				';}).'
			</span>
			'.LCRun3::ifv($cx, ((is_array($in) && isset($in['previousRevisionId'])) ? $in['previousRevisionId'] : null), $in, function($cx, $in) {return '
				<!--span class="wikiglyph wikiglyph-clock"></span--> '.LCRun3::ch($cx, 'uuidTimestamp', Array(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null),'edited_ago'), 'encq').'
				&#8226;
			';}).'
			'.LCRun3::ch($cx, 'uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'time_ago'), 'encq').'
		</div>

		<div class="flow-menu">
			<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
			<ul class="flow-ui-button-container">
				'.LCRun3::ifv($cx, ((is_array($in['links']) && isset($in['links']['post'])) ? $in['links']['post'] : null), $in, function($cx, $in) {return '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['links']['post']) && isset($in['links']['post']['url'])) ? $in['links']['post']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['post']) && isset($in['links']['post']['title'])) ? $in['links']['post']['title'] : null), ENT_QUOTES, 'UTF-8').'"> <span class="wikiglyph wikiglyph-link"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-view'), 'encq').'</a></li>
				';}).'
				'.LCRun3::ifv($cx, ((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null), $in, function($cx, $in) {return '
					<li><a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['title'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-eye-lock"></span> '.LCRun3::ch($cx, 'l10n', Array('TODO-Lock'), 'encq').'</a></li>
				';}).'
				'.LCRun3::ifv($cx, ((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null), $in, function($cx, $in) {return '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-eye-lid"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-hide-post'), 'encq').'</a></li>
				';}).'
				'.LCRun3::ifv($cx, ((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null), $in, function($cx, $in) {return '
					<li><a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-trash"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-delete-post'), 'encq').'</a></li>
				';}).'
				'.LCRun3::ifv($cx, ((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null), $in, function($cx, $in) {return '
					<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['title'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-block"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-suppress-post'), 'encq').'</a></li>
				';}).'
			</ul>
		</div>

		'.LCRun3::sec($cx, ((is_array($in) && isset($in['replies'])) ? $in['replies'] : null), $in, true, function($cx, $in) {return '
			'.LCRun3::hbch($cx, 'eachPost', Array(((is_array($cx['scopes'][count($cx['scopes'])-3]) && isset($cx['scopes'][count($cx['scopes'])-3]['rootBlock'])) ? $cx['scopes'][count($cx['scopes'])-3]['rootBlock'] : null),$in), $in, function($cx, $in) {return '
				<!-- eachPost nested replies -->
				'.LCRun3::ch($cx, 'post', Array(((is_array($cx['scopes'][count($cx['scopes'])-4]) && isset($cx['scopes'][count($cx['scopes'])-4]['rootBlock'])) ? $cx['scopes'][count($cx['scopes'])-4]['rootBlock'] : null),$in), 'encq').'
			';}).'
		';}).'
	</div>
';}).'

		';}).'
	';}).'
</div>
';
}
?>