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
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'moderationAction' => 'Flow\TemplateHelper::moderationAction',
            'concat' => 'Flow\TemplateHelper::concat',
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
	<textarea name="topic_reason"
	          required
	          data-flow-preview-template="flow_post"
	          data-flow-expandable="true"
	          class="mw-ui-input"
	          type="text"
	          data-role="content"
	          placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-moderation-placeholder-',((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['moderationState'])) ? $cx['scopes'][0]['submitted']['moderationState'] : null),'-topic'),Array()), 'raw')),Array()), 'encq').'">'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['reason'])) ? $cx['scopes'][0]['submitted']['reason'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['reason'])) ? $cx['scopes'][0]['submitted']['reason'] : null), ENT_QUOTES, 'UTF-8').'' : '').'</textarea>
	<div class="flow-form-actions flow-form-collapsible">
		<button class="mw-ui-button mw-ui-constructive"
				data-flow-interactive-handler="apiRequest"
				data-flow-api-handler="moderateTopic">'.LCRun3::ch($cx, 'l10n', Array(Array(LCRun3::ch($cx, 'concat', Array(Array('flow-moderation-confirm-',((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['moderationState'])) ? $cx['scopes'][0]['submitted']['moderationState'] : null),'-topic'),Array()), 'raw')),Array()), 'encq').'</button>
		<a class="mw-ui-button mw-ui-quiet mw-ui-destructive"
		   href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
		   title="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'"
		   data-flow-interactive-handler="cancelForm">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</a>
	</div>
</form>

			'.LCRun3::wi($cx, ((is_array($in) && isset($in['revision'])) ? $in['revision'] : null), $in, function($cx, $in) {return '
	<div id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	     class="flow-post'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? ' flow-post-moderated' : '').'"
	     data-flow-id="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	     '.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isMaxThreadingDepth'])) ? $in['isMaxThreadingDepth'] : null))) ? '
	         data-flow-post-max-depth="1"
	     ' : '').'>
		'.LCRun3::hbch($cx, 'ifCond', Array(Array(((is_array($cx['scopes'][0]['rootBlock']['submitted']) && isset($cx['scopes'][0]['rootBlock']['submitted']['action'])) ? $cx['scopes'][0]['rootBlock']['submitted']['action'] : null),'===','edit-post'),Array()), $in, function($cx, $in) {return '
			'.LCRun3::hbch($cx, 'ifCond', Array(Array(((is_array($cx['scopes'][0]['rootBlock']['submitted']) && isset($cx['scopes'][0]['rootBlock']['submitted']['postId'])) ? $cx['scopes'][0]['rootBlock']['submitted']['postId'] : null),'===',((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return '
				<form class="flow-edit-post-form"
      method="POST"
      action="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
>
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

	<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]['rootBlock']) && isset($cx['scopes'][0]['rootBlock']['editToken'])) ? $cx['scopes'][0]['rootBlock']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	<input type="hidden" name="topic_prev_revision" value="'.htmlentities(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
	'.LCRun3::hbch($cx, 'ifAnonymous', Array(Array(),Array()), $in, function($cx, $in) {return '
		<div class="flow-anon-warning">
	<div class="flow-anon-warning-mobile">
		'.'
		'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
			'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
	</div>

	'.'
	'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array()), $in, function($cx, $in) {return '
		<div class="flow-anon-warning-desktop">
			'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
				'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
		</div>
	';}).'
</div>
	';}).'

	<textarea name="topic_content" class="mw-ui-input flow-form-collapsible"
		data-flow-preview-template="flow_post"
		data-role="content">'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]['rootBlock']['submitted']) && isset($cx['scopes'][0]['rootBlock']['submitted']['content'])) ? $cx['scopes'][0]['rootBlock']['submitted']['content'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['rootBlock']['submitted']) && isset($cx['scopes'][0]['rootBlock']['submitted']['content'])) ? $cx['scopes'][0]['rootBlock']['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in['content']) && isset($in['content']['content'])) ? $in['content']['content'] : null), ENT_QUOTES, 'UTF-8').'').'</textarea>

	<div class="flow-form-actions flow-form-collapsible">
		<button class="mw-ui-button mw-ui-constructive"
		        data-flow-api-handler="submitEditPost">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-edit-post-submit'),Array()), 'encq').'</button>
		'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array()), $in, function($cx, $in) {return '
	<button data-flow-api-handler="preview"
	        data-flow-api-target="< form textarea"
	        name="preview"
	        data-role="action"
	        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right"
	>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-preview'),Array()), 'encq').'</button>

	<button data-flow-interactive-handler="cancelForm"
	        data-role="cancel"
	        type="reset"
	        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right"
	>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>
