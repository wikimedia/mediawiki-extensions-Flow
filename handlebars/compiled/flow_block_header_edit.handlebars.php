<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board-header">
	<div class="flow-board-header-edit-view">
		'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
			<ul>
				'.LCRun3::sec($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, true, function($cx, $in) {return '
					<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
				';}).'
			</ul>
		' : '').'

		<form method="POST" action="'.htmlentities(((is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['url'])) ? $in['revision']['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'" flow-api-action="edit-header">
			<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
			'.((LCRun3::ifvar($cx, ((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null))) ? '
				<input type="hidden" name="header_prev_revision" value="'.htmlentities(((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
			' : '').'
			<textarea name="header_content" class="mw-ui-input" placeholder="'.LCRun3::ch($cx, 'l10n', Array('flow-edit-header-placeholder'), 'encq').'">'.((LCRun3::ifvar($cx, ((is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null))) ? ''.htmlentities(((is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null), ENT_QUOTES, 'UTF-8').'').'</textarea>
			<div class="flow-form-actions flow-form-collapsible">
				<button data-role="submit"
					class="flow-ui-button flow-ui-constructive"
					data-flow-interactive-handler="apiRequest"
					data-flow-api-handler="submitHeader">'.LCRun3::ch($cx, 'l10n', Array('flow-edit-header-submit'), 'encq').'</button>
				<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-preview'), 'encq').'</button>
				<button data-flow-interactive-handler="cancelForm"
					data-role="cancel"
					class="flow-ui-button flow-ui-destructive
					flow-ui-quiet flow-click-interactive">'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'</button>
				<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10n', Array('flow-terms-of-use-edit'), 'encq').'</small>
			</div>
		</form>
	</div>
</div>
';
}
?>