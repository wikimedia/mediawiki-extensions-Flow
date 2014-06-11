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
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '
<div class="flow-board-navigation">
	
	<div class="flow-board-navigation-inner">
		'.LCRun3::ch($cx, 'progressiveEnhancement', Array('context'=>$in,'insertionType'=>'insertion','sectionId'=>'flow-board-collapsers','templateName'=>'flow_board_collapsers_subcomponent'), 'encq', true).'

		
		<a href="javascript:void(0);" class="flow-board-navigator-active flow-board-navigator-first flow-ui-tooltip-target" data-tooltip-pointing="down" title="'.LCRun3::ch($cx, 'l10n', Array('flow-sorting-tooltip'), 'encq').'" data-flow-interactive-handler="boardFilterMenuToggle">'.LCRun3::sec($cx, ((is_array($in) && isset($in['ifEquals'])) ? $in['ifEquals'] : null), $in, false, function($cx, $in) {return '
				'.LCRun3::ch($cx, 'l10n', Array('flow-newest-topics'), 'encq').'
			';}, function($cx, $in) {return '
				'.LCRun3::ch($cx, 'l10n', Array('flow-recent-topics'), 'encq').'
			';}).'
			<span class="wikiglyph wikiglyph-caret"></span>'.htmlentities(((is_array($in) && isset($in['~'])) ? $in['~'] : null), ENT_QUOTES, 'UTF-8').'
		</a>
	</div>
	<div class="flow-board-filter-menu">
		<div class="flow-menu flow-menu-inverted">
			<div class="flow-menu-js-drop"><a href="javascript:void(0);" class="flow-board-filter-menu-activator"></a></div>
			'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['board-sort'])) ? $in['links']['board-sort'] : null))) ? '
				<ul class="flow-ui-button-container">
					<li><a href="'.htmlentities(((is_array($in['links']['board-sort']) && isset($in['links']['board-sort']['updated'])) ? $in['links']['board-sort']['updated'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-interactive-handler="apiRequest" data-flow-api-handler="board"><span class="wikiglyph wikiglyph-clock"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-recent-topics'), 'encq').'</a></li>
					<li><a href="'.htmlentities(((is_array($in['links']['board-sort']) && isset($in['links']['board-sort']['newest'])) ? $in['links']['board-sort']['newest'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-interactive-handler="apiRequest" data-flow-api-handler="board"><span class="wikiglyph wikiglyph-star-circle"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-newest-topics'), 'encq').'</a></li>
				</ul>
			' : '').'
		</div>
	</div>
</div>


<div class="flow-board">
	<ul class="flow-topic-navigation" style="display:none;">
	
</ul>

	'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['newtopic'])) ? $in['actions']['newtopic'] : null))) ? '
	<form action="'.htmlentities(((is_array($in['actions']['newtopic']) && isset($in['actions']['newtopic']['url'])) ? $in['actions']['newtopic']['url'] : null), ENT_QUOTES, 'UTF-8').'" method="POST" class="flow-newtopic-form" data-flow-initial-state="collapsed">
		<!-- @todo form errors -->
		<input type="hidden" name="topiclist_replyTo" value="'.htmlentities(((is_array($in) && isset($in['workflowId'])) ? $in['workflowId'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input name="topiclist_topic" class="mw-ui-input" type="text" placeholder="'.LCRun3::ch($cx, 'l10n', Array('flow-newtopic-start-placeholder'), 'encq').'"/>
		<textarea name="topiclist_content" class="mw-ui-input flow-form-collapsible" placeholder="'.LCRun3::ch($cx, 'l10n', Array('flow-newtopic-content-placeholder'), 'encq').'"></textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'l10n', Array('flow-newtopic-save'), 'encq').'</button>
		<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-preview'), 'encq').'</button>
			<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'</button>

			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10n', Array('flow-terms-of-use-new-topic'), 'encq').'</small>
		</div>
	</form>
' : '').'


	<div class="flow-topics">
		'.LCRun3::sec($cx, ((is_array($in) && isset($in['roots'])) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
			<!-- eachPost topiclist -->
			'.LCRun3::hbch($cx, 'eachPost', Array($cx['scopes'][0],$in), $in, function($cx, $in) {return '
				<div class="flow-topic flow-load-interactive" id="flow-topic-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-id="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-load-handler="topicElement">
	<div class="flow-topic-titlebar flow-click-interactive" data-flow-interactive-handler="topicCollapserToggle" tabindex="0">
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
					<li><a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-pencil"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-edit-post'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic-history'])) ? $in['links']['topic-history'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['url'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['title'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'"> <span class="wikiglyph wikiglyph-article"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-post-history'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'"> <span class="wikiglyph wikiglyph-link"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-view'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-regressive flow-ui-progressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['title'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-lock"></span> '.LCRun3::ch($cx, 'l10n', Array('TODO-lock'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-eye-lid"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-hide-post'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-trash"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-delete-post'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['title'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-block"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-post-action-suppress-post'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['close'])) ? $in['actions']['close'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['close']) && isset($in['actions']['close']['url'])) ? $in['actions']['close']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['close']) && isset($in['actions']['close']['title'])) ? $in['actions']['close']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-clock"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-close-topic'), 'encq').'</a></li>
				' : '').'
				'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['summarize'])) ? $in['actions']['summarize'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['summarize']) && isset($in['actions']['summarize']['url'])) ? $in['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['summarize']) && isset($in['actions']['summarize']['title'])) ? $in['actions']['summarize']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikiglyph wikiglyph-flag"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-summarize-topic'), 'encq').'</a></li>
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
		<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content" name="topic_content" data-flow-expandable="true" class="mw-ui-input" type="text" placeholder="'.LCRun3::ch($cx, 'l10n', Array('Reply_to_author_name',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'">'.LCRun3::sec($cx, ((is_array($in) && isset($in['ifEquals'])) ? $in['ifEquals'] : null), $in, false, function($cx, $in) {return '
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

	'.((LCRun3::ifvar($cx, ((is_array($in['links']['pagination']) && isset($in['links']['pagination']['fwd'])) ? $in['links']['pagination']['fwd'] : null))) ? '
		<a href="'.htmlentities(((is_array($in['links']['pagination']['fwd']) && isset($in['links']['pagination']['fwd']['url'])) ? $in['links']['pagination']['fwd']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['pagination']['fwd']) && isset($in['links']['pagination']['fwd']['title'])) ? $in['links']['pagination']['fwd']['title'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-ui-button flow-ui-progressive /*flow-ui-quiet flow-ui-thin*/"><span class="wikiglyph wikiglyph-article"></span> '.LCRun3::ch($cx, 'l10n', Array('flow-load-more'), 'encq').'</a>
	' : '').'
</div>
';
}
?>