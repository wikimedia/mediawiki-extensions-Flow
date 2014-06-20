<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'previewButton' => 'Flow\TemplateHelper::previewButton',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board-header">
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
	<ul class="flow-errors">
		'.LCRun3::sec($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, true, function($cx, $in) {return '
			<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
		';}).'
	</ul>
' : '').'


	<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST" action="'.htmlentities(((is_array($in['revision']['actions']['summarize']) && isset($in['revision']['actions']['summarize']['url'])) ? $in['revision']['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'">
		<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($in) && isset($in['editToken'])) ? $in['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />

		'.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null))) ? '
			<input type="hidden" name="'.htmlentities(((is_array($in) && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_prev_revision" value="'.htmlentities(((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
		' : '').'

		<textarea name="'.htmlentities(((is_array($in) && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_summary" data-flow-expandable="true" class="mw-ui-input" type="text" data-role="content">'.((LCRun3::ifvar($cx, ((is_array($in['submitted']) && isset($in['submitted']['summary'])) ? $in['submitted']['summary'] : null))) ? ''.htmlentities(((is_array($in['submitted']) && isset($in['submitted']['summary'])) ? $in['submitted']['summary'] : null), ENT_QUOTES, 'UTF-8').'' : ''.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null))) ? ''.htmlentities(((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'').'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button
				data-role="submit"
				class="flow-ui-button flow-ui-constructive"
				data-flow-interactive-handler="apiRequest"
				data-flow-api-handler="summarizeTopic">
					'.LCRun3::ch($cx, 'l10n', Array('flow-topic-action-summarize-topic'), 'encq').'
			</button>
			'.LCRun3::ch($cx, 'previewButton', Array('flow_topic_titlebar_content'), 'encq').'
			<a href="'.htmlentities(((is_array($in['revision']['links']['topic']) && isset($in['revision']['links']['topic']['url'])) ? $in['revision']['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['revision']['links']['topic']) && isset($in['revision']['links']['topic']['title'])) ? $in['revision']['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'" data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'</a>
			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10n', Array('flow-terms-of-use-summarize'), 'encq').'</small>
		</div>
	</form>
</div>
';
}
?>