';}).'

		<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-edit'),Array()), 'encq').'</small>
	</div>
</form>

			';}, function($cx, $in) {return '
				<div
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? '
		class="flow-post-main flow-click-interactive flow-element-collapsible flow-element-collapsed"
		data-flow-load-handler="collapserState"
		data-flow-interactive-handler="collapserCollapsibleToggle"
		tabindex="0"
	' : '
		class="flow-post-main"
	').'
>
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


	'.LCRun3::wi($cx, ((is_array($in) && isset($in['creator'])) ? $in['creator'] : null), $in, function($cx, $in) {return '
		<span class="flow-author">
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['links'])) ? $in['links'] : null))) ? '
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['userpage'])) ? $in['links']['userpage'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['links']['userpage']) && isset($in['links']['userpage']['url'])) ? $in['links']['userpage']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   '.((!LCRun3::ifvar($cx, ((is_array($in) && isset($in['name'])) ? $in['name'] : null))) ? 'title="'.htmlentities(((is_array($in['links']['userpage']) && isset($in['links']['userpage']['title'])) ? $in['links']['userpage']['title'] : null), ENT_QUOTES, 'UTF-8').'"' : '').'
			   class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['userpage']) && isset($in['links']['userpage']['exists'])) ? $in['links']['userpage']['exists'] : null))) ? 'new ' : '').'mw-userlink">
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['name'])) ? $in['name'] : null))) ? ''.htmlentities(((is_array($in) && isset($in['name'])) ? $in['name'] : null), ENT_QUOTES, 'UTF-8').'' : ''.LCRun3::ch($cx, 'l10n', Array(Array('flow-anonymous'),Array()), 'encq').'
		').''.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['userpage'])) ? $in['links']['userpage'] : null))) ? '</a>' : '').'<span class="mw-usertoollinks">'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['talk'])) ? $in['links']['talk'] : null))) ? '<span><a href="'.htmlentities(((is_array($in['links']['talk']) && isset($in['links']['talk']['url'])) ? $in['links']['talk']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				    class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['talk']) && isset($in['links']['talk']['exists'])) ? $in['links']['talk']['exists'] : null))) ? 'new ' : '').'"
				    title="'.htmlentities(((is_array($in['links']['talk']) && isset($in['links']['talk']['title'])) ? $in['links']['talk']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('talkpagelinktext'),Array()), 'encq').'</a></span>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['contribs'])) ? $in['links']['contribs'] : null))) ? '<span>
					<a href="'.htmlentities(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['url'])) ? $in['links']['contribs']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['title'])) ? $in['links']['contribs']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('contribslink'),Array()), 'encq').'</a>
				</span>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['block'])) ? $in['links']['block'] : null))) ? '<span><a class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['block']) && isset($in['links']['block']['exists'])) ? $in['links']['block']['exists'] : null))) ? 'new ' : '').'"
				   href="'.htmlentities(((is_array($in['links']['block']) && isset($in['links']['block']['url'])) ? $in['links']['block']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['links']['block']) && isset($in['links']['block']['title'])) ? $in['links']['block']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('blocklink'),Array()), 'encq').'</a></span>' : '').'</span>
	' : '').'
