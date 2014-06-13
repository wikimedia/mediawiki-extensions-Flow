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
            'timestamp' => 'Flow\TemplateHelper::timestamp',
            'html' => 'Flow\TemplateHelper::html',
            'post' => 'Flow\TemplateHelper::post',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifEquals' => 'Flow\TemplateHelper::ifEquals',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
		<ul>
		'.LCRun3::sec($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, true, function($cx, $in) {return '
			<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
		';}).'
		</ul>
	' : '').'
	<div class="flow-board-link">
		'.((LCRun3::ifvar($cx, ((is_array($in['board']) && isset($in['board']['is_user_namespace'])) ? $in['board']['is_user_namespace'] : null))) ? '
			'.LCRun3::ch($cx, 'l10nParse', Array('flow-topic-permalink-warning-user-board',((is_array($in['board']) && isset($in['board']['title_text'])) ? $in['board']['title_text'] : null),((is_array($in['board']) && isset($in['board']['link'])) ? $in['board']['link'] : null)), 'encq').'
		' : '
			'.LCRun3::ch($cx, 'l10nParse', Array('flow-topic-permalink-warning',((is_array($in['board']) && isset($in['board']['title_prefixed_text'])) ? $in['board']['title_prefixed_text'] : null),((is_array($in['board']) && isset($in['board']['link'])) ? $in['board']['link'] : null)), 'encq').'
		').'
	</div>
	<div class="flow-topics">
		
		'.LCRun3::sec($cx, ((is_array($in) && isset($in['roots'])) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
	
	'.LCRun3::hbch($cx, 'eachPost', Array($cx['scopes'][0],$in), $in, function($cx, $in) {return '
		<div class="flow-topic flow-load-interactive '.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? 'flow-topic-collapsed-invert' : '').'" id="flow-topic-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-id="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-load-handler="topicElement">
	<div class="flow-topic-titlebar flow-click-interactive" data-flow-interactive-handler="topicCollapserToggle" tabindex="0">
		'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? '
			<h2 class="flow-moderated-topic-title">'.LCRun3::ch($cx, 'l10n', Array('post_moderation_state',((is_array($in) && isset($in['moderateState'])) ? $in['moderateState'] : null),((is_array($in) && isset($in['replyToId'])) ? $in['replyToId'] : null),((is_array($in['moderator']) && isset($in['moderator']['name'])) ? $in['moderator']['name'] : null)), 'encq').'</h2>
			<div>@Todo - Add css to toggle between "xxx is hidden by xxx" and real title</div>
		' : '').'
		<h2 class="flow-topic-title">'.htmlentities(((is_array($in) && isset($in['content'])) ? $in['content'] : null), ENT_QUOTES, 'UTF-8').'</h2>
		<span class="flow-author">'.LCRun3::ch($cx, 'l10n', Array('started_with_participants',$in), 'encq').'</span>
		<div class="flow-topic-meta">
			<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-inline" href="#flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content">'.LCRun3::ch($cx, 'l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'</a>
			&bull; '.LCRun3::ch($cx, 'l10n', Array('comment_count',$in), 'encq').' &bull;
			'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null))) ? '
				<!--span class="wikiglyph wikiglyph-speech-bubbles"></span--> '.LCRun3::ch($cx, 'timestamp', Array(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null),'active_ago'), 'encq').'
			' : '
				<!--span class="wikiglyph wikiglyph-speech-bubble"></span--> '.LCRun3::ch($cx, 'uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'started_ago'), 'encq').'
			').'
		</div>
		<span class="flow-reply-count"><span class="wikiglyph wikiglyph-speech-bubble"></span><span class="flow-reply-count-number">'.htmlentities(((is_array($in) && isset($in['reply_count'])) ? $in['reply_count'] : null), ENT_QUOTES, 'UTF-8').'</span></span>

		<div class="flow-menu">
			<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div>
			<ul class="flow-ui-button-container">
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-pencil"></span>
							'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-edit-title'), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic-history'])) ? $in['links']['topic-history'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['url'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['title'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-article"></span>
							'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-history'), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-link"></span>
							'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-view'), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-regressive flow-ui-progressive flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['title'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-lock"></span>
							'.LCRun3::ch($cx, 'l10n', Array('TODO-lock'), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-eye-lid"></span>
							'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-hide-topic'), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-trash"></span>
							'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-delete-topic'), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['title'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-block"></span>
							'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-suppress-topic'), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['close'])) ? $in['actions']['close'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['actions']['close']) && isset($in['actions']['close']['url'])) ? $in['actions']['close']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['actions']['close']) && isset($in['actions']['close']['title'])) ? $in['actions']['close']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-clock"></span>
							'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-close-topic'), 'encq').'
						</a>
					</li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['summarize'])) ? $in['actions']['summarize'] : null))) ? '
					<li>
						<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin"
						   href="'.htmlentities(((is_array($in['actions']['summarize']) && isset($in['actions']['summarize']['url'])) ? $in['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'"
						   title="'.htmlentities(((is_array($in['actions']['summarize']) && isset($in['actions']['summarize']['title'])) ? $in['actions']['summarize']['title'] : null), ENT_QUOTES, 'UTF-8').'">
							<span class="wikiglyph wikiglyph-flag"></span>
							'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['summary'])) ? $in['summary'] : null))) ? '
								'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-resummarize-topic'), 'encq').'
							' : '
								'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-summarize-topic'), 'encq').'
							').'
						</a>
					</li>
				' : '').'
			</ul>
		</div>
		'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['summary'])) ? $in['summary'] : null))) ? '
			<div class="flow-topic-summary">
				'.LCRun3::ch($cx, 'html', Array(((is_array($in) && isset($in['summary'])) ? $in['summary'] : null)), 'encq').'
			</div>
		' : '').'
	</div>

	'.LCRun3::sec($cx, ((is_array($in) && isset($in['replies'])) ? $in['replies'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array($cx['scopes'][0],$in), $in, function($cx, $in) {return '
			<!-- eachPost topic -->
			'.LCRun3::ch($cx, 'post', Array($cx['scopes'][0],$in), 'encq').'
		';}).'
	';}).'

	'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['reply'])) ? $in['actions']['reply'] : null))) ? '
	<form class="flow-reply-form" data-flow-initial-state="collapsed" method="POST" action="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['url'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'">
	    <input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topic_replyTo" value="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />
		<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content" name="topic_content" data-flow-expandable="true" class="mw-ui-input" type="text" placeholder="'.LCRun3::ch($cx, 'l10n', Array('Reply_to_author_name',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'">'.LCRun3::hbch($cx, 'ifEquals', Array(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['postId'])) ? $cx['scopes'][0]['submitted']['postId'] : null),((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)), $in, function($cx, $in) {return '
				'.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'';}).'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'</button>
			<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-preview'), 'encq').'</button>
			<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'</button>

			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10n', Array('flow-terms-of-use-reply'), 'encq').'</small>
		</div>
	</form>
' : '').'

</div>

	';}).'
';}).'
	</div>
</div>
';
}
?>
