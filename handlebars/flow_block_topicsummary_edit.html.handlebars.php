<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-board-header">
	'.((LCRun2::ifvar(((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
		<ul>
			'.LCRun2::sec(((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $cx, $in, true, function($cx, $in) {return '
				<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
			';}).'
		</ul>
	' : '').'
	<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST" action="'.htmlentities(((is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['url'])) ? $in['revision']['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'">
	    <input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($in) && isset($in['editToken'])) ? $in['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		'.((LCRun2::ifvar(((is_array($in['revision']) && isset($in['revision']['previousRevisionId'])) ? $in['revision']['previousRevisionId'] : null))) ? '
			<input type="hidden" name="'.htmlentities(((is_array($in) && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_prev_revision" value="'.htmlentities(((is_array($in['revision']) && isset($in['revision']['previousRevisionId'])) ? $in['revision']['previousRevisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
		' : '').'
		<textarea name="'.htmlentities(((is_array($in) && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_summary" data-flow-expandable="true" class="mw-ui-input" type="text">'.((LCRun2::ifvar(((is_array($in) && isset($in['submitted'])) ? $in['submitted'] : null))) ? ''.htmlentities(((is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.((LCRun2::ifvar(((is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null))) ? ''.htmlentities(((is_array($in['revision']) && isset($in['revision']['content'])) ? $in['revision']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'').'</textarea>
		<div class="flow-form-actions flow-form-collapsible">
			<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Summarize'), 'enc', $cx).'</button>
			<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Preview'), 'enc', $cx).'</button>
			<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Cancel'), 'enc', $cx).'</button>
			<small class="flow-terms-of-use plainlinks">'.LCRun2::ch('l10n', Array('summarize_TOU'), 'enc', $cx).'</small>
		</div>
	</form>
</div>
';
}
?>