</span>

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
			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
			   data-flow-interactive-handler="activateReplyPost">'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['title'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null))) ? '
			&#8226;
			<a href="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-api-handler="activateEditPost"
			   data-flow-api-target="< .flow-post-main"
			   data-flow-interactive-handler="apiRequest"
			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet">
				'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-edit-post'),Array()), 'encq').'
			</a>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['thank'])) ? $in['actions']['thank'] : null))) ? '
			&#8226;
			'.'
			<a class="mw-ui-anchor mw-ui-constructive mw-ui-quiet mw-thanks-flow-thank-link"
			   href="'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['url'])) ? $in['actions']['thank']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['title'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['title'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		' : '').'
	</span>
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['previousRevisionId'])) ? $in['previousRevisionId'] : null))) ? '
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['diff-prev'])) ? $in['links']['diff-prev'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['url'])) ? $in['links']['diff-prev']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			    class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
			    title="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'">
		' : '').'
			'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null),'flow-edited-ago',((is_array($in) && isset($in['1'])) ? $in['1'] : null)),Array()), 'encq').'
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['diff-prev'])) ? $in['links']['diff-prev'] : null))) ? '
			</a>
		' : '').'
		&#8226;
	' : '').'
	'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'flow-time-ago',((is_array($in) && isset($in['0'])) ? $in['0'] : null),((is_array($in) && isset($in['timestamp_readable'])) ? $in['timestamp_readable'] : null)),Array()), 'encq').'
</div>

		<div class="flow-menu">
	<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
	<ul class="mw-ui-button-container flow-list">
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['post'])) ? $in['links']['post'] : null))) ? '
			<li>
				<a class="mw-ui-button mw-ui-quiet"
				   href="'.htmlentities(((is_array($in['links']['post']) && isset($in['links']['post']['url'])) ? $in['links']['post']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['links']['post']) && isset($in['links']['post']['title'])) ? $in['links']['post']['title'] : null), ENT_QUOTES, 'UTF-8').'">
					<span class="wikiglyph wikiglyph-link"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-view'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="mw-ui-button mw-ui-quiet"
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
				<a class="mw-ui-button mw-ui-progressive mw-ui-quiet"
				   href="'.htmlentities(((is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['url'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['title'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="unhide">
					<span class="wikiglyph wikiglyph-flag"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-unhide-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="mw-ui-button mw-ui-quiet"
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
				<a class="mw-ui-button mw-ui-progressive mw-ui-quiet"
				   href="'.htmlentities(((is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['url'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['title'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="undelete">
					<span class="wikiglyph wikiglyph-eye-lid"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-undelete-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="mw-ui-button mw-ui-destructive mw-ui-quiet"
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
				<a class="mw-ui-button mw-ui-progressive mw-ui-quiet"
				   href="'.htmlentities(((is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['url'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['title'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="unsuppress">
					<span class="wikiglyph wikiglyph-eye-lid"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-unsuppress-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
	</ul>
</div>

	' : '').'
</div>

			';}).'
		';}, function($cx, $in) {return '
			<div
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? '
		class="flow-post-main flow-click-interactive flow-element-collapsible flow-element-collapsed"
		data-flow-load-handler="collapserState"
		data-flow-interactive-handler="collapserCollapsibleToggle"
		tabindex="0"
	' : '
		class="flow-post-main"
	').'
>
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


	'.LCRun3::wi($cx, ((is_array($in) && isset($in['creator'])) ? $in['creator'] : null), $in, function($cx, $in) {return '
		<span class="flow-author">
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['links'])) ? $in['links'] : null))) ? '
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['userpage'])) ? $in['links']['userpage'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['links']['userpage']) && isset($in['links']['userpage']['url'])) ? $in['links']['userpage']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   '.((!LCRun3::ifvar($cx, ((is_array($in) && isset($in['name'])) ? $in['name'] : null))) ? 'title="'.htmlentities(((is_array($in['links']['userpage']) && isset($in['links']['userpage']['title'])) ? $in['links']['userpage']['title'] : null), ENT_QUOTES, 'UTF-8').'"' : '').'
			   class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['userpage']) && isset($in['links']['userpage']['exists'])) ? $in['links']['userpage']['exists'] : null))) ? 'new ' : '').'mw-userlink">
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['name'])) ? $in['name'] : null))) ? ''.htmlentities(((is_array($in) && isset($in['name'])) ? $in['name'] : null), ENT_QUOTES, 'UTF-8').'' : ''.LCRun3::ch($cx, 'l10n', Array(Array('flow-anonymous'),Array()), 'encq').'
		').''.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['userpage'])) ? $in['links']['userpage'] : null))) ? '</a>' : '').'<span class="mw-usertoollinks">'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['talk'])) ? $in['links']['talk'] : null))) ? '<span><a href="'.htmlentities(((is_array($in['links']['talk']) && isset($in['links']['talk']['url'])) ? $in['links']['talk']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				    class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['talk']) && isset($in['links']['talk']['exists'])) ? $in['links']['talk']['exists'] : null))) ? 'new ' : '').'"
				    title="'.htmlentities(((is_array($in['links']['talk']) && isset($in['links']['talk']['title'])) ? $in['links']['talk']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('talkpagelinktext'),Array()), 'encq').'</a></span>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['contribs'])) ? $in['links']['contribs'] : null))) ? '<span>
					<a href="'.htmlentities(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['url'])) ? $in['links']['contribs']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['title'])) ? $in['links']['contribs']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('contribslink'),Array()), 'encq').'</a>
				</span>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['block'])) ? $in['links']['block'] : null))) ? '<span><a class="'.((!LCRun3::ifvar($cx, ((is_array($in['links']['block']) && isset($in['links']['block']['exists'])) ? $in['links']['block']['exists'] : null))) ? 'new ' : '').'"
				   href="'.htmlentities(((is_array($in['links']['block']) && isset($in['links']['block']['url'])) ? $in['links']['block']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['links']['block']) && isset($in['links']['block']['title'])) ? $in['links']['block']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.LCRun3::ch($cx, 'l10n', Array(Array('blocklink'),Array()), 'encq').'</a></span>' : '').'</span>
	' : '').'
