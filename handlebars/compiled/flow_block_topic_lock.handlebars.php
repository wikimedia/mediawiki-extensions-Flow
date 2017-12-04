use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in = null, $options = null) {
    $helpers = array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
);
    $partials = array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, (isset($cx['sp_vars']['root']['errors']) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, (isset($cx['sp_vars']['root']['errors']) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, LR::hbch($cx, 'html', array(array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)),array()), 'encq', $in)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_form_cancel_button' => function ($cx, $in, $sp) {return ''.$sp.'<button data-flow-interactive-handler="cancelForm"
'.$sp.'        data-role="cancel"
'.$sp.'        type="reset"
'.$sp.'        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"
'.$sp.'
'.$sp.'>
'.$sp.''.((LR::ifvar($cx, ((is_array($in) && isset($in['msg'])) ? $in['msg'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(((is_array($in) && isset($in['msg'])) ? $in['msg'] : null)),array()), 'encq', $in)).'' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq', $in)).'').'</button>
';},
'flow_topic_titlebar_lock' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board">
'.$sp.'	<div class="flow-topic-summary-container">
'.$sp.'		<div class="flow-topic-summary">
'.$sp.'			<form class="flow-edit-form" data-flow-initial-state="expanded" method="POST"
'.$sp.'				  action="'.((LR::ifvar($cx, ((is_array($in) && isset($in['isLocked'])) ? $in['isLocked'] : null), false)) ? ''.LR::encq($cx, ((isset($in['actions']['unlock']) && is_array($in['actions']['unlock']) && isset($in['actions']['unlock']['url'])) ? $in['actions']['unlock']['url'] : null)).'' : ''.LR::encq($cx, ((isset($in['actions']['lock']) && is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null)).'').'">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()),0, '				').'				<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, (isset($cx['sp_vars']['root']['editToken']) ? $cx['sp_vars']['root']['editToken'] : null)).'" />
'.$sp.'				<input type="hidden" name="flow_reason" value="'.((LR::ifvar($cx, ((is_array($in) && isset($in['isLocked'])) ? $in['isLocked'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-rev-message-restore-topic-reason'),array()), 'encq', $in)).'' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-rev-message-lock-topic-reason'),array()), 'encq', $in)).'').'" />
'.$sp.'				<div class="flow-form-actions flow-form-collapsible">
'.$sp.'					<button data-role="submit"
'.$sp.'					        class="mw-ui-button mw-ui-progressive"
'.$sp.'					>
'.$sp.''.((LR::ifvar($cx, ((is_array($in) && isset($in['isLocked'])) ? $in['isLocked'] : null), false)) ? '							'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-topic-action-unlock-topic'),array()), 'encq', $in)).'
'.$sp.'' : '							'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-topic-action-lock-topic'),array()), 'encq', $in)).'
'.$sp.'').'					</button>
'.$sp.''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()),0, '					').'					<small class="flow-terms-of-use plainlinks">
'.$sp.''.((LR::ifvar($cx, ((is_array($in) && isset($in['isLocked'])) ? $in['isLocked'] : null), false)) ? '							'.LR::encq($cx, LR::hbch($cx, 'l10nParse', array(array('flow-terms-of-use-unlock-topic'),array()), 'encq', $in)).'
'.$sp.'' : '							'.LR::encq($cx, LR::hbch($cx, 'l10nParse', array(array('flow-terms-of-use-lock-topic'),array()), 'encq', $in)).'
'.$sp.'').'					</small>
'.$sp.'				</div>
'.$sp.'			</form>
'.$sp.'		</div>
'.$sp.'	</div>
'.$sp.'</div>
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
    
    return ''.LR::p($cx, 'flow_topic_titlebar_lock', array(array($in),array()),0).'
';
};