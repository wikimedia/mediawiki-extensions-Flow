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
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-topic-summary-container">
	<div class="flow-topic-summary">
		<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST"
			  action="'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? ''.htmlentities(((is_array($in['actions']['unlock']) && isset($in['actions']['unlock']['url'])) ? $in['actions']['unlock']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), ENT_QUOTES, 'UTF-8').'').'">
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

			<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
			'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['summary'])) ? $in['summary'] : null))) ? '
				<input type="hidden" name="flow_prev_revision" value="'.htmlentities(((is_array($in['summary']) && isset($in['summary']['revId'])) ? $in['summary']['revId'] : null), ENT_QUOTES, 'UTF-8').'" />
			' : '').'
			<textarea name="flow_summary"
				  class="mw-ui-input"
				  type="text"
				  required
				  data-flow-preview-node="summary"
				  data-flow-preview-template="flow_topic_titlebar_summary">'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['summary'])) ? $in['summary'] : null))) ? ''.'
						'.htmlentities(((is_array($in['summary']) && isset($in['summary']['content'])) ? $in['summary']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'').'</textarea>
			<div class="flow-form-actions flow-form-collapsible">
				<button
					data-role="submit"
					class="mw-ui-button mw-ui-constructive"
					data-flow-interactive-handler="apiRequest"
					data-flow-api-target="< .flow-topic"
					data-flow-api-handler="lockTopic">
						'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? '
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-unlock-topic'),Array()), 'encq').'
						' : '
							'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-lock-topic'),Array()), 'encq').'
						').'
				</button>
				'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array()), $in, function($cx, $in) {return '
	<button data-flow-api-handler="preview"
	        data-flow-api-target="< form textarea"
	        name="preview"
	        data-role="action"
	        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right"
	>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-preview'),Array()), 'encq').'</button>

	<button data-flow-interactive-handler="cancelForm"
	        data-role="cancel"
	        type="reset"
	        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right"
	>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>
';}).'

				<small class="flow-terms-of-use plainlinks">
					'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? '
						'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-unlock-topic'),Array()), 'encq').'
					' : '
						'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-lock-topic'),Array()), 'encq').'
					').'
				</small>
			</div>
		</form>
	</div>
</div>


';
}
?>