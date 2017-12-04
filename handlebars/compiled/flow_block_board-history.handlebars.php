<?php
use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in = null, $options = null) {
    $helpers = array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
            'historyDescription' => 'Flow\TemplateHelper::historyDescription',
            'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
            'concat' => 'Flow\TemplateHelper::concat',
            'ifCond' => 'Flow\TemplateHelper::ifCond',
);
    $partials = array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, (isset($cx['sp_vars']['root']['errors']) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, (isset($cx['sp_vars']['root']['errors']) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, LR::hbch($cx, 'html', array(array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)),array()), 'encq', $in)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_moderation_actions_list' => function ($cx, $in, $sp) {return ''.$sp.'<section>'.LR::hbbch($cx, 'ifCond', array(array(((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'===','topic'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-edit-title-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['edit']) && is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-edit-title'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']) && is_array($in['links']) && isset($in['links']['topic-history'])) ? $in['links']['topic-history'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['topic-history']) && is_array($in['links']['topic-history']) && isset($in['links']['topic-history']['url'])) ? $in['links']['topic-history']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-clock"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-history'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']) && is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['topic']) && is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-view'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['summarize'])) ? $in['actions']['summarize'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-summarize-topic-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['summarize']) && is_array($in['actions']['summarize']) && isset($in['actions']['summarize']['url'])) ? $in['actions']['summarize']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-stripeToC mw-ui-icon-stripeToC-progressive-hover"></span> ' : '').''.((LR::ifvar($cx, ((isset($in['summary']['revision']['content']) && is_array($in['summary']['revision']['content']) && isset($in['summary']['revision']['content']['content'])) ? $in['summary']['revision']['content']['content'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-resummarize-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-topic-action-summarize-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'').'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').'';}).''.LR::hbbch($cx, 'ifCond', array(array(((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-role="lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)).'"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['lock']) && is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['unlock'])) ? $in['actions']['unlock'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-role="unlock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)).'"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['unlock']) && is_array($in['actions']['unlock']) && isset($in['actions']['unlock']['url'])) ? $in['actions']['unlock']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation mw-ui-icon-ongoingConversation-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').'';}, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)).'"
