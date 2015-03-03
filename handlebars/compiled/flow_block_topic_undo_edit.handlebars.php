<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'mustsec' => false,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' => array(),
        'helpers' => array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'diffUndo' => 'Flow\TemplateHelper::diffUndo',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array('flow_errors' => function ($cx, $in) {return '<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
		<ul>
'.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in) {return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
';}).'		</ul>
	</div>
' : '').'</div>
';},'flow_form_buttons' => function ($cx, $in) {return '<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right flow-js"

>'.LCRun3::ch($cx, 'l10n', array(array('flow-preview'),array()), 'encq').'</button>

<button data-flow-interactive-handler="cancelForm"
        data-role="cancel"
        type="reset"
        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"

>'.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'</button>
';},),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return '<div class="flow-board">
'.((LCRun3::ifvar($cx, ((isset($in['undo']['possible']) && is_array($in['undo'])) ? $in['undo']['possible'] : null))) ? '		<p>'.LCRun3::ch($cx, 'l10n', array(array('flow-undo-edit-content'),array()), 'encq').'</p>
' : '		<p class="error">'.LCRun3::ch($cx, 'l10n', array(array('flow-undo-edit-failure'),array()), 'encq').'</p>
').'
'.LCRun3::p($cx, 'flow_errors', array(array($in),array())).'
'.((LCRun3::ifvar($cx, ((isset($in['undo']['possible']) && is_array($in['undo'])) ? $in['undo']['possible'] : null))) ? '		'.LCRun3::ch($cx, 'diffUndo', array(array(((isset($in['undo']['diff_content']) && is_array($in['undo'])) ? $in['undo']['diff_content'] : null)),array()), 'encq').'
' : '').'
	<form method="POST" action="'.htmlentities((string)((isset($in['links']['undo-edit-post']['url']) && is_array($in['links']['undo-edit-post'])) ? $in['links']['undo-edit-post']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-post">
		<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['rootBlock']['editToken']) && is_array($cx['sp_vars']['root']['rootBlock'])) ? $cx['sp_vars']['root']['rootBlock']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topic_prev_revision" value="'.htmlentities((string)((isset($in['current']['revisionId']) && is_array($in['current'])) ? $in['current']['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
		<input type="hidden" name="topic_postId" value="'.htmlentities((string)((isset($in['current']['postId']) && is_array($in['current'])) ? $in['current']['postId'] : null), ENT_QUOTES, 'UTF-8').'" />

		<textarea name="topic_content"
		          class="mw-ui-input"
		          data-role="content"
		          data-flow-preview-template="flow_post"
		          data-flow-preview-title="'.htmlentities((string)((isset($in['articleTitle']) && is_array($in)) ? $in['articleTitle'] : null), ENT_QUOTES, 'UTF-8').'"
		          data-flow-username="'.htmlentities((string)((isset($in['current']['creator']['name']) && is_array($in['current']['creator'])) ? $in['current']['creator']['name'] : null), ENT_QUOTES, 'UTF-8').'"
		>'.((LCRun3::ifvar($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null))) ? ''.htmlentities((string)((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.((LCRun3::ifvar($cx, ((isset($in['undo']['possible']) && is_array($in['undo'])) ? $in['undo']['possible'] : null))) ? ''.htmlentities((string)((isset($in['undo']['content']) && is_array($in['undo'])) ? $in['undo']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['current']['content']['content']) && is_array($in['current']['content'])) ? $in['current']['content']['content'] : null), ENT_QUOTES, 'UTF-8').'').'').'</textarea>

		<div class="flow-form-actions flow-form-collapsible">
			<button class="mw-ui-button mw-ui-constructive">'.LCRun3::ch($cx, 'l10n', array(array('flow-edit-post-submit'),array()), 'encq').'</button>
'.LCRun3::p($cx, 'flow_form_buttons', array(array($in),array())).'			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-edit'),array()), 'encq').'
			</small>
		</div>
	</form>
</div>

';
}
?>