</span>

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
			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
			   data-flow-interactive-handler="activateReplyPost">'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['title'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null))) ? '
			&#8226;
			<a href="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
			   data-flow-api-handler="activateEditPost"
			   data-flow-api-target="< .flow-post-main"
			   data-flow-interactive-handler="apiRequest"
			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet">
				'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-edit-post'),Array()), 'encq').'
			</a>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['thank'])) ? $in['actions']['thank'] : null))) ? '
			&#8226;
			'.'
			<a class="mw-ui-anchor mw-ui-constructive mw-ui-quiet mw-thanks-flow-thank-link"
			   href="'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['url'])) ? $in['actions']['thank']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			   title="'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['title'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['actions']['thank']) && isset($in['actions']['thank']['title'])) ? $in['actions']['thank']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>
		' : '').'
	</span>
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['previousRevisionId'])) ? $in['previousRevisionId'] : null))) ? '
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['diff-prev'])) ? $in['links']['diff-prev'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['url'])) ? $in['links']['diff-prev']['url'] : null), ENT_QUOTES, 'UTF-8').'"
			    class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
			    title="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'">
		' : '').'
			'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null),'flow-edited-ago',((is_array($in) && isset($in['1'])) ? $in['1'] : null)),Array()), 'encq').'
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['diff-prev'])) ? $in['links']['diff-prev'] : null))) ? '
			</a>
		' : '').'
		&#8226;
	' : '').'
	'.LCRun3::ch($cx, 'uuidTimestamp', Array(Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'flow-time-ago',((is_array($in) && isset($in['0'])) ? $in['0'] : null),((is_array($in) && isset($in['timestamp_readable'])) ? $in['timestamp_readable'] : null)),Array()), 'encq').'
</div>

		<div class="flow-menu">
	<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
	<ul class="mw-ui-button-container flow-list">
		'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['post'])) ? $in['links']['post'] : null))) ? '
			<li>
				<a class="mw-ui-button mw-ui-quiet"
				   href="'.htmlentities(((is_array($in['links']['post']) && isset($in['links']['post']['url'])) ? $in['links']['post']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['links']['post']) && isset($in['links']['post']['title'])) ? $in['links']['post']['title'] : null), ENT_QUOTES, 'UTF-8').'">
					<span class="wikiglyph wikiglyph-link"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-view'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="mw-ui-button mw-ui-quiet"
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
				<a class="mw-ui-button mw-ui-progressive mw-ui-quiet"
				   href="'.htmlentities(((is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['url'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['title'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="unhide">
					<span class="wikiglyph wikiglyph-flag"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-unhide-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="mw-ui-button mw-ui-quiet"
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
				<a class="mw-ui-button mw-ui-progressive mw-ui-quiet"
				   href="'.htmlentities(((is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['url'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['title'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="undelete">
					<span class="wikiglyph wikiglyph-eye-lid"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-undelete-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
		'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
			<li class="flow-menu-moderation-action">
				<a class="mw-ui-button mw-ui-destructive mw-ui-quiet"
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
				<a class="mw-ui-button mw-ui-progressive mw-ui-quiet"
				   href="'.htmlentities(((is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['url'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				   title="'.htmlentities(((is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['title'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				   data-flow-interactive-handler="moderationDialog"
				   data-template="flow_moderate_post"
				   data-role="unsuppress">
					<span class="wikiglyph wikiglyph-eye-lid"></span>
					'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-unsuppress-post'),Array()), 'encq').'
				</a>
			</li>
		' : '').'
	</ul>
