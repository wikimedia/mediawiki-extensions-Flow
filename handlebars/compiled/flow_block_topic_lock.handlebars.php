<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'mustsec' => false,
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
        'partials' => array('flow_errors' => function ($cx, $in) {return '<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
		<ul>
'.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in) {return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
';}).'		</ul>
	</div>
' : '').'</div>
';},'flow_form_buttons' => function ($cx, $in) {return '<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right flow-js"

>'.LCRun3::ch($cx, 'l10n', array(array('flow-preview'),array()), 'encq').'</button>

<button data-flow-interactive-handler="cancelForm"
        data-role="cancel"
        type="reset"
        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"

>'.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'</button>
';},'flow_topic_titlebar_lock' => function ($cx, $in) {return '<div class="flow-topic-summary-container">
	<div class="flow-topic-summary">
		<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST"
			  action="'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? ''.htmlentities((string)((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'').'">
'.LCRun3::p($cx, 'flow_errors', array(array($in),array())).'			<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
			<textarea name="flow_reason"
			          class="mw-ui-input"
			          type="text"
			          required
			          data-flow-preview-node="moderateReason"
			          data-flow-preview-template="flow_topic_titlebar.partial"
					  data-flow-preview-title="'.htmlentities((string)((isset($in['articleTitle']) && is_array($in)) ? $in['articleTitle'] : null), ENT_QUOTES, 'UTF-8').'"
			>'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']['reason']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['reason'] : null))) ? ''.htmlentities((string)((isset($cx['sp_vars']['root']['submitted']['reason']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['reason'] : null), ENT_QUOTES, 'UTF-8').'' : '').'</textarea>
			<div class="flow-form-actions flow-form-collapsible">
				<button data-role="submit"
				        class="mw-ui-button mw-ui-constructive"
				        data-flow-interactive-handler="apiRequest"
				        data-flow-api-target="< .flow-topic"
				        data-flow-api-handler="lockTopic"
				>
'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '						'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-action-unlock-topic'),array()), 'encq').'
' : '						'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-action-lock-topic'),array()), 'encq').'
').'				</button>
'.LCRun3::p($cx, 'flow_form_buttons', array(array($in),array())).'				<small class="flow-terms-of-use plainlinks">
'.((LCRun3::ifvar($cx, ((isset($in['isModerated']) && is_array($in)) ? $in['isModerated'] : null))) ? '						'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-unlock-topic'),array()), 'encq').'
' : '						'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-lock-topic'),array()), 'encq').'
').'				</small>
			</div>
		</form>
	</div>
</div>
';},),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return ''.LCRun3::p($cx, 'flow_topic_titlebar_lock', array(array($in),array())).'
';
}
?>