<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::html',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-topic-summary">
	<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST" action="'.htmlentities(((is_array($in['revision']['actions']['summarize']) && isset($in['revision']['actions']['summarize']['url'])) ? $in['revision']['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'">
		<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.LCRun3::ch($cx, 'html', Array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)), 'encq').'</li>
			';}).'
		</ul>
	</div>
' : '').'
</div>

		<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($in) && isset($in['editToken'])) ? $in['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />

		'.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null))) ? '
			<input type="hidden" name="'.htmlentities(((is_array($in) && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_prev_revision" value="'.htmlentities(((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
		' : '').'

		<textarea class="mw-ui-input"
		          required
		          name="'.htmlentities(((is_array($in) && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_summary"
		          data-flow-preview-node="summary"
		          data-flow-preview-template="flow_topic_titlebar_summary"
		          type="text"
				  data-role="content">'.((LCRun3::ifvar($cx, ((is_array($in['submitted']) && isset($in['submitted']['summary'])) ? $in['submitted']['summary'] : null))) ? ''.htmlentities(((is_array($in['submitted']) && isset($in['submitted']['summary'])) ? $in['submitted']['summary'] : null), ENT_QUOTES, 'UTF-8').'' : ''.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null))) ? ''.htmlentities(((is_array($in['revision']['content']) && isset($in['revision']['content']['content'])) ? $in['revision']['content']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'').'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button
				data-role="submit"
				class="mw-ui-button mw-ui-constructive"
				data-flow-interactive-handler="apiRequest"
				data-flow-api-handler="summarizeTopic"
				data-flow-api-target="< .flow-topic-summary">
					'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-summarize-topic'), 'encq').'
			</button>
			<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive  mw-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-preview'), 'encq').'</button>
			<a href="'.htmlentities(((is_array($in['revision']['links']['topic']) && isset($in['revision']['links']['topic']['url'])) ? $in['revision']['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['revision']['links']['topic']) && isset($in['revision']['links']['topic']['title'])) ? $in['revision']['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-interactive-handler="cancelForm" data-role="cancel"
			   class="mw-ui-button mw-ui-destructive mw-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'</a>
			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array('flow-terms-of-use-summarize'), 'encq').'</small>
		</div>
	</form>
</div>
';
}
?>