<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
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
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['newtopic'])) ? $in['actions']['newtopic'] : null))) ? '
	<form action="'.htmlentities(((is_array($in['actions']['newtopic']) && isset($in['actions']['newtopic']['url'])) ? $in['actions']['newtopic']['url'] : null), ENT_QUOTES, 'UTF-8').'" method="POST" class="flow-newtopic-form" data-flow-initial-state="collapsed">
		<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.LCRun3::ch($cx, 'html', Array(Array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)),Array()), 'encq').'</li>
			';}).'
		</ul>
	</div>
' : '').'
</div>


		'.LCRun3::hbch($cx, 'ifAnonymous', Array(Array(),Array()), $in, function($cx, $in) {return '
			'.LCRun3::hbch($cx, 'tooltip', Array(Array(),Array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-anon-warning flow-form-collapsible','isBlock'=>true)), $in, function($cx, $in) {return '
	'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin'),Array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', Array(Array('Special:UserLogin/signup'),Array()), 'raw')),Array()), 'encq').'';}).'
		';}).'

		<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topiclist_replyTo" value="'.htmlentities(((is_array($in) && isset($in['workflowId'])) ? $in['workflowId'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input name="topiclist_topic" class="mw-ui-input mw-ui-input-large"
			required
			type="text" placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-start-placeholder'),Array()), 'encq').'" data-role="title"/>
		<textarea name="topiclist_content"
			data-flow-preview-template="flow_post"
			class="mw-ui-input flow-form-collapsible mw-ui-input-large"
			placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-content-placeholder'),Array()), 'encq').'" data-role="content"></textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit" data-flow-api-handler="newTopic"
				data-flow-interactive-handler="apiRequest"
				class="mw-ui-button mw-ui-constructive mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-newtopic-save'),Array()), 'encq').'</button>
			<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-preview'),Array()), 'encq').'</button>
<button data-flow-interactive-handler="cancelForm" data-role="cancel"
	class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>

			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-new-topic'),Array()), 'encq').'</small>
		</div>
	</form>
' : '').'

</div>
';
}
?>