'.$sp.'				   data-role="lock"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['lock']) && is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['unlock'])) ? $in['actions']['unlock'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-topicmenu-lock"
'.$sp.'				   data-flow-id="'.LR::encq($cx, ((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)).'"
'.$sp.'				   data-role="unlock"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['unlock']) && is_array($in['actions']['unlock']) && isset($in['actions']['unlock']['url'])) ? $in['actions']['unlock']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation mw-ui-icon-ongoingConversation-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').'';}).''.LR::hbbch($cx, 'ifCond', array(array(((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'===','post'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null), false)) ? '<li>
'.$sp.'				<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-progressive mw-ui-quiet mw-ui-hovericon flow-ui-edit-post-link"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['edit']) && is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null)).'"
'.$sp.'				>'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-post-action-edit-post'),array()), 'encq', $in)).'</a>
'.$sp.'			</li>' : '').''.((LR::ifvar($cx, ((isset($in['links']) && is_array($in['links']) && isset($in['links']['post'])) ? $in['links']['post'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['links']['post']) && is_array($in['links']['post']) && isset($in['links']['post']['url'])) ? $in['links']['post']['url'] : null)).'">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-post-action-view'),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').'';}).'</section>
'.$sp.'
'.$sp.'<section>'.LR::hbbch($cx, 'ifCond', array(array(((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['undo'])) ? $in['actions']['undo'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'				   href="'.LR::encq($cx, ((isset($in['actions']['undo']) && is_array($in['actions']['undo']) && isset($in['actions']['undo']['url'])) ? $in['actions']['undo']['url'] : null)).'"
'.$sp.'				>'.LR::encq($cx, ((isset($in['actions']['undo']) && is_array($in['actions']['undo']) && isset($in['actions']['undo']['title'])) ? $in['actions']['undo']['title'] : null)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').'';}).''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['hide']) && is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="hide">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-hide-',((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['unhide'])) ? $in['actions']['unhide'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-quiet"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['unhide']) && is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['url'])) ? $in['actions']['unhide']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="unhide">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-unhide-',((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['delete']) && is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="delete">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-trash mw-ui-icon-trash-destructive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-delete-',((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['undelete'])) ? $in['actions']['undelete'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['undelete']) && is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['url'])) ? $in['actions']['undelete']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="undelete">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-trash mw-ui-icon-trash-destructive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-undelete-',((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['suppress']) && is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="suppress">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-suppress-',((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').''.((LR::ifvar($cx, ((isset($in['actions']) && is_array($in['actions']) && isset($in['actions']['unsuppress'])) ? $in['actions']['unsuppress'] : null), false)) ? '<li>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<a class="'.LR::encq($cx, ((is_array($in) && isset($in['moderationMwUiClass'])) ? $in['moderationMwUiClass'] : null)).' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.LR::encq($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['url'])) ? $in['actions']['unsuppress']['url'] : null)).'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.LR::encq($cx, ((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)).'.partial"
'.$sp.'			   data-role="unsuppress">'.((LR::ifvar($cx, ((is_array($in) && isset($in['moderationIcons'])) ? $in['moderationIcons'] : null), false)) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(LR::hbch($cx, 'concat', array(array('flow-',((is_array($in) && isset($in['moderationType'])) ? $in['moderationType'] : null),'-action-unsuppress-',((is_array($in) && isset($in['moderationTemplate'])) ? $in['moderationTemplate'] : null)),array()), 'raw', $in)),array()), 'encq', $in)).'</a>'.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'</li>' : '').'</section>
';},
'flow_history_line' => function ($cx, $in, $sp) {return ''.$sp.'<span class="flow-pipelist">
'.$sp.'	('.LR::encq($cx, ((is_array($in) && isset($in['noop'])) ? $in['noop'] : null)).'<span>'.((LR::ifvar($cx, ((isset($in['links']) && is_array($in['links']) && isset($in['links']['diff-cur'])) ? $in['links']['diff-cur'] : null), false)) ? '<a href="'.LR::encq($cx, ((isset($in['links']['diff-cur']) && is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['url'])) ? $in['links']['diff-cur']['url'] : null)).'" title="'.LR::encq($cx, ((isset($in['links']['diff-cur']) && is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['title'])) ? $in['links']['diff-cur']['title'] : null)).'">'.LR::encq($cx, ((isset($in['links']['diff-cur']) && is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['text'])) ? $in['links']['diff-cur']['text'] : null)).'</a>' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('cur'),array()), 'encq', $in)).'').'</span>
'.$sp.'	<span>
'.$sp.''.((LR::ifvar($cx, ((isset($in['links']) && is_array($in['links']) && isset($in['links']['diff-prev'])) ? $in['links']['diff-prev'] : null), false)) ? '			<a href="'.LR::encq($cx, ((isset($in['links']['diff-prev']) && is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['url'])) ? $in['links']['diff-prev']['url'] : null)).'" title="'.LR::encq($cx, ((isset($in['links']['diff-prev']) && is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null)).'">'.LR::encq($cx, ((isset($in['links']['diff-prev']) && is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['text'])) ? $in['links']['diff-prev']['text'] : null)).'</a>' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('last'),array()), 'encq', $in)).'').'</span>'.((LR::ifvar($cx, ((isset($in['links']) && is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null), false)) ? '		<span><a href="'.LR::encq($cx, ((isset($in['links']['topic']) && is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null)).'" title="'.LR::encq($cx, ((isset($in['links']['topic']) && is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null)).'">'.LR::encq($cx, ((isset($in['links']['topic']) && is_array($in['links']['topic']) && isset($in['links']['topic']['text'])) ? $in['links']['topic']['text'] : null)).'</a></span>' : '').')
'.$sp.'</span>
'.$sp.'
'.$sp.''.LR::encq($cx, LR::hbch($cx, 'historyTimestamp', array(array($in),array()), 'encq', $in)).'
'.$sp.'
'.$sp.'<span class="mw-changeslist-separator">. .</span>
'.$sp.''.LR::encq($cx, LR::hbch($cx, 'historyDescription', array(array($in),array()), 'encq', $in)).'
'.$sp.'
'.$sp.''.((LR::ifvar($cx, ((is_array($in) && isset($in['size'])) ? $in['size'] : null), false)) ? '	<span class="mw-changeslist-separator">. .</span>
'.$sp.'	'.LR::encq($cx, LR::hbch($cx, 'showCharacterDifference', array(array(((isset($in['size']) && is_array($in['size']) && isset($in['size']['old'])) ? $in['size']['old'] : null),((isset($in['size']) && is_array($in['size']) && isset($in['size']['new'])) ? $in['size']['new'] : null)),array()), 'encq', $in)).'
'.$sp.'' : '').'
'.$sp.'<ul class="flow-history-moderation-menu">
'.$sp.''.LR::p($cx, 'flow_moderation_actions_list', array(array($in),array('moderationType'=>'history','moderationTarget'=>'post','moderationTemplate'=>'post','moderationMwUiClass'=>'mw-ui-anchor','moderationIcons'=>false)),0, '	').'</ul>
';});
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'jslen' => false,
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
        'partialid' => 0,
        'runtime' => '\LightnCandy\Runtime',
    );
    
    return '<div class="flow-board-history">
'.LR::p($cx, 'flow_errors', array(array($in),array()),0, '	').'
	'.((LR::ifvar($cx, ((is_array($in) && isset($in['navbar'])) ? $in['navbar'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'html', array(array(((is_array($in) && isset($in['navbar'])) ? $in['navbar'] : null)),array()), 'encq', $in)).'' : '').'

	<ul>
'.LR::sec($cx, ((is_array($in) && isset($in['revisions'])) ? $in['revisions'] : null), null, $in, true, function($cx, $in) {return '			<li>'.LR::p($cx, 'flow_history_line', array(array($in),array()),0).'</li>
';}).'	</ul>

	'.((LR::ifvar($cx, ((is_array($in) && isset($in['navbar'])) ? $in['navbar'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'html', array(array(((is_array($in) && isset($in['navbar'])) ? $in['navbar'] : null)),array()), 'encq', $in)).'' : '').'
</div>
';
};