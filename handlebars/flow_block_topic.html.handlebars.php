<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'timestamp' => 'Flow\TemplateHelper::timestamp',
            'post' => 'Flow\TemplateHelper::post',
),
        'blockhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-board">
	<div class="flow-topics">
		
		'.LCRun2::bch('eachPost', Array($in,((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)), $cx, $in, function($cx, $in) {return '
			<div class="flow-topic" id="flow-topic-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'">
	<div class="flow-topic-titlebar flow-click-interactive" data-flow-interactive-handler="topicCollapserToggle" tabindex="0">
		<h2 class="flow-topic-title">'.htmlentities(((is_array($in) && isset($in['content'])) ? $in['content'] : null), ENT_QUOTES, 'UTF-8').'</h2>
		<span class="flow-author">'.LCRun2::ch('l10n', Array('started_with_participants',$in), 'enc', $cx).'</span>
		<div class="flow-topic-meta">
			<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-inline" href="#flow-topic-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content">'.LCRun2::ch('l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'</a>
			&bull; '.LCRun2::ch('l10n', Array('comment_count',$in), 'enc', $cx).' &bull;
			'.((LCRun2::ifvar(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null))) ? '
				<!--span class="WikiFont WikiFont-speech-bubbles"></span--> '.LCRun2::ch('timestamp', Array(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null),'active_ago'), 'enc', $cx).'
			' : '
				<!--span class="WikiFont WikiFont-speech-bubble"></span--> '.LCRun2::ch('uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'started_ago'), 'enc', $cx).'
			').'
		</div>
		<span class="flow-reply-count"><span class="WikiFont WikiFont-speech-bubble"></span><span class="flow-reply-count-number">'.htmlentities(((is_array($in) && isset($in['reply_count'])) ? $in['reply_count'] : null), ENT_QUOTES, 'UTF-8').'</span></span>

		<div class="flow-menu">
			<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="WikiFont WikiFont-ellipsis"></span></a></div>
			<ul class="flow-ui-button-container">
				'.((LCRun2::ifvar(((is_array($in['links']) && isset($in['links']['topic-history'])) ? $in['links']['topic-history'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['url'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['title'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['title'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'</a></li>
				' : '').'
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['title'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="WikiFont WikiFont-lock"></span> '.LCRun2::ch('l10n', Array('Lock'), 'enc', $cx).'</a></li>
				' : '').'
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="WikiFont WikiFont-eye-lid"></span> '.LCRun2::ch('l10n', Array('Hide'), 'enc', $cx).'</a></li>
				' : '').'
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="WikiFont WikiFont-trash-slash"></span> '.LCRun2::ch('l10n', Array('Delete'), 'enc', $cx).'</a></li>
				' : '').'
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="WikiFont WikiFont-block-slash"></span> '.LCRun2::ch('l10n', Array('Suppress'), 'enc', $cx).'</a></li>
				' : '').'
			</ul>
		</div>
	</div>

	'.LCRun2::bch('eachPost', Array($cx['scopes'][0],((is_array($in) && isset($in['replies'])) ? $in['replies'] : null)), $cx, $in, function($cx, $in) {return '
		<!-- eachPost topic -->
		'.LCRun2::ch('post', Array($cx['scopes'][0],$in), 'enc', $cx).'
	';}).'

	<form class="flow-reply-form" data-flow-initial-state="collapsed">
	<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content" data-flow-expandable="true" class="mw-ui-input" type="text" placeholder="'.LCRun2::ch('l10n', Array('Reply_to_author_name',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'"></textarea>

	<div class="flow-form-actions flow-form-collapsible">
		<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'</button>
		<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Preview'), 'enc', $cx).'</button>
		<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Cancel'), 'enc', $cx).'</button>

		<small class="flow-terms-of-use plainlinks">'.LCRun2::ch('l10n', Array('reply_TOU'), 'enc', $cx).'</small>
	</div>
</form>

</div>

		';}).'
	</div>
</div>
';
}
?>