<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'linkWithReturnTo' => 'Flow\TemplateHelper::linkWithReturnTo',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
		<ul>
		'.LCRun3::sec($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, true, function($cx, $in) {return '
			<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
		';}).'
		</ul>
	' : '').'

	

	'.LCRun3::sec($cx, ((is_array($in) && isset($in['roots'])) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array($cx['scopes'][0],$in), $in, function($cx, $in) {return '
			<form method="POST" action="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'">
	<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	
	<input type="hidden" name="topic_prev_revision" value="'.htmlentities(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
	'.LCRun3::hbch($cx, 'ifAnonymous', Array(), $in, function($cx, $in) {return '
			<span class="flow-anon-warning flow-ui-tooltip flow-ui-progressive flow-ui-tooltip-down flow-form-collapsible plainlinks">
	'.LCRun3::ch($cx, 'l10nParse', Array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', Array('Special:Userlogin'), 'encq'),LCRun3::ch($cx, 'linkWithReturnTo', Array('Special:Userlogin/signup'), 'encq')), 'encq').'
	<span class="flow-ui-tooltip-triangle"></span>
</span>
	';}).'
	<textarea name="topic_content" class="mw-ui-input flow-form-collapsible">'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null))) ? ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in) && isset($in['content'])) ? $in['content'] : null), ENT_QUOTES, 'UTF-8').'').'</textarea>
	<div class="flow-form-actions flow-form-collapsible">
		<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'l10n', Array('flow-post-action-edit-post',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'</button>
		<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-preview'), 'encq').'</button>
		<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'</button>
		<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10n', Array('flow-terms-of-use-edit'), 'encq').'</small>
	</div>
</form>

		';}).'
	';}).'
</div>
';
}
?>