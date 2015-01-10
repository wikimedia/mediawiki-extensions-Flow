<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'mustsec' => false,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'linkWithReturnTo' => 'Flow\TemplateHelper::linkWithReturnTo',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
            'tooltip' => 'Flow\TemplateHelper::tooltip',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
        'partials' => Array('flow_errors' => function ($cx, $in) {return '<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((isset($cx['scopes'][0]['errors']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((isset($cx['scopes'][0]['errors']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.LCRun3::ch($cx, 'html', Array(Array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),Array()), 'encq').'</li>
			';}).'
		</ul>
	</div>
' : '').'
</div>
';},'flow_anon_warning' => function ($cx, $in) {return '<div class="flow-anon-warning">
	<div class="flow-anon-warning-mobile">
		
		'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
			'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
	</div>

	
	'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array()), $in, function($cx, $in) {return '
		<div class="flow-anon-warning-desktop">
			'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
				'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
		</div>
	';}).'
</div>';},'flow_form_buttons' => function ($cx, $in) {return '<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right flow-js"

        
>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-preview'),Array()), 'encq').'</button>

<button data-flow-interactive-handler="cancelForm"
        data-role="cancel"
        type="reset"
        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"

        
        
>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>
';},'flow_newtopic_form' => function ($cx, $in) {return ''.((LCRun3::ifvar($cx, ((isset($in['actions']['newtopic']) && is_array($in['actions'])) ? $in['actions']['newtopic'] : null))) ? '
	<form action="'.htmlentities((string)((isset($in['actions']['newtopic']['url']) && is_array($in['actions']['newtopic'])) ? $in['actions']['newtopic']['url'] : null), ENT_QUOTES, 'UTF-8').'" method="POST" class="flow-newtopic-form" data-flow-initial-state="collapsed">
		'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'

		'.LCRun3::hbch($cx, 'ifAnonymous', Array(Array(),Array()), $in, function($cx, $in) {return '
			'.LCRun3::p($cx, 'flow_anon_warning', Array(Array($in),Array())).'
		';}).'

		<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['scopes'][0]['editToken']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topiclist_replyTo" value="'.htmlentities((string)((isset($in['workflowId']) && is_array($in)) ? $in['workflowId'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input name="topiclist_topic" class="mw-ui-input mw-ui-input-large"
			required
			type="text"
			placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-start-placeholder'),Array()), 'encq').'"
			data-role="title"

			
			data-flow-interactive-handler-focus="activateNewTopic"
		/>
		<textarea name="topiclist_content"
			data-flow-preview-template="flow_topic"
			class="mw-ui-input flow-form-collapsible mw-ui-input-large"
			'.((LCRun3::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null))) ? 'style="display:none;"' : '').'
			placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-content-placeholder',((isset($cx['scopes'][0]['title']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['title'] : null)),Array()), 'encq').'"
			data-role="content"
			required
		></textarea>

		<div class="flow-form-actions flow-form-collapsible"
			'.((LCRun3::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null))) ? 'style="display:none;"' : '').'>
			<button data-role="submit" data-flow-api-handler="newTopic"
				data-flow-interactive-handler="apiRequest"
				data-flow-eventlog-action="save-attempt"
				class="mw-ui-button mw-ui-constructive mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-save'),Array()), 'encq').'</button>
			'.LCRun3::p($cx, 'flow_form_buttons', Array(Array($in),Array())).'
			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-new-topic'),Array()), 'encq').'</small>
		</div>
	</form>
' : '').'
';},),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	'.LCRun3::p($cx, 'flow_newtopic_form', Array(Array($in),Array())).'
</div>
';
}
?>
