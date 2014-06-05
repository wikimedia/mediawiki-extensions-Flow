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
            'post' => 'Flow\TemplateHelper::post',
            'addReturnTo' => 'Flow\TemplateHelper::addReturnTo',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
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

	<div class="flow-topics">
		
		'.LCRun3::sec($cx, ((is_array($in) && isset($in['roots'])) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
			'.LCRun3::hbch($cx, 'eachPost', Array($cx['scopes'][0],$in), $in, function($cx, $in) {return '
				<div class="flow-topic flow-load-interactive" id="flow-topic-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-id="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-load-handler="topicElement">
	<div class="flow-topic-titlebar flow-click-interactive" data-flow-interactive-handler="topicCollapserToggle" tabindex="0">
		<h2 class="flow-topic-title">'.htmlentities(((is_array($in) && isset($in['content'])) ? $in['content'] : null), ENT_QUOTES, 'UTF-8').'</h2>
		<span class="flow-author">'.LCRun3::ch($cx, 'l10n', Array('started_with_participants',$in), 'encq').'</span>
		<div class="flow-topic-meta">
			<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-inline" href="#flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content">'.LCRun3::ch($cx, 'l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'</a>
			&bull; '.LCRun3::ch($cx, 'l10n', Array('comment_count',$in), 'encq').' &bull;
			'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null))) ? '
				<!--span class="wikicon wikicon-speech-bubbles"></span--> '.LCRun3::ch($cx, 'timestamp', Array(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null),'active_ago'), 'encq').'
			' : '
				<!--span class="wikicon wikicon-speech-bubble"></span--> '.LCRun3::ch($cx, 'uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'started_ago'), 'encq').'
			').'
		</div>
		<span class="flow-reply-count"><span class="wikicon wikicon-speech-bubble"></span><span class="flow-reply-count-number">'.htmlentities(((is_array($in) && isset($in['reply_count'])) ? $in['reply_count'] : null), ENT_QUOTES, 'UTF-8').'</span></span>

		<div class="flow-menu">
			<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikicon wikicon-ellipsis"></span></a></div>
			<ul class="flow-ui-button-container">
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-pencil"></span> '.LCRun3::ch($cx, 'l10n', Array('Edit'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic-history'])) ? $in['links']['topic-history'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['url'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['title'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'"> <span class="wikicon wikicon-article"></span> '.LCRun3::ch($cx, 'l10n', Array('History'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'"> <span class="wikicon wikicon-link"></span> '.LCRun3::ch($cx, 'l10n', Array('Permalink'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['title'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-lock"></span> '.LCRun3::ch($cx, 'l10n', Array('Lock'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-eye-lid"></span> '.LCRun3::ch($cx, 'l10n', Array('Hide'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-trash"></span> '.LCRun3::ch($cx, 'l10n', Array('Delete'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-block"></span> '.LCRun3::ch($cx, 'l10n', Array('Suppress'), 'encq').'</a></li>
				' : '').'
			</ul>
		</div>
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
		'.LCRun3::hbch($cx, 'ifAnonymous', Array(), $in, function($cx, $in) {return '
			<span class="flow-ui-tooltip flow-ui-destructive flow-ui-tooltip-down">
	'.LCRun3::ch($cx, 'addReturnTo', Array(((is_array($in) && isset($in['(addReturnTo'])) ? $in['(addReturnTo'] : null),((is_array($in['"http:']['localhost:8081']['wiki']) && isset($in['"http:']['localhost:8081']['wiki']['Special:Userlogin")'])) ? $in['"http:']['localhost:8081']['wiki']['Special:Userlogin")'] : null)), 'encq').'
	<span class="flow-ui-tooltip-triangle"></span>
</span>
		';}).'
		<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content" name="topic_content" data-flow-expandable="true" class="mw-ui-input" type="text" placeholder="'.LCRun3::ch($cx, 'l10n', Array('Reply_to_author_name',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'">'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'</button>
			<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('Preview'), 'encq').'</button>
			<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('Cancel'), 'encq').'</button>

			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10n', Array('reply_TOU'), 'encq').'</small>
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