</div>

	' : '').'
</div>

		';}).'

		'.'
		'.((!LCRun3::ifvar($cx, ((is_array($in) && isset($in['isPreview'])) ? $in['isPreview'] : null))) ? '
			<div class="flow-replies">
	'.LCRun3::sec($cx, ((is_array($in) && isset($in['replies'])) ? $in['replies'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array(Array(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['rootBlock'])) ? $cx['scopes'][0]['rootBlock'] : null),$in),Array()), $in, function($cx, $in) {return '
			<!-- eachPost nested replies -->
			'.LCRun3::ch($cx, 'post', Array(Array(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['rootBlock'])) ? $cx['scopes'][0]['rootBlock'] : null),$in),Array()), 'encq').'
		';}).'
	';}).'
	'.LCRun3::hbch($cx, 'ifCond', Array(Array(((is_array($cx['scopes'][0]['rootBlock']['submitted']) && isset($cx['scopes'][0]['rootBlock']['submitted']['postId'])) ? $cx['scopes'][0]['rootBlock']['submitted']['postId'] : null),'===',((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'ifCond', Array(Array(((is_array($cx['scopes'][0]['rootBlock']['submitted']) && isset($cx['scopes'][0]['rootBlock']['submitted']['action'])) ? $cx['scopes'][0]['rootBlock']['submitted']['action'] : null),'===','reply'),Array()), $in, function($cx, $in) {return '
			'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['reply'])) ? $in['actions']['reply'] : null))) ? '
	<form class="flow-post flow-reply-form"
	      method="POST"
	      action="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['url'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'"
	      id="flow-reply-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
	      data-flow-initial-state="collapsed"
	>
		<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]['rootBlock']) && isset($cx['scopes'][0]['rootBlock']['editToken'])) ? $cx['scopes'][0]['rootBlock']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topic_replyTo" value="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />
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


		'.LCRun3::hbch($cx, 'ifAnonymous', Array(Array(),Array()), $in, function($cx, $in) {return '
			<div class="flow-anon-warning">
	<div class="flow-anon-warning-mobile">
		'.'
		'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
			'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
	</div>

	'.'
	'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array()), $in, function($cx, $in) {return '
		<div class="flow-anon-warning-desktop">
			'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
				'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
		</div>
	';}).'
</div>
		';}).'

		<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content"
				name="topic_content"
				required
				data-flow-preview-template="flow_post"
				data-flow-expandable="true"
				class="mw-ui-input"
				type="text"
				placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-reply-topic-title-placeholder',LCRun3::ch($cx, 'plaintextSnippet', Array(Array(((is_array($in['content']) && isset($in['content']['format'])) ? $in['content']['format'] : null),((is_array($in['content']) && isset($in['content']['content'])) ? $in['content']['content'] : null)),Array()), 'raw')),Array()), 'encq').'"
				data-role="content">'.LCRun3::hbch($cx, 'ifCond', Array(Array(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['postId'])) ? $cx['scopes'][0]['submitted']['postId'] : null),'===',((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'';}).'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit"
			        class="mw-ui-button mw-ui-constructive"
			        data-flow-interactive-handler="apiRequest"
			        data-flow-api-handler="submitReply"
			        data-flow-api-target="< .flow-topic">'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['title'])) ? $in['actions']['reply']['title'] : null), ENT_QUOTES, 'UTF-8').'</button>
			'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array()), $in, function($cx, $in) {return '
	<button data-flow-api-handler="preview"
	        data-flow-api-target="< form textarea"
	        name="preview"
	        data-role="action"
	        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right"
	>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-preview'),Array()), 'encq').'</button>

	<button data-flow-interactive-handler="cancelForm"
	        data-role="cancel"
	        type="reset"
	        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right"
	>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>
';}).'

			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-reply'),Array()), 'encq').'</small>
		</div>
	</form>
' : '').'

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