<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' => array(),
        'helpers' => array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
            'historyDescription' => 'Flow\TemplateHelper::historyDescription',
            'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
            'concat' => 'Flow\TemplateHelper::concat',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(            'ifCond' => 'Flow\TemplateHelper::ifCond',
),
        'partials' => array('flow_moderation_actions_list' => function ($cx, $in, $sp) {return ''.$sp.'<section>'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','topic'),array()), $in, false, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '<li class="flow-js">'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-progressive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="activateEditTitle"
'.$sp.'				   data-flow-api-target="< .flow-topic-titlebar"
'.$sp.'				>'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-edit-title'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['topic-history']) && is_array($in['links'])) ? $in['links']['topic-history'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'				   href="'.htmlentities((string)((isset($in['links']['topic-history']['url']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['links']['topic-history']['title']) && is_array($in['links']['topic-history'])) ? $in['links']['topic-history']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-clock"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-history'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'				   href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-view'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['summarize']) && is_array($in['actions'])) ? $in['actions']['summarize'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-progressive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="activateSummarizeTopic"
'.$sp.'				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['summarize']['url']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['summarize']['title']) && is_array($in['actions']['summarize'])) ? $in['actions']['summarize']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-stripeToC mw-ui-icon-stripeToC-progressive-hover"></span> ' : '').''.((LCRun3::ifvar($cx, ((isset($in['summary']) && is_array($in)) ? $in['summary'] : null))) ? ''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-resummarize-topic'),array()), 'raw')),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-topic-action-summarize-topic'),array()), 'raw')),array()), 'encq').'').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','post'),array()), $in, false, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['edit']) && is_array($in['actions'])) ? $in['actions']['edit'] : null))) ? '<li>
'.$sp.'				<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-progressive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['edit']['title']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   data-flow-api-handler="activateEditPost"
'.$sp.'				   data-flow-api-target="< .flow-post-main"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				>'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-edit mw-ui-icon-edit-progressive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array('flow-post-action-edit-post'),array()), 'encq').'</a>
'.$sp.'			</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['links']['post']) && is_array($in['links'])) ? $in['links']['post'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'				   href="'.htmlentities((string)((isset($in['links']['post']['url']) && is_array($in['links']['post'])) ? $in['links']['post']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['links']['post']['title']) && is_array($in['links']['post'])) ? $in['links']['post']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-link"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-post-action-view'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).'</section>
'.$sp.'
'.$sp.'<section>'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['undo']) && is_array($in['actions'])) ? $in['actions']['undo'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['undo']['url']) && is_array($in['actions']['undo'])) ? $in['actions']['undo']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				>'.htmlentities((string)((isset($in['actions']['undo']['title']) && is_array($in['actions']['undo'])) ? $in['actions']['undo']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).''.((LCRun3::ifvar($cx, ((isset($in['actions']['hide']) && is_array($in['actions'])) ? $in['actions']['hide'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['hide']['url']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['hide']['title']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="hide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-hide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['unhide']['title']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="unhide">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-flag"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unhide-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['delete']) && is_array($in['actions'])) ? $in['actions']['delete'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['delete']['url']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['delete']['title']) && is_array($in['actions']['delete'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="delete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-remove mw-ui-icon-remove-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-delete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['undelete']) && is_array($in['actions'])) ? $in['actions']['undelete'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['undelete']['url']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['undelete']['title']) && is_array($in['actions']['undelete'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="undelete">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-remove mw-ui-icon-remove-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-undelete-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['suppress']) && is_array($in['actions'])) ? $in['actions']['suppress'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['suppress']['url']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['suppress']['title']) && is_array($in['actions']['suppress'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="suppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-suppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unsuppress']) && is_array($in['actions'])) ? $in['actions']['unsuppress'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'			   href="'.htmlentities((string)((isset($in['actions']['unsuppress']['url']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   title="'.htmlentities((string)((isset($in['actions']['unsuppress']['title']) && is_array($in['actions']['unsuppress'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'			   data-flow-interactive-handler="moderationDialog"
'.$sp.'			   data-flow-template="flow_moderate_'.htmlentities((string)((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null), ENT_QUOTES, 'UTF-8').'.partial"
'.$sp.'			   data-role="unsuppress">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-block mw-ui-icon-block-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unsuppress-',((isset($in['moderationTemplate']) && is_array($in)) ? $in['moderationTemplate'] : null)),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'===','history'),array()), $in, false, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="lockTopic"
'.$sp.'				   data-flow-api-method="POST"
'.$sp.'				   data-role="lock"
'.$sp.'				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="lockTopic"
'.$sp.'				   data-flow-api-method="POST"
'.$sp.'				   data-role="unlock"
'.$sp.'				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}, function($cx, $in)use($sp){return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['lock']) && is_array($in['actions'])) ? $in['actions']['lock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-destructive mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="lockAndSummarizeTopic"
'.$sp.'				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
'.$sp.'				   data-flow-api-method="POST"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['lock']['title']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-check mw-ui-icon-check-destructive-hover"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-lock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((isset($in['actions']['unlock']) && is_array($in['actions'])) ? $in['actions']['unlock'] : null))) ? '<li>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<a class="'.htmlentities((string)((isset($in['moderationMwUiClass']) && is_array($in)) ? $in['moderationMwUiClass'] : null), ENT_QUOTES, 'UTF-8').' mw-ui-quiet mw-ui-hovericon"
'.$sp.'				   data-flow-interactive-handler="apiRequest"
'.$sp.'				   data-flow-api-handler="lockAndSummarizeTopic"
'.$sp.'				   data-flow-id="'.htmlentities((string)((isset($in['postId']) && is_array($in)) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   data-flow-api-target="< .flow-topic-titlebar .flow-topic-summary-container"
'.$sp.'				   data-flow-api-method="POST"
'.$sp.'				   href="'.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				   title="'.htmlentities((string)((isset($in['actions']['unlock']['title']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.((LCRun3::ifvar($cx, ((isset($in['moderationIcons']) && is_array($in)) ? $in['moderationIcons'] : null))) ? '<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-ongoingConversation"></span> ' : '').''.LCRun3::ch($cx, 'l10n', array(array(LCRun3::ch($cx, 'concat', array(array('flow-',((isset($in['moderationType']) && is_array($in)) ? $in['moderationType'] : null),'-action-unlock-topic'),array()), 'raw')),array()), 'encq').'</a>'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'';}).'</section>
';},'flow_history_line' => function ($cx, $in, $sp) {return ''.$sp.'<span class="flow-pipelist">
'.$sp.'	('.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<span>'.((LCRun3::ifvar($cx, ((isset($in['links']['diff-cur']) && is_array($in['links'])) ? $in['links']['diff-cur'] : null))) ? '<a href="'.htmlentities((string)((isset($in['links']['diff-cur']['url']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities((string)((isset($in['links']['diff-cur']['title']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['links']['diff-cur']['text']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['text'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun3::ch($cx, 'l10n', array(array('cur'),array()), 'encq').'').'</span>
'.$sp.'	<span>
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['links']['diff-prev']) && is_array($in['links'])) ? $in['links']['diff-prev'] : null))) ? '			<a href="'.htmlentities((string)((isset($in['links']['diff-prev']['url']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities((string)((isset($in['links']['diff-prev']['title']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['links']['diff-prev']['text']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['text'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun3::ch($cx, 'l10n', array(array('last'),array()), 'encq').'').'</span>'.((LCRun3::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null))) ? '		<span><a href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities((string)((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['links']['topic']['text']) && is_array($in['links']['topic'])) ? $in['links']['topic']['text'] : null), ENT_QUOTES, 'UTF-8').'</a></span>' : '').')
'.$sp.'</span>
'.$sp.'
'.$sp.''.LCRun3::ch($cx, 'historyTimestamp', array(array($in),array()), 'encq').'
'.$sp.'
'.$sp.'<span class="mw-changeslist-separator">. .</span>
'.$sp.''.LCRun3::ch($cx, 'historyDescription', array(array($in),array()), 'encq').'
'.$sp.'
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['size']) && is_array($in)) ? $in['size'] : null))) ? '	<span class="mw-changeslist-separator">. .</span>
'.$sp.'	'.LCRun3::ch($cx, 'showCharacterDifference', array(array(((isset($in['size']['old']) && is_array($in['size'])) ? $in['size']['old'] : null),((isset($in['size']['new']) && is_array($in['size'])) ? $in['size']['new'] : null)),array()), 'encq').'
'.$sp.'' : '').'
'.$sp.'<ul class="flow-history-moderation-menu">
'.$sp.''.LCRun3::p($cx, 'flow_moderation_actions_list', array(array($in),array('moderationType'=>'history','moderationTarget'=>'post','moderationTemplate'=>'post','moderationMwUiClass'=>'mw-ui-anchor','moderationIcons'=>false)), '	').'</ul>
';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="flow-board">
	<div class="flow-topic-histories">
		'.LCRun3::ch($cx, 'html', array(array(((isset($in['navbar']) && is_array($in)) ? $in['navbar'] : null)),array()), 'encq').'

		<ul>
'.LCRun3::sec($cx, ((isset($in['revisions']) && is_array($in)) ? $in['revisions'] : null), $in, true, function($cx, $in) {return '				<li>'.LCRun3::p($cx, 'flow_history_line', array(array($in),array())).'</li>
';}).'		</ul>

		'.LCRun3::ch($cx, 'html', array(array(((isset($in['navbar']) && is_array($in)) ? $in['navbar'] : null)),array()), 'encq').'
	</div>
</div>
';
}
?>