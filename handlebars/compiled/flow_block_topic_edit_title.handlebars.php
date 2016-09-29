<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' => array(),
        'helpers' => array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'partials' => array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in)use($sp){return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_edit_topic_title' => function ($cx, $in, $sp) {return ''.$sp.'<form method="POST" action="'.htmlentities((string)((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-edit-title-form">
'.$sp.''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '	').'	<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.'	<input type="hidden" name="topic_prev_revision" value="'.htmlentities((string)((isset($in['revisionId']) && is_array($in)) ? $in['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.'	<input name="topic_content" class="mw-ui-input" value="'.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null))) ? ''.htmlentities((string)((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities((string)((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null), ENT_QUOTES, 'UTF-8').'').'" />
'.$sp.'	<div class="flow-form-actions">
'.$sp.'		<button data-role="submit"
'.$sp.'		        class="mw-ui-button mw-ui-progressive">'.LCRun3::ch($cx, 'l10n', array(array('flow-edit-title-submit'),array()), 'encq').'</button>
'.$sp.'	</div>
'.$sp.'</form>
';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="flow-board">

'.LCRun3::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), $in, true, function($cx, $in) {return ''.LCRun3::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_edit_topic_title', array(array($in),array()), '			').'';}).'';}).'</div>
';
}
?>