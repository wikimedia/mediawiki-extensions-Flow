<?php use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
);
    $partials = array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, ((isset($in['html']) && is_array($in)) ? $in['html'] : null)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_edit_topic_title' => function ($cx, $in, $sp) {return ''.$sp.'<form method="POST" action="'.LR::encq($cx, ((isset($in['actions']['edit']['url']) && is_array($in['actions']['edit'])) ? $in['actions']['edit']['url'] : null)).'" class="flow-edit-title-form">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '	').'	<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, ((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null)).'" />
'.$sp.'	<input type="hidden" name="topic_prev_revision" value="'.LR::encq($cx, ((isset($in['revisionId']) && is_array($in)) ? $in['revisionId'] : null)).'" />
'.$sp.'	<input name="topic_content" class="mw-ui-input" value="'.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null), false)) ? ''.LR::encq($cx, ((isset($cx['sp_vars']['root']['submitted']['content']) && is_array($cx['sp_vars']['root']['submitted'])) ? $cx['sp_vars']['root']['submitted']['content'] : null)).'' : ''.LR::encq($cx, ((isset($in['content']['content']) && is_array($in['content'])) ? $in['content']['content'] : null)).'').'" />
'.$sp.'	<div class="flow-form-actions">
'.$sp.'		<button data-role="submit"
'.$sp.'		        class="mw-ui-button mw-ui-constructive">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</button>
'.$sp.'	</div>
'.$sp.'</form>
';});
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'lambda' => false,
            'mustlok' => false,
            'mustlam' => false,
            'echo' => false,
            'partnc' => false,
            'knohlp' => false,
            'debug' => isset($options['debug']) ? $options['debug'] : 1,
        ),
        'constants' => array(),
        'helpers' => isset($options['helpers']) ? array_merge($helpers, $options['helpers']) : $helpers,
        'partials' => isset($options['partials']) ? array_merge($partials, $options['partials']) : $partials,
        'scopes' => array(),
        'sp_vars' => isset($options['data']) ? array_merge(array('root' => $in), $options['data']) : array('root' => $in),
        'blparam' => array(),
        'runtime' => '\LightnCandy\Runtime',
    );
    
    return '<div class="flow-board">

'.LR::sec($cx, ((isset($in['roots']) && is_array($in)) ? $in['roots'] : null), null, $in, true, function($cx, $in) {return ''.LR::hbch($cx, 'eachPost', array(array(((isset($cx['sp_vars']['root']) && is_array($cx['sp_vars'])) ? $cx['sp_vars']['root'] : null),$in),array()), $in, false, function($cx, $in) {return ''.LR::p($cx, 'flow_edit_topic_title', array(array($in),array()), '			').'';}).'';}).'</div>
';
};
?>