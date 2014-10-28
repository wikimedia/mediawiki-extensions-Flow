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
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
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
';},'flow_form_buttons' => function ($cx, $in) {return '<button data-flow-api-handler="preview"
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
';},),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-topic-summary-container">
	<div class="flow-topic-summary">
		<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST" action="'.htmlentities((string)((isset($in['revision']['actions']['summarize']['url']) && is_array($in['revision']['actions']['summarize'])) ? $in['revision']['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'">
			'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'
			<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($in['editToken']) && is_array($in)) ? $in['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />

			'.((LCRun3::ifvar($cx, ((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null))) ? '
				<input type="hidden" name="'.htmlentities((string)((isset($in['type']) && is_array($in)) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_prev_revision" value="'.htmlentities((string)((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
			' : '').'

			<textarea class="mw-ui-input"
				  required
				  name="'.htmlentities((string)((isset($in['type']) && is_array($in)) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_summary"
				  data-flow-preview-node="summary"
				  data-flow-preview-template="flow_topic_titlebar_summary"
				  type="text"
					  data-role="content">'.((LCRun3::ifvar($cx, ((isset($in['submitted']['summary']) && is_array($in['submitted'])) ? $in['submitted']['summary'] : null))) ? ''.htmlentities((string)((isset($in['submitted']['summary']) && is_array($in['submitted'])) ? $in['submitted']['summary'] : null), ENT_QUOTES, 'UTF-8').'' : ''.((LCRun3::ifvar($cx, ((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null))) ? ''.htmlentities((string)((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'').'</textarea>
			<div class="flow-form-actions flow-form-collapsible">
				<button
					data-role="submit"
					class="mw-ui-button mw-ui-constructive"
					data-flow-interactive-handler="apiRequest"
					data-flow-api-handler="summarizeTopic"
					data-flow-api-target="< .flow-topic-summary-container">
						'.LCRun3::ch($cx, 'l10n', Array(Array('flow-topic-action-summarize-topic'),Array()), 'encq').'
				</button>
				'.LCRun3::p($cx, 'flow_form_buttons', Array(Array($in),Array())).'
				<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-summarize'),Array()), 'encq').'</small>
			</div>
		</form>
	</div>
</div>
';
}
?>