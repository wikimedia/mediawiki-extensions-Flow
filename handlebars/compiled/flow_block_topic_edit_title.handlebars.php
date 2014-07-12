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
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	'.'

	'.LCRun3::hbch($cx, 'eachPost', Array(Array($cx['scopes'][0],((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)),Array()), $in, function($cx, $in) {return '
		<form method="POST" action="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'">
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
	'.'
	<input type="hidden" name="topic_prev_revision" value="'.htmlentities(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
	<input name="topic_content" class="mw-ui-input" value="'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in['content']) && isset($in['content']['content'])) ? $in['content']['content'] : null), ENT_QUOTES, 'UTF-8').'').'" />
	<div class="flow-form-actions flow-form-collapsible">
		<button data-role="submit"
		        data-flow-api-handler="submitTopicTitle"
		        data-flow-api-target="< .flow-topic"
		        class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-edit-title-submit'),Array()), 'encq').'</button>
		<button data-role="cancel"
		        data-flow-interactive-handler="cancelForm"
		        class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>
		<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-edit'),Array()), 'encq').'</small>
	</div>
</form>

	';}).'
</div>
';
}
?>