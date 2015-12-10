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
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in)use($sp){return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_form_cancel_button' => function ($cx, $in, $sp) {return ''.$sp.'<button data-flow-interactive-handler="cancelForm"
'.$sp.'        data-role="cancel"
'.$sp.'        type="reset"
'.$sp.'        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"
'.$sp.'
'.$sp.'>
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['msg']) && is_array($in)) ? $in['msg'] : null))) ? ''.LCRun3::ch($cx, 'l10n', array(array(((isset($in['msg']) && is_array($in)) ? $in['msg'] : null)),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'').'</button>
';},'flow_topic_titlebar_lock' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board">
'.$sp.'	<div class="flow-topic-summary-container">
'.$sp.'		<div class="flow-topic-summary">
'.$sp.'			<form class="flow-edit-form" data-flow-initial-state="expanded" method="POST"
'.$sp.'				  action="'.((LCRun3::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))) ? ''.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'').'">
'.$sp.''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '				').'				<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.'				<input type="hidden" name="flow_reason" value="'.((LCRun3::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))) ? ''.LCRun3::ch($cx, 'l10n', array(array('flow-rev-message-restore-topic-reason'),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10n', array(array('flow-rev-message-lock-topic-reason'),array()), 'encq').'').'" />
'.$sp.'				<div class="flow-form-actions flow-form-collapsible">
'.$sp.'					<button data-role="submit"
'.$sp.'					        class="mw-ui-button mw-ui-constructive"
'.$sp.'					>
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))) ? '							'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-action-unlock-topic'),array()), 'encq').'
'.$sp.'' : '							'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-action-lock-topic'),array()), 'encq').'
'.$sp.'').'					</button>
'.$sp.''.LCRun3::p($cx, 'flow_form_cancel_button', array(array($in),array()), '					').'					<small class="flow-terms-of-use plainlinks">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null))) ? '							'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-unlock-topic'),array()), 'encq').'
'.$sp.'' : '							'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-lock-topic'),array()), 'encq').'
'.$sp.'').'					</small>
'.$sp.'				</div>
'.$sp.'			</form>
'.$sp.'		</div>
'.$sp.'	</div>
'.$sp.'</div>
';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return ''.LCRun3::p($cx, 'flow_topic_titlebar_lock', array(array($in),array())).'
';
}
?>