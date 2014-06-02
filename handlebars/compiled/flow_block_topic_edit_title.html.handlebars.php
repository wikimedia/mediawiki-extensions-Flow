<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
),
        'blockhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-board">
	'.((LCRun2::ifvar(((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
		<ul>
		'.LCRun2::sec(((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $cx, $in, true, function($cx, $in) {return '
			<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
		';}).'
		</ul>
	' : '').'

	

	'.LCRun2::bch('eachPost', Array($in,((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)), $cx, $in, function($cx, $in) {return '
		<form method="POST" action="'.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'">
	<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	
	<input type="hidden" name="topic_prev_revision" value="'.htmlentities(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
	<textarea name="topic_content" class="mw-ui-input flow-form-collapsible">'.((LCRun2::ifvar(((is_array($in) && isset($in['submitted'])) ? $in['submitted'] : null))) ? '
			'.htmlentities(((is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in) && isset($in['content'])) ? $in['content'] : null), ENT_QUOTES, 'UTF-8').'').'</textarea>
	<div class="flow-form-actions flow-form-collapsible">
		<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Edit',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'</button>
		<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Preview'), 'enc', $cx).'</button>
		<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Cancel'), 'enc', $cx).'</button>
		<small class="flow-terms-of-use plainlinks">'.LCRun2::ch('l10n', Array('edit_TOU'), 'enc', $cx).'</small>
	</div>
</form>

	';}).'
</div>
';
}
?>