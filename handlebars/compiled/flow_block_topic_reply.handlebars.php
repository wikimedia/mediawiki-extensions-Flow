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
            'previewButton' => 'Flow\TemplateHelper::previewButton',
            'plaintextSnippet' => 'Flow\TemplateHelper::plaintextSnippet',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'ifEquals' => 'Flow\TemplateHelper::ifEquals',
),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((is_array($in) && isset($in['errors'])) ? $in['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.htmlentities(((is_array($in) && isset($in['message'])) ? $in['message'] : null), ENT_QUOTES, 'UTF-8').'</li>
			';}).'
		</ul>
	</div>
' : '').'


	

	'.LCRun3::sec($cx, ((is_array($in) && isset($in['roots'])) ? $in['roots'] : null), $in, true, function($cx, $in) {return '
		'.LCRun3::hbch($cx, 'eachPost', Array($cx['scopes'][0],$in), $in, function($cx, $in) {return '
			<form class="flow-reply-form" method="POST" action="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['url'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'">
				<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
				<input type="hidden" name="topic_replyTo" value="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />

				<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content"
				          name="topic_content"
				          class="mw-ui-input"
				          type="text"
				          placeholder="'.LCRun3::ch($cx, 'l10n', Array('flow-reply-topic-title-placeholder',LCRun3::ch($cx, 'plaintextSnippet', Array(((is_array($in) && isset($in['contentFormat'])) ? $in['contentFormat'] : null),((is_array($in) && isset($in['content'])) ? $in['content'] : null)), 'encq')), 'encq').'"
				          data-role="content">'.LCRun3::hbch($cx, 'ifEquals', Array(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['postId'])) ? $cx['scopes'][0]['submitted']['postId'] : null),((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)), $in, function($cx, $in) {return ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'';}).'</textarea>

				<div class="flow-form-actions flow-form-collapsible">
					<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'encq').'</button>
					'.LCRun3::ch($cx, 'previewButton', Array('flow_post'), 'encq').'
					<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-cancel'), 'encq').'</button>
					<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array('flow-terms-of-use-reply'), 'encq').'</small>
				</div>
			</form>
		';}).'
	';}).'
</div>
';
}
?>