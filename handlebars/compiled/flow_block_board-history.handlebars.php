<?php use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
            'historyDescription' => 'Flow\TemplateHelper::historyDescription',
            'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
            'concat' => 'Flow\TemplateHelper::concat',
            'ifCond' => 'Flow\TemplateHelper::ifCond',
);
    $partials = array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, LR::hbch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq', $in)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_moderation_actions_list' => function ($cx, $in, $sp) {return ''.$sp.'<section>'.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','topic'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-edit-title-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-edit-title'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']['topic-history']) && is_array($in['links'])) ? $in['links']['topic-history'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-clock"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-history'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-view'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['summarize']) && is_array($in['actions'])) ? $in['actions']['summarize'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-summarize-topic-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['summarize']['url']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-stripeToC mw-ui-icon-stripeToC-progressive-hover"></span> ' : '').''.((LR::ifvar($cx, ((isset($in['summary']['revision']['content']['content']) && is_array($in['summary']['revision']['content'])) ? $in['summary']['revision']['content']['content'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-resummarize-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-summarize-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'').'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}).''.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-role="lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-role="unlock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation mw-ui-icon-ongoingConversation-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'				   data-role="lock"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((isset($in['postId']) && is_array($in)) ? $in['postId'] : null)).'"
'.$sp.'				   data-role="unlock"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation mw-ui-icon-ongoingConversation-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}).''.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','post'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null), false)) ? '<li>
'.$sp.'				<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-edit-post-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null)).'"
'.$sp.'				>'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-post-action-edit-post'),array()), 'encq', $in)).'</a>
'.$sp.'			</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']['post']) && is_array($in['links'])) ? $in['links']['post'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['post']['url']) && is_array($in['links']['post'])) ? $in['links']['post']['url'] : null)).'">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-post-action-view'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}).'</section>
'.$sp.'
'.$sp.'<section>'.LR::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']['undo']) && is_array($in['actions'])) ? $in['actions']['undo'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['undo']['url']) && is_array($in['actions']['undo'])) ? $in['actions']['undo']['url'] : null)).'"
'.$sp.'				>'.LR::encq($cx, ((isset($in['actions']['undo']['title']) && is_array($in['actions']['undo'])) ? $in['actions']['undo']['title'] : null)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'';}).''.((LR::ifvar($cx, ((isset($in['actions']['hide']) && is_array($in['actions'])) ? $in['actions']['hide'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['hide']['url']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="hide">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-hide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="unhide">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unhide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['delete']) && is_array($in['actions'])) ? $in['actions']['delete'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['delete']['url']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="delete">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-remove mw-ui-icon-remove-destructive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-delete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['undelete']) && is_array($in['actions'])) ? $in['actions']['undelete'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['undelete']['url']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="undelete">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-remove mw-ui-icon-remove-destructive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-undelete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['suppress']) && is_array($in['actions'])) ? $in['actions']['suppress'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['suppress']['url']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="suppress">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-suppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions'])) ? $in['actions']['unsuppress'] : null), false)) ? '<li>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['unsuppress']['url']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="unsuppress">'.((LR::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unsuppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'</li>' : '').'</section>
';},
'flow_history_line' => function ($cx, $in, $sp) {return ''.$sp.'<span class="flow-pipelist">
'.$sp.'	('.LR::encq($cx, ((isset($in['noop']) && is_array($in)) ? $in['noop'] : null)).'<span>'.((LR::ifvar($cx, ((isset($in['links']['diff-cur']) && is_array($in['links'])) ? $in['links']['diff-cur'] : null), false)) ? '<a href="'.LR::encq($cx, ((isset($in['links']['diff-cur']['url']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['url'] : null)).'" title="'.LR::encq($cx, ((isset($in['links']['diff-cur']['title']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['title'] : null)).'">'.LR::encq($cx, ((isset($in['links']['diff-cur']['text']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['text'] : null)).'</a>' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('cur'),array()), 'encq', $in)).'').'</span>
'.$sp.'	<span>
'.$sp.''.((LR::ifvar($cx, ((isset($in['links']['diff-prev']) && is_array($in['links'])) ? $in['links']['diff-prev'] : null), false)) ? '			<a href="'.LR::encq($cx, ((isset($in['links']['diff-prev']['url']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['url'] : null)).'" title="'.LR::encq($cx, ((isset($in['links']['diff-prev']['title']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['title'] : null)).'">'.LR::encq($cx, ((isset($in['links']['diff-prev']['text']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['text'] : null)).'</a>' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('last'),array()), 'encq', $in)).'').'</span>'.((LR::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null), false)) ? '		<span><a href="'.LR::encq($cx, ((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null)).'" title="'.LR::encq($cx, ((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null)).'">'.LR::encq($cx, ((isset($in['links']['topic']['text']) && is_array($in['links']['topic'])) ? $in['links']['topic']['text'] : null)).'</a></span>' : '').')
'.$sp.'</span>
'.$sp.'
'.$sp.''.LR::encq($cx, LR::hbch($cx, 'historyTimestamp', array(array($in),array()), 'encq', $in)).'
'.$sp.'
'.$sp.'<span class="mw-changeslist-separator">. .</span>
'.$sp.''.LR::encq($cx, LR::hbch($cx, 'historyDescription', array(array($in),array()), 'encq', $in)).'
'.$sp.'
'.$sp.''.((LR::ifvar($cx, ((isset($in['size']) && is_array($in)) ? $in['size'] : null), false)) ? '	<span class="mw-changeslist-separator">. .</span>
'.$sp.'	'.LR::encq($cx, LR::hbch($cx, 'showCharacterDifference', array(array(((isset($in['size']['old']) && is_array($in['size'])) ? $in['size']['old'] : null),((isset($in['size']['new']) && is_array($in['size'])) ? $in['size']['new'] : null)),array()), 'encq', $in)).'
'.$sp.'' : '').'
'.$sp.'<ul class="flow-history-moderation-menu">
'.$sp.''.LR::p($cx, 'flow_moderation_actions_list', array(array($in),array('moderationType'=>'history','moderationTarget'=>'post','moderationTemplate'=>'post','moderationMwUiClass'=>'mw-ui-anchor','moderationIcons'=>false)), '	').'</ul>
';});
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
    
    return '<div class="flow-board-history">
'.LR::p($cx, 'flow_errors', array(array($in),array()), '	').'
	'.((LR::ifvar($cx, ((isset($in['navbar']) && is_array($in)) ? $in['navbar'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'html', array(array(((isset($in['navbar']) && is_array($in)) ? $in['navbar'] : null)),array()), 'encq', $in)).'' : '').'

	<ul>
'.LR::sec($cx, ((isset($in['revisions']) && is_array($in)) ? $in['revisions'] : null), null, $in, true, function($cx, $in) {return '			<li>'.LR::p($cx, 'flow_history_line', array(array($in),array())).'</li>
';}).'	</ul>

	'.((LR::ifvar($cx, ((isset($in['navbar']) && is_array($in)) ? $in['navbar'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'html', array(array(((isset($in['navbar']) && is_array($in)) ? $in['navbar'] : null)),array()), 'encq', $in)).'' : '').'
</div>
';
};
?>