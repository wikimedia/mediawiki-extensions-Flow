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
            'math' => 'Flow\TemplateHelper::math',
            'post' => 'Flow\TemplateHelper::post',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
        'blockhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '
<div class="flow-board-navigation">
	'.((LCRun2::ifvar(((is_array($in['links']) && isset($in['links']['search'])) ? $in['links']['search'] : null))) ? '
		<a href="'.htmlentities(((is_array($in['links']['search']) && isset($in['links']['search']['url'])) ? $in['links']['search']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['search']) && isset($in['links']['search']['title'])) ? $in['links']['search']['title'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-board-navigator-last"><span class="wikicon wikicon-magnifying-glass flow-ui-tooltip-target" title="Search"></span></a>
	' : '
		<!-- @todo make disabled class do something? -->
		<a href="#" class="flow-board-navigator-last disabled"><span class="wikicon wikicon-magnifying-glass flow-ui-tooltip-target" title="Search"></span></a>
	').'
	<div>
		'.LCRun2::ch('progressiveEnhancement', Array($in,'insertion','flow-board-collapsers','flow_board_collapsers_subcomponent'), 'enc', $cx).'

		
		<a href="'.htmlentities(((is_array($in['links']) && isset($in['links']['unknown'])) ? $in['links']['unknown'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-board-navigator-active flow-board-navigator-first flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('Sorting_tooltip'), 'enc', $cx).'">'.LCRun2::ch('l10n', Array('Newest_topics'), 'enc', $cx).'</a>
	</div>
</div>


<div class="flow-board">
	<ul class="flow-topic-navigation">
	<li class="flow-topic-navigation-heading"><h5>'.LCRun2::ch('l10n', Array('Topics_n',$in), 'enc', $cx).'</h5></li>
	'.LCRun2::bch('eachPost', Array($in,((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)), $cx, $in, function($cx, $in) {return '
		<li class="flow-topic-navigator-wrap">
			<a href="#flow-topic-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-topic-navigator">
				'.LCRun2::ch('math', Array($cx['sp_vars']['index'],'+','1'), 'enc', $cx).'. '.htmlentities(((is_array($in) && isset($in['content'])) ? $in['content'] : null), ENT_QUOTES, 'UTF-8').'
				<span class="flow-topic-navigator-meta">
					'.((LCRun2::ifvar(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null))) ? '
						'.LCRun2::ch('timestamp', Array(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null),'active_ago'), 'enc', $cx).'
					' : '
						'.LCRun2::ch('uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'started_ago'), 'enc', $cx).'
					').'
				</span>
			</a>
		</li>
	';}).'
	<li class="flow-topic-navigation-footer">
		'.LCRun2::ch('l10n', Array('topic_count_sidebar',$in), 'enc', $cx).'
		'.((LCRun2::ifvar(((is_array($in['links']['pagination']) && isset($in['links']['pagination']['fwd'])) ? $in['links']['pagination']['fwd'] : null))) ? '
			<a href="'.htmlentities(((is_array($in['links']['pagination']['fwd']) && isset($in['links']['pagination']['fwd']['url'])) ? $in['links']['pagination']['fwd']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['pagination']['fwd']) && isset($in['links']['pagination']['fwd']['title'])) ? $in['links']['pagination']['fwd']['title'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin"><span class="wikicon wikicon-article"></span> '.LCRun2::ch('l10n', Array('Load_More'), 'enc', $cx).'</a>
		' : '
			<!-- @todo make disabled class do something? -->
			<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin disabled"><span class="wikicon wikicon-article"></span> '.LCRun2::ch('l10n', Array('Load_More'), 'enc', $cx).'</a>
		').'
	</li>
</ul>
	'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['newtopic'])) ? $in['actions']['newtopic'] : null))) ? '
	<form action="'.htmlentities(((is_array($in['actions']['newtopic']) && isset($in['actions']['newtopic']['url'])) ? $in['actions']['newtopic']['url'] : null), ENT_QUOTES, 'UTF-8').'" method="POST" class="flow-newtopic-form" data-flow-initial-state="collapsed">
		<!-- @todo form errors -->
		<input type="hidden" name="topiclist_replyTo" value="'.htmlentities(((is_array($in) && isset($in['workflowId'])) ? $in['workflowId'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input name="topiclist_topic" class="mw-ui-input" type="text" placeholder="'.LCRun2::ch('l10n', Array('Start_a_new_topic'), 'enc', $cx).'"/>
		<textarea name="topiclist_content" class="mw-ui-input flow-form-collapsible" placeholder="'.LCRun2::ch('l10n', Array('topic_details_placeholder'), 'enc', $cx).'"></textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Add_Topic'), 'enc', $cx).'</button>
			<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Preview'), 'enc', $cx).'</button>
			<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Cancel'), 'enc', $cx).'</button>

			<small class="flow-terms-of-use plainlinks">'.LCRun2::ch('l10n', Array('topic_TOU'), 'enc', $cx).'</small>
		</div>
	</form>
