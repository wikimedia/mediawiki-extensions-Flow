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
    return '<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST" action="'.((LCRun2::ifvar(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null))) ? ''.htmlentities(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in['actions']['create']) && isset($in['actions']['create']['url'])) ? $in['actions']['create']['url'] : null), ENT_QUOTES, 'UTF-8').'').'">
    <input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($in) && isset($in['editToken'])) ? $in['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	<input type="hidden" name="topic_replyTo" value="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />
	'.((LCRun2::ifvar(((is_array($in) && isset($in['previousRevisionId'])) ? $in['previousRevisionId'] : null))) ? '
		<input type="hidden" name="'.htmlentities(((is_array($in) && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_prev_revision" value="'.htmlentities(((is_array($in) && isset($in['previousRevisionId'])) ? $in['previousRevisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
	' : '').'
	<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content" name="'.htmlentities(((is_array($in) && isset($in['type'])) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_summary" data-flow-expandable="true" class="mw-ui-input" type="text" data-topic-summary-id="'.htmlentities(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'">
	</textarea>

	<div class="flow-form-actions flow-form-collapsible">
		<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Summarize'), 'enc', $cx).'</button>
		<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Preview'), 'enc', $cx).'</button>
		<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Cancel'), 'enc', $cx).'</button>

		<small class="flow-terms-of-use plainlinks">'.LCRun2::ch('l10n', Array('summarize_TOU'), 'enc', $cx).'</small>
	</div>
</form>
';
}
?>