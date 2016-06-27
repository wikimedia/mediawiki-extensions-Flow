<?php use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
            'ifCond' => 'Flow\TemplateHelper::ifCond',
            'tooltip' => 'Flow\TemplateHelper::tooltip',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
);
    $partials = array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, ((isset($in['html']) && is_array($in)) ? $in['html'] : null)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_moderate_post' => function ($cx, $in, $sp) {return ''.$sp.'<form method="POST" action="'.LR::encq($cx, ((isset($in['moderationAction']) && is_array($in)) ? $in['moderationAction'] : null)).'">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '	').'	<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, ((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null)).'" />
'.$sp.'	<input name="topic_reason"
'.$sp.'	          type="text"
'.$sp.'	          size="45"
'.$sp.'	          required
'.$sp.'	          data-flow-expandable="true"
'.$sp.'	          class="mw-ui-input"
'.$sp.'	          data-role="content"
'.$sp.'	          placeholder="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'	          autofocus'.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']['reason']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['reason'] : null), false)) ? 'value="'.LR::encq($cx, ((isset($cx['sp_vars']['root']['submitted']['reason']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['reason'] : null)).'"' : '').'>
'.$sp.'	<div class="flow-form-actions flow-form-collapsible">
'.$sp.'		<button data-flow-interactive-handler="apiRequest"
'.$sp.'		        data-flow-api-handler="moderatePost"
'.$sp.'		        class="mw-ui-button mw-ui-constructive"
'.$sp.'		        data-role="submit">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</button>
'.$sp.'		<a data-flow-interactive-handler="cancelForm"
'.$sp.'		   class="mw-ui-button mw-ui-destructive mw-ui-quiet"
'.$sp.'		   href="'.LR::encq($cx, ((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null)).'"
'.$sp.'		   title="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a>
'.$sp.'	</div>
'.$sp.'</form>
';},
'flow_post_author' => function ($cx, $in, $sp) {return ''.$sp.'<span class="flow-author">
'.$sp.''.((LR::ifvar($cx, ((isset($in['links']) && is_array($in)) ? $in['links'] : null), false)) ? ''.((LR::ifvar($cx, ((isset($in['links']['userpage']) && is_array($in['links'])) ? $in['links']['userpage'] : null), false)) ? '			<a href="'.LR::encq($cx, ((isset($in['links']['userpage']['url']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['url'] : null)).'"
'.$sp.'			   '.((!LR::ifvar($cx, ((isset($in['name']) && is_array($in)) ? $in['name'] : null), false)) ? 'title="'.LR::encq($cx, ((isset($in['links']['userpage']['title']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['title'] : null)).'"' : '').'
'.$sp.'			   class="'.((!LR::ifvar($cx, ((isset($in['links']['userpage']['exists']) && is_array($in['links']['userpage'])) ? $in['links']['userpage']['exists'] : null), false)) ? 'new ' : '').'mw-userlink">
'.$sp.'' : '').''.((LR::ifvar($cx, ((isset($in['name']) && is_array($in)) ? $in['name'] : null), false)) ? '<bdi>'.LR::encq($cx, ((isset($in['name']) && is_array($in)) ? $in['name'] : null)).'</bdi>' : ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'').''.((LR::ifvar($cx, ((isset($in['links']['userpage']) && is_array($in['links'])) ? $in['links']['userpage'] : null), false)) ? '</a>' : '').'<span class="mw-usertoollinks flow-pipelist">
'.$sp.'			('.((LR::ifvar($cx, ((isset($in['links']['talk']) && is_array($in['links'])) ? $in['links']['talk'] : null), false)) ? '<span><a href="'.LR::encq($cx, ((isset($in['links']['talk']['url']) && is_array($in['links']['talk'])) ? $in['links']['talk']['url'] : null)).'"
'.$sp.'				    class="'.((!LR::ifvar($cx, ((isset($in['links']['talk']['exists']) && is_array($in['links']['talk'])) ? $in['links']['talk']['exists'] : null), false)) ? 'new ' : '').'"
'.$sp.'				    title="'.LR::encq($cx, ((isset($in['links']['talk']['title']) && is_array($in['links']['talk'])) ? $in['links']['talk']['title'] : null)).'">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a></span>' : '').''.((LR::ifvar($cx, ((isset($in['links']['contribs']) && is_array($in['links'])) ? $in['links']['contribs'] : null), false)) ? '<span><a href="'.LR::encq($cx, ((isset($in['links']['contribs']['url']) && is_array($in['links']['contribs'])) ? $in['links']['contribs']['url'] : null)).'" title="'.LR::encq($cx, ((isset($in['links']['contribs']['title']) && is_array($in['links']['contribs'])) ? $in['links']['contribs']['title'] : null)).'">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a></span>' : '').''.((LR::ifvar($cx, ((isset($in['links']['block']) && is_array($in['links'])) ? $in['links']['block'] : null), false)) ? '<span><a class="'.((!LR::ifvar($cx, ((isset($in['links']['block']['exists']) && is_array($in['links']['block'])) ? $in['links']['block']['exists'] : null), false)) ? 'new ' : '').'"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['block']['url']) && is_array($in['links']['block'])) ? $in['links']['block']['url'] : null)).'"
'.$sp.'				   title="'.LR::encq($cx, ((isset($in['links']['block']['title']) && is_array($in['links']['block'])) ? $in['links']['block']['title'] : null)).'">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</a></span>' : '').')
'.$sp.'		</span>
'.$sp.'' : '').'</span>
';},
'flow_post_moderation_state' => function ($cx, $in, $sp) {return ''.$sp.'<span class="plainlinks">'.((LR::ifvar($cx, ((isset($in['replyToId']) && is_array($in)) ? $in['replyToId'] : null), false)) ? ''.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'' : ''.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'').'</span>
';},
'flow_post_meta_actions' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-post-meta">
'.$sp.'	<span class="flow-post-meta-actions">
'.$sp.''.((LR::ifvar($cx, ((isset($in['actions']['reply']) && is_array($in['actions'])) ? $in['actions']['reply'] : null), false)) ? '			<a href="'.LR::encq($cx, ((isset($in['actions']['reply']['url']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['url'] : null)).'"
'.$sp.'			   title="'.LR::encq($cx, ((isset($in['actions']['reply']['title']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['title'] : null)).'"
'.$sp.'			   class="mw-ui-anchor mw-ui-progressive mw-ui-quiet flow-reply-link"
'.$sp.'			   data-flow-eventlog-schema="FlowReplies"
'.$sp.'			   data-flow-eventlog-action="initiate"
'.$sp.'			   data-flow-eventlog-entrypoint="reply-post"
'.$sp.'			   data-flow-eventlog-forward="
'.$sp.'				   < .flow-post:not([data-flow-post-max-depth=\'1\']) .flow-reply-form [data-role=\'cancel\'],
'.$sp.'				   < .flow-post:not([data-flow-post-max-depth=\'1\']) .flow-reply-form [data-role=\'submit\']
'.$sp.'			   "
'.$sp.'			>'.LR::encq($cx, ((isset($in['actions']['reply']['text']) && is_array($in['actions']['reply'])) ? $in['actions']['reply']['text'] : null)).'</a>
'.$sp.'' : '').''.((LR::ifvar($cx, ((isset($in['actions']['thank']) && is_array($in['actions'])) ? $in['actions']['thank'] : null), false)) ? '			<a class="mw-ui-anchor mw-ui-constructive mw-ui-quiet mw-thanks-flow-thank-link"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['thank']['url']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['url'] : null)).'"
'.$sp.'			   title="'.LR::encq($cx, ((isset($in['actions']['thank']['title']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['title'] : null)).'">'.LR::encq($cx, ((isset($in['actions']['thank']['text']) && is_array($in['actions']['thank'])) ? $in['actions']['thank']['text'] : null)).'</a>
'.$sp.'' : '').'	</span>
'.$sp.'
'.$sp.'	<span class="flow-post-timestamp">
'.$sp.''.((LR::ifvar($cx, ((isset($in['isOriginalContent']) && is_array($in)) ? $in['isOriginalContent'] : null), false)) ? '			<a href="'.LR::encq($cx, ((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null)).'" class="flow-timestamp-anchor">
'.$sp.'				'.LR::encq($cx, ((isset($in['uuidTimestamp']) && is_array($in)) ? $in['uuidTimestamp'] : null)).'
'.$sp.'			</a>
'.$sp.'' : '			<span>
'.$sp.''.LR::hbch($cx, 'ifCond', array(array(((isset($in['creator']['name']) && is_array($in['creator'])) ? $in['creator']['name'] : null),'===',((isset($in['lastEditUser']['name']) && is_array($in['lastEditUser'])) ? $in['lastEditUser']['name'] : null)),array()), $in, false, function($cx, $in)use($sp){return '					'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
'.$sp.'';}, function($cx, $in)use($sp){return '					'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
'.$sp.'';}).'			</span>
'.$sp.'			<a href="'.LR::encq($cx, ((isset($in['links']['diff-prev']['url']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['url'] : null)).'" class="flow-timestamp-anchor">'.LR::encq($cx, ((isset($in['uuidTimestamp']) && is_array($in)) ? $in['uuidTimestamp'] : null)).'</a>
'.$sp.'').'	</span>
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
'flow_post_actions' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-menu flow-menu-hoverable">
'.$sp.'	<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ellipsis"></span></a></div>
'.$sp.'	<ul class="mw-ui-button-container flow-list">
'.$sp.''.LR::p($cx, 'flow_moderation_actions_list', array(array($in),array('moderationType'=>'post','moderationTarget'=>'post','moderationTemplate'=>'post','moderationContainerClass'=>'flow-menu','moderationMwUiClass'=>'mw-ui-button','moderationIcons'=>true)), '		').'	</ul>
'.$sp.'</div>
';},
'flow_post_inner' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-post-main">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '	').'
'.$sp.''.LR::wi($cx, ((isset($in['creator']) && is_array($in)) ? $in['creator'] : null), null, $in, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_post_author', array(array($in),array()), '		').'';}).'
'.$sp.''.((LR::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null), false)) ? '		<div class="flow-moderated-post-content">
'.$sp.''.LR::p($cx, 'flow_post_moderation_state', array(array($in),array()), '			').'		</div>
'.$sp.'' : '').'
'.$sp.'	<div class="flow-post-content">
'.$sp.'		'.LR::encq($cx, ((isset($in['escapeContent']) && is_array($in)) ? $in['escapeContent'] : null)).'
'.$sp.'	</div>
'.$sp.'
'.$sp.''.LR::p($cx, 'flow_post_meta_actions', array(array($in),array()), '	').''.LR::p($cx, 'flow_post_actions', array(array($in),array()), '	').'</div>
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
'flow_edit_post' => function ($cx, $in, $sp) {return ''.$sp.'<form class="flow-edit-post-form"
'.$sp.'      method="POST"
'.$sp.'      action="'.LR::encq($cx, ((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null)).'"
'.$sp.'>
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '	').'	<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, ((isset($cx['sp_vars']['root']['rootBlock']['editToken']) && is_array($cx['sp_vars']['root']['rootBlock'])) ? $cx['sp_vars']['root']['rootBlock']['editToken'] : null)).'" />
'.$sp.'	<input type="hidden" name="topic_prev_revision" value="'.LR::encq($cx, ((isset($in['revisionId']) && is_array($in)) ? $in['revisionId'] : null)).'" />
'.$sp.''.LR::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_anon_warning', array(array($in),array()), '		').'';}).'
'.$sp.'	<div class="flow-editor">
'.$sp.'		<textarea name="topic_content" class="mw-ui-input flow-form-collapsible" data-role="content">'.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['rootBlock']['submitted']['content']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['content'] : null), false)) ? ''.LR::encq($cx, ((isset($cx['sp_vars']['root']['rootBlock']['submitted']['content']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['content'] : null)).'' : ''.LR::encq($cx, ((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)).'').'</textarea>
'.$sp.'	</div>
'.$sp.'
'.$sp.'	<div class="flow-form-actions flow-form-collapsible">
'.$sp.'		<button class="mw-ui-button mw-ui-constructive"
'.$sp.'		        data-flow-api-handler="submitEditPost">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</button>
'.$sp.''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()), '		').'		<small class="flow-terms-of-use plainlinks">'.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'</small>
'.$sp.'	</div>
'.$sp.'</form>
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
'flow_post_replies' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-replies">
'.$sp.''.LR::sec($cx, ((isset($in['replies']) && is_array($in)) ? $in['replies'] : null), null, $in, true, function($cx, $in)use($sp){return ''.LR::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']['rootBlock']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['rootBlock'] : null),$in),array()), $in, false, function($cx, $in)use($sp){return '			<!-- eachPost nested replies -->
'.$sp.'			'.LR::encq($cx, ((isset($in['post']) && is_array($in)) ? $in['post'] : null)).'
'.$sp.'';}).'';}).''.LR::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['postId']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in)use($sp){return ''.LR::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['action']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['action'] : null),'===','reply'),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_reply_form', array(array($in),array()), '			').'';}).'';}).'</div>
';},
'flow_post_partial' => function ($cx, $in, $sp) {return ''.$sp.''.LR::wi($cx, ((isset($in['revision']) && is_array($in)) ? $in['revision'] : null), null, $in, function($cx, $in)use($sp){return '	<div id="flow-post-'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'	     class="flow-post'.((LR::ifvar($cx, ((isset($in['isMaxThreadingDepth']) && is_array($in)) ? $in['isMaxThreadingDepth'] : null), false)) ? ' flow-post-max-depth' : '').'"
'.$sp.'	     data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'	>
'.$sp.''.((LR::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null), false)) ? ''.LR::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['showPostId']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['showPostId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_post_inner', array(array($in),array()), '				').'';}, function($cx, $in)use($sp){return '				<div class="flow-post-main flow-post-moderated">
'.$sp.'					<span class="flow-moderated-post-content">
'.$sp.''.LR::p($cx, 'flow_post_moderation_state', array(array($in),array()), '						').'					</span>
'.$sp.'				</div>
'.$sp.'';}).'' : ''.LR::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['action']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['action'] : null),'===','edit-post'),array()), $in, false, function($cx, $in)use($sp){return ''.LR::hbch($cx, 'ifCond', array(array(((isset($cx['sp_vars']['root']['rootBlock']['submitted']['postId']) && is_array($cx['sp_vars']['root']['rootBlock']['submitted'])) ? $cx['sp_vars']['root']['rootBlock']['submitted']['postId'] : null),'===',((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_edit_post', array(array($in),array()), '					').'';}, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_post_inner', array(array($in),array()), '					').'';}).'';}, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_post_inner', array(array($in),array()), '				').'';}).'').'
'.$sp.''.LR::p($cx, 'flow_post_replies', array(array($in),array()), '		').'	</div>
'.$sp.'';}).'';});
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
    
    return '<div class="flow-board">
'.LR::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),((isset($cx['sp_vars']['root']['submitted']['postId']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['postId'] : null)),array()), $in, false, function($cx, $in) {return ''.LR::p($cx, 'flow_moderate_post', array(array($in),array()), '		').''.LR::p($cx, 'flow_post_partial', array(array($in),array()), '		').'';}).'</div>
';
};
?>