' : '').'


	<div class="flow-topics">
		'.LCRun2::bch('eachPost', Array($in,((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)), $cx, $in, function($cx, $in) {return '
			<!-- eachPost topiclist -->
			<div class="flow-topic" id="flow-topic-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'">
	<div class="flow-topic-titlebar flow-click-interactive" data-flow-interactive-handler="topicCollapserToggle" tabindex="0">
		<h2 class="flow-topic-title">'.htmlentities(((is_array($in) && isset($in['content'])) ? $in['content'] : null), ENT_QUOTES, 'UTF-8').'</h2>
		<span class="flow-author">'.LCRun2::ch('l10n', Array('started_with_participants',$in), 'enc', $cx).'</span>
		<div class="flow-topic-meta">
			<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-inline" href="#flow-topic-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content">'.LCRun2::ch('l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'</a>
			&bull; '.LCRun2::ch('l10n', Array('comment_count',$in), 'enc', $cx).' &bull;
			'.((LCRun2::ifvar(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null))) ? '
				<!--span class="wikicon wikicon-speech-bubbles"></span--> '.LCRun2::ch('timestamp', Array(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null),'active_ago'), 'enc', $cx).'
			' : '
				<!--span class="wikicon wikicon-speech-bubble"></span--> '.LCRun2::ch('uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'started_ago'), 'enc', $cx).'
			').'
		</div>
		<span class="flow-reply-count"><span class="wikicon wikicon-speech-bubble"></span><span class="flow-reply-count-number">'.htmlentities(((is_array($in) && isset($in['reply_count'])) ? $in['reply_count'] : null), ENT_QUOTES, 'UTF-8').'</span></span>

		<div class="flow-menu">
			<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikicon wikicon-ellipsis"></span></a></div>
			<ul class="flow-ui-button-container">
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'</a></li>
				' : '').'	
				'.((LCRun2::ifvar(((is_array($in['links']) && isset($in['links']['topic-history'])) ? $in['links']['topic-history'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['url'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['title'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['title'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'</a></li>
				' : '').'
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['title'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-lock"></span> '.LCRun2::ch('l10n', Array('Lock'), 'enc', $cx).'</a></li>
				' : '').'
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-eye-lid"></span> '.LCRun2::ch('l10n', Array('Hide'), 'enc', $cx).'</a></li>
				' : '').'
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-trash-slash"></span> '.LCRun2::ch('l10n', Array('Delete'), 'enc', $cx).'</a></li>
				' : '').'
				'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
					<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"><span class="wikicon wikicon-block-slash"></span> '.LCRun2::ch('l10n', Array('Suppress'), 'enc', $cx).'</a></li>
				' : '').'
			</ul>
		</div>
	</div>

	'.LCRun2::bch('eachPost', Array($cx['scopes'][0],((is_array($in) && isset($in['replies'])) ? $in['replies'] : null)), $cx, $in, function($cx, $in) {return '
		<!-- eachPost topic -->
		'.LCRun2::ch('post', Array($cx['scopes'][0],$in), 'enc', $cx).'
	';}).'

	'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['reply'])) ? $in['actions']['reply'] : null))) ? '
	<form class="flow-reply-form" data-flow-initial-state="collapsed" method="POST" action="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['url'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'">
	    <input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topic_replyTo" value="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />
		<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content" name="topic_content" data-flow-expandable="true" class="mw-ui-input" type="text" placeholder="'.LCRun2::ch('l10n', Array('Reply_to_author_name',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'">'.((LCRun2::ifvar(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'</button>
			<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Preview'), 'enc', $cx).'</button>
			<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Cancel'), 'enc', $cx).'</button>

			<small class="flow-terms-of-use plainlinks">'.LCRun2::ch('l10n', Array('reply_TOU'), 'enc', $cx).'</small>
		</div>
	</form>
' : '').'

</div>

		';}).'
	</div>
</div>
';
}
?>