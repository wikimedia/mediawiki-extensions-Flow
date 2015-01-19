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
),
        'blockhelpers' => array(),
        'hbhelpers' => array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
        'partials' => array('flow_errors' => function ($cx, $in) {return '<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
		<ul>
'.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in) {return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
';}).'		</ul>
	</div>
' : '').'</div>
';},'flow_edit_topic_title' => function ($cx, $in) {return '<form method="POST" action="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'">
'.LCRun3::p($cx, 'flow_errors', array(array($in),array())).'	<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
	<input type="hidden" name="topic_prev_revision" value="'.htmlentities((string)((isset($in['revisionId']) && is_array($in)) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
	<input name="topic_content" class="mw-ui-input" value="'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null))) ? ''.htmlentities((string)((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null), ENT_QUOTES, 'UTF-8').'').'" />
	<div class="flow-form-actions flow-form-collapsible">
		<button data-role="submit"
		        data-flow-api-handler="submitTopicTitle"
		        data-flow-api-target="< .flow-topic"
		        class="mw-ui-button mw-ui-constructive">'.LCRun3::ch($cx, 'l10n', array(array('flow-edit-title-submit'),array()), 'encq').'</button>

'.LCRun3::hbch($cx, 'progressiveEnhancement', array(array(),array()), $in, false, function($cx, $in) {return '			<button data-role="cancel"
			        type="reset"
			        data-flow-interactive-handler="cancelForm"
			        class="mw-ui-button mw-ui-destructive mw-ui-quiet">'.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'</button>
			<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-edit'),array()), 'encq').'</small>
';}).'	</div>
</form>
';},),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return '<div class="flow-board">

'.LCRun3::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), $in, true, function($cx, $in) {return ''.LCRun3::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_edit_topic_title', array(array($in),array())).'';}).'';}).'</div>
';
}
?>