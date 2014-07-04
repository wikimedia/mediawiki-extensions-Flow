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
	<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST"
		  action="'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? ''.htmlentities(((is_array($in['actions']['reopen']) && isset($in['actions']['reopen']['url'])) ? $in['actions']['reopen']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in['actions']['close']) && isset($in['actions']['close']['url'])) ? $in['actions']['close']['url'] : null), ENT_QUOTES, 'UTF-8').'').'">
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

		<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['summaryRevId'])) ? $in['summaryRevId'] : null))) ? '
			<input type="hidden" name="flow_prev_revision" value="'.htmlentities(((is_array($in) && isset($in['summaryRevId'])) ? $in['summaryRevId'] : null), ENT_QUOTES, 'UTF-8').'" />
		' : '').'
		<textarea name="flow_summary"
		          class="mw-ui-input"
		          type="text"
		          required
		          data-flow-preview-node="summary"
		          data-flow-preview-template="flow_topic_titlebar_summary">'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['summary'])) ? $in['summary'] : null))) ? ''.htmlentities(((is_array($in['summary']) && isset($in['summary']['content'])) ? $in['summary']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'').'</textarea>
		<div class="flow-form-actions flow-form-collapsible">
			<button
				data-role="submit"
				class="mw-ui-button mw-ui-constructive"
				data-flow-interactive-handler="apiRequest"
				data-flow-api-target="< .flow-topic-titlebar"
				data-flow-api-handler="closeOpenTopic">
					'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? '
						'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-reopen-topic'), 'encq').'
					' : '
						'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-close-topic'), 'encq').'
					').'
			</button>
			<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive  mw-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-preview'), 'encq').'</button>
			<a
				href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'"
				title="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'"
				data-flow-interactive-handler="cancelForm"
				data-role="cancel"
				class="mw-ui-button mw-ui-destructive mw-ui-quiet">
					'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'
			</a>
			<small class="flow-terms-of-use plainlinks">
				'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isModerated'])) ? $in['isModerated'] : null))) ? '
					'.LCRun3::ch($cx, 'l10nParse', Array('flow-terms-of-use-reopen-topic'), 'encq').'
				' : '
					'.LCRun3::ch($cx, 'l10nParse', Array('flow-terms-of-use-close-topic'), 'encq').'
				').'
			</small>
		</div>
	</form>
</div>


';
}
?>