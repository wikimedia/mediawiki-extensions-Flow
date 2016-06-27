use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
            'ifCond' => 'Flow\TemplateHelper::ifCond',
            'tooltip' => 'Flow\TemplateHelper::tooltip',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
);
    $partials = array('flow_board_navigation' => function ($cx, $in, $sp) {return ''.$sp.''.((LR::ifvar($cx, ((isset($in['links']['board-sort']) && is_array($in['links'])) ? $in['links']['board-sort'] : null), false)) ? '<div class="flow-board-navigation" data-flow-load-handler="boardNavigation">
'.$sp.'	<div class="flow-error-container">
'.$sp.'	</div>
'.$sp.'</div>
'.$sp.'' : '').'';},
'flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, ((isset($in['html']) && is_array($in)) ? $in['html'] : null)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_anon_warning' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-anon-warning">
'.$sp.'	<div class="flow-anon-warning-mobile">
'.$sp.''.LR::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in)use($sp){return ''.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'';}).'	</div>
'.$sp.'
'.$sp.''.LR::hbch($cx, 'progressiveEnhancement', array(array(),array()), $in, false, function($cx, $in)use($sp){return '		<div class="flow-anon-warning-desktop">
'.$sp.''.LR::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in)use($sp){return ''.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'';}).'		</div>
'.$sp.'';}).'</div>
';},
'flow_form_cancel_button' => function ($cx, $in, $sp) {return ''.$sp.'<button data-flow-interactive-handler="cancelForm"
'.$sp.'        data-role="cancel"
'.$sp.'        type="reset"
'.$sp.'        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"
'.$sp.'
'.$sp.'>
'.$sp.''.((LR::ifvar($cx, ((isset($in['msg']) && is_array($in)) ? $in['msg'] : null), false)) ? ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'' : ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'').'</button>
';},
'flow_newtopic_form' => function ($cx, $in, $sp) {return ''.$sp.''.((LR::ifvar($cx, ((isset($in['actions']['newtopic']) && is_array($in['actions'])) ? $in['actions']['newtopic'] : null), false)) ? '	<form action="'.LR::encq($cx, ((isset($in['actions']['newtopic']['url']) && is_array($in['actions']['newtopic'])) ? $in['actions']['newtopic']['url'] : null)).'" method="POST" class="flow-newtopic-form" data-flow-initial-state="'.((LR::ifvar($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null), false)) ? 'expanded' : 'collapsed').'">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '		').'
'.$sp.''.LR::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_anon_warning', array(array($in),array()), '			').'';}).'
'.$sp.'		<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, ((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null)).'" />
'.$sp.'		<input type="hidden" name="topiclist_replyTo" value="'.LR::encq($cx, ((isset($in['workflowId']) && is_array($in)) ? $in['workflowId'] : null)).'" />
'.$sp.'		<input name="topiclist_topic" class="mw-ui-input mw-ui-input-large"
'.$sp.'			required
'.$sp.'			'.((LR::ifvar($cx, ((isset($in['submitted']['topic']) && is_array($in['submitted'])) ? $in['submitted']['topic'] : null), false)) ? 'value="'.LR::encq($cx, ((isset($in['submitted']['topic']) && is_array($in['submitted'])) ? $in['submitted']['topic'] : null)).'"' : '').'
'.$sp.'			type="text"
'.$sp.'			placeholder="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'			data-role="title"
'.$sp.'		/>
'.$sp.'		<div class="flow-editor">
'.$sp.'			<textarea name="topiclist_content"
'.$sp.'			          class="mw-ui-input flow-form-collapsible mw-ui-input-large'.((LR::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null), false)) ? ' flow-form-collapsible-collapsed' : '').'"
'.$sp.'			          placeholder="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'			          data-role="content"
'.$sp.'			          required
'.$sp.'			>'.((LR::ifvar($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null), false)) ? ''.LR::encq($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null)).'' : '').'</textarea>
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<div class="flow-form-actions flow-form-collapsible'.((LR::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null), false)) ? ' flow-form-collapsible-collapsed' : '').'">
'.$sp.'			<button data-role="submit"
'.$sp.'				class="mw-ui-button mw-ui-constructive mw-ui-flush-right">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</button>
'.$sp.''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()), '			').'			<small class="flow-terms-of-use plainlinks">'.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'</small>
'.$sp.'		</div>
'.$sp.'	</form>
'.$sp.'' : '').'';},
'flow_topic_moderation_flag' => function ($cx, $in, $sp) {return ''.$sp.'<span class="mw-ui-icon mw-ui-icon-before'.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','lock'),array()), $in, false, function($cx, $in)use($sp){return ' mw-ui-icon-check';}).''.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','hide'),array()), $in, false, function($cx, $in)use($sp){return ' mw-ui-icon-flag';}).''.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null),'===','delete'),array()), $in, false, function($cx, $in)use($sp){return ' mw-ui-icon-remove';}).'"></span>
';},
'flow_post_moderation_state' => function ($cx, $in, $sp) {return ''.$sp.'<span class="plainlinks">'.((LR::ifvar($cx, ((isset($in['replyToId']) && is_array($in)) ? $in['replyToId'] : null), false)) ? ''.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'' : ''.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'').'</span>
';},
'flow_topic_titlebar_content' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic-titlebar-container">
'.$sp.'    <h2 class="flow-topic-title flow-load-interactive '.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? 'flow-collapse-toggle flow-click-interactive' : '').'"
'.$sp.'        data-flow-topic-title="'.LR::encq($cx, ((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)).'"
'.$sp.'        data-flow-load-handler="topicTitle"
'.$sp.''.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? '        data-flow-interactive-handler="collapserCollapsibleToggle"
'.$sp.'' : '').'            >
'.$sp.'		'.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check"></span> ' : '').''.LR::encq($cx, ((isset($in['escapeContent']) && is_array($in)) ? $in['escapeContent'] : null)).'</h2>
'.$sp.'    <div class="flow-topic-meta">
'.$sp.''.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? '<a class="expand-collapse-posts-link flow-collapse-toggle flow-click-interactive"
'.$sp.'               href="javascript:void(0);"
'.$sp.'               title="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'               data-collapsed-title="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'               data-expanded-title="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'               data-flow-interactive-handler="collapserCollapsibleToggle"
'.$sp.'                    >'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>' : ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'').' &bull;
'.$sp.'
'.$sp.'        <a href="'.LR::encq($cx, ((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null)).'" class="flow-timestamp-anchor">
'.$sp.''.((LR::ifvar($cx, ((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null), false)) ? '				'.LR::encq($cx, ((isset($in['timestamp']) && is_array($in)) ? $in['timestamp'] : null)).'
'.$sp.'' : '				'.LR::encq($cx, ((isset($in['uuidTimestamp']) && is_array($in)) ? $in['uuidTimestamp'] : null)).'
'.$sp.'').'        </a>
'.$sp.'    </div>
'.$sp.''.((LR::ifvar($cx, ((isset($in['isModeratedNotLocked']) && is_array($in)) ? $in['isModeratedNotLocked'] : null), false)) ? '        <div class="flow-moderated-topic-title flow-ui-text-truncated">'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).''.LR::p($cx, 'flow_topic_moderation_flag', array(array($in),array())).'
'.$sp.''.LR::p($cx, 'flow_post_moderation_state', array(array($in),array()), '			').'        </div>
'.$sp.'        <div class="flow-moderated-topic-reason">
'.$sp.'			'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
'.$sp.'			'.LR::encq($cx, ((isset($in['escapeContent']) && is_array($in)) ? $in['escapeContent'] : null)).'
'.$sp.'        </div>
'.$sp.'' : '').'    <span class="flow-reply-count"><span class="flow-reply-count-number">'.LR::encq($cx, ((isset($in['reply_count']) && is_array($in)) ? $in['reply_count'] : null)).'</span></span>
'.$sp.'</div>';},
'flow_topic_titlebar_summary' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic-summary-container '.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? 'flow-collapse-toggle flow-click-interactive' : '').'"
'.$sp.''.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? '		data-flow-interactive-handler="collapserCollapsibleToggle"
'.$sp.'' : '').'		>
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '	').''.((LR::ifvar($cx, ((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null), false)) ? '		<div class="flow-topic-summary">
'.$sp.'			<div class="flow-topic-summary-author">
'.$sp.''.LR::hbch($cx, 'ifCond', array(array(((isset($in['revision']['creator']['name']) && is_array($in['revision']['creator'])) ? $in['revision']['creator']['name'] : null),'===',((isset($in['revision']['author']['name']) && is_array($in['revision']['author'])) ? $in['revision']['author']['name'] : null)),array()), $in, false, function($cx, $in)use($sp){return '					'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
'.$sp.'';}, function($cx, $in)use($sp){return '					'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
'.$sp.'					<a href="'.LR::encq($cx, ((isset($in['revision']['links']['diff-prev']['url']) && is_array($in['revision']['links']['diff-prev'])) ? $in['revision']['links']['diff-prev']['url'] : null)).'" class="flow-timestamp-anchor">'.LR::encq($cx, ((isset($in['uuidTimestamp']) && is_array($in)) ? $in['uuidTimestamp'] : null)).'</a>
'.$sp.'';}).'			</div>
'.$sp.'			<div class="flow-topic-summary-content">
'.$sp.'				'.LR::encq($cx, ((isset($in['escapeContent']) && is_array($in)) ? $in['escapeContent'] : null)).'
'.$sp.'			</div>
'.$sp.'			<div style="clear: both;"></div>
'.$sp.'		</div>
'.$sp.'' : '').'</div>
';},
'flow_topic_titlebar_watch' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic-watchlist flow-watch-link">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '	').'
'.$sp.'	<a href="'.((LR::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null), false)) ? ''.LR::encq($cx, ((isset($in['links']['unwatch-topic']['url']) && is_array($in['links']['unwatch-topic'])) ? $in['links']['unwatch-topic']['url'] : null)).'' : ''.LR::encq($cx, ((isset($in['links']['watch-topic']['url']) && is_array($in['links']['watch-topic'])) ? $in['links']['watch-topic']['url'] : null)).'').'"
'.$sp.'	   class="mw-ui-anchor mw-ui-hovericon mw-ui-constructive '.((!LR::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null), false)) ? 'mw-ui-quiet' : '').'
'.$sp.''.((LR::ifvar($cx, ((isset($in['isWatched']) && is_array($in)) ? $in['isWatched'] : null), false)) ? 'flow-watch-link-unwatch' : 'flow-watch-link-watch').'"
'.$sp.'	   data-flow-api-handler="watchItem"
'.$sp.'	   data-flow-api-target="< .flow-topic-watchlist"
'.$sp.'	   data-flow-api-method="POST">'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<span class="flow-unwatch mw-ui-icon mw-ui-icon-before mw-ui-icon-unStar-constructive" title="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"></span>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).''.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<span class="flow-watch mw-ui-icon mw-ui-icon-before mw-ui-icon-star mw-ui-icon-star-constructive-hover" title="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"></span>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</a>
'.$sp.'</div>
';},
'flow_moderation_actions_list' => function ($cx, $in, $sp) {return ''.$sp.'<section>'.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','topic'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-edit-title-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']['topic-history']) && is_array($in['links'])) ? $in['links']['topic-history'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-clock"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['summarize']) && is_array($in['actions'])) ? $in['actions']['summarize'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-summarize-topic-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['summarize']['url']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-stripeToC mw-ui-icon-stripeToC-progressive-hover"></span> ' : '').''.((LR::ifvar($cx, ((isset($in['summary']['revision']['content']['content']) && is_array($in['summary']['revision']['content'])) ? $in['summary']['revision']['content']['content'] : null), false)) ? ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'' : ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'').'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}).''.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-role="lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-progressive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-role="unlock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation mw-ui-icon-ongoingConversation-progressive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'				   data-role="lock"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-progressive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'				   data-role="unlock"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation mw-ui-icon-ongoingConversation-progressive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}).''.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','post'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null), false)) ? '<li>
'.$sp.'				<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-edit-post-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null)).'"
'.$sp.'				>'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>
'.$sp.'			</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']['post']) && is_array($in['links'])) ? $in['links']['post'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['post']['url']) && is_array($in['links']['post'])) ? $in['links']['post']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}).'</section>
'.$sp.'
'.$sp.'<section>'.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['undo']) && is_array($in['actions'])) ? $in['actions']['undo'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['undo']['url']) && is_array($in['actions']['undo'])) ? $in['actions']['undo']['url'] : null)).'"
'.$sp.'				>'.LR::encq($cx, ((isset($in['actions']['undo']['title']) && is_array($in['actions']['undo'])) ? $in['actions']['undo']['title'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}).''.((LR::ifvar($cx, ((isset($in['actions']['hide']) && is_array($in['actions'])) ? $in['actions']['hide'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['hide']['url']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="hide">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="unhide">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['delete']) && is_array($in['actions'])) ? $in['actions']['delete'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['delete']['url']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="delete">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-remove mw-ui-icon-remove-destructive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['undelete']) && is_array($in['actions'])) ? $in['actions']['undelete'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['undelete']['url']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="undelete">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-remove mw-ui-icon-remove-destructive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['suppress']) && is_array($in['actions'])) ? $in['actions']['suppress'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['suppress']['url']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="suppress">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions'])) ? $in['actions']['unsuppress'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['unsuppress']['url']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="unsuppress">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'</section>
';},
'flow_topic_titlebar' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic-titlebar">
'.$sp.''.LR::p($cx, 'flow_topic_titlebar_content', array(array($in),array()), '	').''.LR::p($cx, 'flow_topic_titlebar_summary', array(array(((isset($in['summary']) && is_array($in)) ? $in['summary'] : null)),array('isLocked'=>((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))), '	').''.((LR::ifvar($cx, ((isset($in['watchable']) && is_array($in)) ? $in['watchable'] : null), false)) ? ''.LR::p($cx, 'flow_topic_titlebar_watch', array(array($in),array()), '		').'' : '').'	<div class="flow-menu flow-menu-hoverable">
'.$sp.'		<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ellipsis"></span></a></div>
'.$sp.'		<ul class="mw-ui-button-container flow-list">
'.$sp.''.LR::p($cx, 'flow_moderation_actions_list', array(array($in),array('moderationType'=>'topic','moderationTarget'=>'title','moderationTemplate'=>'topic','moderationContainerClass'=>'flow-menu','moderationMwUiClass'=>'mw-ui-button','moderationIcons'=>true)), '			').'		</ul>
'.$sp.'	</div>
'.$sp.'</div>
';},
'flow_reply_form' => function ($cx, $in, $sp) {return ''.$sp.''.((!LR::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null), false)) ? '
'.$sp.'<form class="flow-post flow-reply-form"
'.$sp.'      method="POST"
'.$sp.'      action="'.LR::encq($cx, ((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null)).'"
'.$sp.'      id="flow-reply-'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'      data-flow-initial-state="collapsed"
'.$sp.'>
'.$sp.'	<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, ((isset($cx['sp_vars']['root']['rootBlock']['editToken']) && is_array($cx['sp_vars']['root']['rootBlock'])) ? $cx['sp_vars']['root']['rootBlock']['editToken'] : null)).'" />
'.$sp.'	<input type="hidden" name="topic_replyTo" value="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'" />
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '	').'
'.$sp.''.LR::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_anon_warning', array(array($in),array()), '		').'';}).'
'.$sp.'	<div class="flow-editor">
'.$sp.'		<textarea id="flow-post-'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'-form-content"
'.$sp.'		          name="topic_content"
'.$sp.'		          required
'.$sp.'		          data-flow-expandable="true"
'.$sp.'		          class="mw-ui-input flow-click-interactive"
'.$sp.'		          type="text"
'.$sp.'			          placeholder="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'		          data-role="content"
'.$sp.'
'.$sp.'		>'.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['submitted'] : null), false)) ? ''.LR::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['submitted']['postId']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in)use($sp){return ''.LR::encq($cx, ((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null)).'';}).'' : '').'</textarea>
'.$sp.'	</div>
'.$sp.'
'.$sp.'	<div class="flow-form-actions flow-form-collapsible">
'.$sp.'		<button data-role="submit"
'.$sp.'		        class="mw-ui-button mw-ui-constructive"
'.$sp.'		>'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</button>
'.$sp.''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()), '		').'		<small class="flow-terms-of-use plainlinks">'.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'</small>
'.$sp.'	</div>
'.$sp.'</form>
'.$sp.'' : '').'';},
'flow_topic' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-topic flow-load-interactive'.((LR::ifvar($cx, ((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null), false)) ? ' flow-topic-moderatestate-'.LR::encq($cx, ((isset($in['moderateState']) && is_array($in)) ? $in['moderateState'] : null)).' ' : '').''.((LR::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null), false)) ? ' flow-topic-moderated ' : '').''.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? 'flow-element-collapsible flow-element-collapsed' : '').'"
'.$sp.'     id="flow-topic-'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'     data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'     data-flow-load-handler="topic"
'.$sp.'     data-flow-toc-scroll-target=".flow-topic-titlebar"
'.$sp.'     data-flow-topic-timestamp-updated="'.LR::encq($cx, ((isset($in['last_updated']) && is_array($in)) ? $in['last_updated'] : null)).'"
'.$sp.'>
'.$sp.''.LR::p($cx, 'flow_topic_titlebar', array(array($in),array()), '	').'
'.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['posts']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['posts'] : null), false)) ? ''.LR::sec($cx, ((isset($in['replies']) && is_array($in)) ? $in['replies'] : null), null, $in, true, function($cx, $in)use($sp){return ''.LR::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in)use($sp){return '				<!-- eachPost topic -->
'.$sp.'				'.LR::encq($cx, ((isset($in['post']) && is_array($in)) ? $in['post'] : null)).'
'.$sp.'';}).'';}).'' : '').'
'.$sp.''.((LR::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null), false)) ? ''.LR::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['submitted']['postId']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_reply_form', array(array($in),array()), '			').'';}, function($cx, $in)use($sp){return ''.LR::hbch($cx, 'progressiveEnhancement', array(array(),array('type'=>'replace','target'=>'~ a')), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_reply_form', array(array($in),array()), '				').'';}).'			<a href="'.LR::encq($cx, ((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null)).'"
'.$sp.'			   title="'.LR::encq($cx, ((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null)).'"
'.$sp.'			   class="flow-ui-input-replacement-anchor mw-ui-input"
'.$sp.'			>'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>
'.$sp.'';}).'' : ''.LR::hbch($cx, 'progressiveEnhancement', array(array(),array('type'=>'insert')), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_reply_form', array(array($in),array()), '			').'';}).'').'</div>
';},
'flow_topiclist_loop' => function ($cx, $in, $sp) {return ''.$sp.''.LR::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), null, $in, true, function($cx, $in)use($sp){return ''.LR::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_topic', array(array($in),array()), '		').'';}).'';}).'';},
'flow_load_more' => function ($cx, $in, $sp) {return ''.$sp.''.((LR::ifvar($cx, ((isset($in['loadMoreObject']) && is_array($in)) ? $in['loadMoreObject'] : null), false)) ? '	<div class="flow-load-more">
'.$sp.'		<div class="flow-error-container">
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<div class="flow-ui-loading"><div class="mw-ui-icon mw-ui-icon-before mw-ui-icon-advanced"></div></div>
'.$sp.'
'.$sp.'		<a data-flow-interactive-handler="apiRequest"
'.$sp.'		   data-flow-api-handler="'.LR::encq($cx, ((isset($in['loadMoreApiHandler']) && is_array($in)) ? $in['loadMoreApiHandler'] : null)).'"
'.$sp.'		   data-flow-api-target="< .flow-load-more"
'.$sp.'		   data-flow-load-handler="loadMore"
'.$sp.'		   data-flow-scroll-target="'.LR::encq($cx, ((isset($in['loadMoreTarget']) && is_array($in)) ? $in['loadMoreTarget'] : null)).'"
'.$sp.'		   data-flow-scroll-container="'.LR::encq($cx, ((isset($in['loadMoreContainer']) && is_array($in)) ? $in['loadMoreContainer'] : null)).'"
'.$sp.'		   data-flow-template="'.LR::encq($cx, ((isset($in['loadMoreTemplate']) && is_array($in)) ? $in['loadMoreTemplate'] : null)).'"
'.$sp.'		   href="'.LR::encq($cx, ((isset($in['loadMoreObject']['url']) && is_array($in['loadMoreObject'])) ? $in['loadMoreObject']['url'] : null)).'"
'.$sp.'		   title="'.LR::encq($cx, ((isset($in['loadMoreObject']['title']) && is_array($in['loadMoreObject'])) ? $in['loadMoreObject']['title'] : null)).'"
'.$sp.'		   class="mw-ui-button mw-ui-progressive flow-load-interactive flow-ui-fallback-element"><span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-article-invert"></span> '.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>
'.$sp.'	</div>
'.$sp.'' : '	<div class="flow-no-more">
'.$sp.'		'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
'.$sp.'	</div>
'.$sp.'	<div class="flow-bottom-spacer"></div>
'.$sp.'').'';});
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'lambda' => false,
            'mustlok' => false,
            'mustlam' => false,
            'echo' => false,
            'partnc' => false,
            'knohlp' => false,
            'debug' => isset($options['debug']) ? $options['debug'] : 1,
        ),
        'constants' => array(),
        'helpers' => isset($options['helpers']) ? array_merge($helpers, $options['helpers']) : $helpers,
        'partials' => isset($options['partials']) ? array_merge($partials, $options['partials']) : $partials,
        'scopes' => array(),
        'sp_vars' => isset($options['data']) ? array_merge(array('root' => $in), $options['data']) : array('root' => $in),
        'blparam' => array(),
        'runtime' => '\LightnCandy\Runtime',
    );
    
    return ''.LR::p($cx, 'flow_board_navigation', array(array($in),array())).'
<div class="flow-board" data-flow-sortby="'.LR::encq($cx, ((isset($in['sortby']) && is_array($in)) ? $in['sortby'] : null)).'">
	<div class="flow-newtopic-container">
		<div class="flow-nojs">
			<a class="mw-ui-input mw-ui-input-large flow-ui-input-replacement-anchor"
				href="'.LR::encq($cx, ((isset($in['links']['newtopic']) && is_array($in['links'])) ? $in['links']['newtopic'] : null)).'">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>
		</div>

		<div class="flow-js">
'.LR::p($cx, 'flow_newtopic_form', array(array($in),array('isOnFlowBoard'=>true)), '			').'		</div>
	</div>

	<div class="flow-topics">
'.LR::p($cx, 'flow_topiclist_loop', array(array($in),array()), '		').'
'.LR::p($cx, 'flow_load_more', array(array($in),array('loadMoreApiHandler'=>'loadMoreTopics','loadMoreTarget'=>'window','loadMoreContainer'=>'< .flow-topics','loadMoreTemplate'=>'flow_topiclist_loop.partial','loadMoreObject'=>((isset($in['links']['pagination']['fwd']) && is_array($in['links']['pagination'])) ? $in['links']['pagination']['fwd'] : null))), '		').'	</div>
</div>
';
};