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
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
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
';},'flow_edit_topic_title' => function ($cx, $in) {return '<form method="POST" action="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'">
	'.LCRun3::p($cx, 'flow_errors', Array(Array($in),Array())).'
	<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['scopes'][0]['editToken']) && is_array($cx['scopes'][0])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	
	<input type="hidden" name="topic_prev_revision" value="'.htmlentities((string)((isset($in['revisionId']) && is_array($in)) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
	<input name="topic_content" class="mw-ui-input" value="'.((LCRun3::ifvar($cx, ((isset($cx['scopes'][0]['submitted']['content']) && is_array($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted']['content'] : null))) ? ''.htmlentities((string)((isset($cx['scopes'][0]['submitted']['content']) && is_array($cx['scopes'][0]['submitted'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null), ENT_QUOTES, 'UTF-8').'').'" />
	<div class="flow-form-actions flow-form-collapsible">
		<button data-role="submit"
		        data-flow-api-handler="submitTopicTitle"
		        data-flow-api-target="< .flow-topic"
		        class="mw-ui-button mw-ui-constructive">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-edit-title-submit'),Array()), 'encq').'</button>

		'.LCRun3::hbch($cx, 'progressiveEnhancement', Array(Array(),Array()), $in, function($cx, $in) {return '
			<button data-role="cancel"
			        type="reset"
			        data-flow-interactive-handler="cancelForm"
			        class="mw-ui-button mw-ui-destructive mw-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>
			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-edit'),Array()), 'encq').'</small>
		';}).'
	</div>
</form>
';},),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	

	'.LCRun3::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array(Array($cx['scopes'][0],$in),Array()), $in, function($cx, $in) {return '
			'.LCRun3::p($cx, 'flow_edit_topic_title', Array(Array($in),Array())).'
		';}).'
	';}).'
</div>
';
}
?>
