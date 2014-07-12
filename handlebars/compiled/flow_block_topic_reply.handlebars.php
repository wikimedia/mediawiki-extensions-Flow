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
	'.'

	'.LCRun3::hbch($cx, 'eachPost', Array(Array($cx['scopes'][0],((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)),Array()), $in, function($cx, $in) {return '
		<form class="flow-reply-form" method="POST" action="'.htmlentities(((is_array($in['actions']['reply']) && isset($in['actions']['reply']['url'])) ? $in['actions']['reply']['url'] : null), ENT_QUOTES, 'UTF-8').'">
			<input type="hidden" name="wpEditToken" value="'.htmlentities(((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['editToken'])) ? $cx['scopes'][0]['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
			<input type="hidden" name="topic_replyTo" value="'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'" />
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


			<textarea id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'-form-content"
				      data-flow-preview-template="flow_post"
				      name="topic_content"
				      class="mw-ui-input"
				      type="text"
				      placeholder="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-reply-topic-title-placeholder',LCRun3::ch($cx, 'plaintextSnippet', Array(Array(((is_array($in['content']) && isset($in['content']['format'])) ? $in['content']['format'] : null),((is_array($in['content']) && isset($in['content']['content'])) ? $in['content']['content'] : null)),Array()), 'raw')),Array()), 'encq').'"
				      data-role="content">'.LCRun3::hbch($cx, 'ifEquals', Array(Array(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['postId'])) ? $cx['scopes'][0]['submitted']['postId'] : null),((is_array($in) && isset($in['postId'])) ? $in['postId'] : null)),Array()), $in, function($cx, $in) {return ''.htmlentities(((is_array($cx['scopes'][0]['submitted']) && isset($cx['scopes'][0]['submitted']['content'])) ? $cx['scopes'][0]['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'';}).'</textarea>

			<div class="flow-form-actions flow-form-collapsible">
				<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun3::ch($cx, 'l10n', Array(Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)),Array()), 'encq').'</button>
				<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-preview'),Array()), 'encq').'</button>
<button data-flow-interactive-handler="cancelForm" data-role="cancel"
	class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>

				<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', Array(Array('flow-terms-of-use-reply'),Array()), 'encq').'</small>
			</div>
		</form>
	';}).'
</div>
';
}
?>