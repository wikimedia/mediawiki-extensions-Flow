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
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
            'linkWithReturnTo' => 'Flow\TemplateHelper::linkWithReturnTo',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
            'tooltip' => 'Flow\TemplateHelper::tooltip',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
),
        'partials' => array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in)use($sp){return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_anon_warning' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-anon-warning">
'.$sp.'	<div class="flow-anon-warning-mobile">
'.$sp.''.LCRun3::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in)use($sp){return ''.LCRun3::ch($cx, 'l10nParse', array(array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin'),array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin/signup'),array()), 'raw')),array()), 'encq').'';}).'	</div>
'.$sp.'
'.$sp.''.LCRun3::hbch($cx, 'progressiveEnhancement', array(array(),array()), $in, false, function($cx, $in)use($sp){return '		<div class="flow-anon-warning-desktop">
'.$sp.''.LCRun3::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in)use($sp){return ''.LCRun3::ch($cx, 'l10nParse', array(array('flow-anon-warning',LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin'),array()), 'raw'),LCRun3::ch($cx, 'linkWithReturnTo', array(array('Special:UserLogin/signup'),array()), 'raw')),array()), 'encq').'';}).'		</div>
'.$sp.'';}).'</div>
';},'flow_newtopic_form' => function ($cx, $in, $sp) {return ''.$sp.''.((LCRun3::ifvar($cx, ((isset($in['actions']['newtopic']) && is_array($in['actions'])) ? $in['actions']['newtopic'] : null))) ? '	<form action="'.htmlentities((string)((isset($in['actions']['newtopic']['url']) && is_array($in['actions']['newtopic'])) ? $in['actions']['newtopic']['url'] : null), ENT_QUOTES, 'UTF-8').'" method="POST" class="flow-newtopic-form">
'.$sp.''.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '		').'
'.$sp.''.LCRun3::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in)use($sp){return ''.LCRun3::p($cx, 'flow_anon_warning', array(array($in),array()), '			').'';}).'
'.$sp.'		<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.'		<input type="hidden" name="topiclist_replyTo" value="'.htmlentities((string)((isset($in['workflowId']) && is_array($in)) ? $in['workflowId'] : null), ENT_QUOTES, 'UTF-8').'" />
'.$sp.'		<input name="topiclist_topic" class="mw-ui-input mw-ui-input-large"
'.$sp.'			required
'.$sp.'			'.((LCRun3::ifvar($cx, ((isset($in['submitted']['topic']) && is_array($in['submitted'])) ? $in['submitted']['topic'] : null))) ? 'value="'.htmlentities((string)((isset($in['submitted']['topic']) && is_array($in['submitted'])) ? $in['submitted']['topic'] : null), ENT_QUOTES, 'UTF-8').'"' : '').'
'.$sp.'			type="text"
'.$sp.'			placeholder="'.LCRun3::ch($cx, 'l10n', array(array('flow-newtopic-start-placeholder'),array()), 'encq').'"
'.$sp.'			data-role="title"
'.$sp.'		/>
'.$sp.''.((!LCRun3::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null))) ? '			<div class="flow-editor">
'.$sp.'				<textarea name="topiclist_content"
'.$sp.'				          class="mw-ui-input flow-form-collapsible mw-editfont-'.htmlentities((string)((isset($cx['sp_vars']['root']['editFont']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editFont'] : null), ENT_QUOTES, 'UTF-8').'"
'.$sp.'				          placeholder="'.LCRun3::ch($cx, 'l10n', array(array('flow-newtopic-content-placeholder',((isset($cx['sp_vars']['root']['title']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['title'] : null)),array()), 'encq').'"
'.$sp.'				          data-role="content"
'.$sp.'				          required
'.$sp.'				>'.((LCRun3::ifvar($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null))) ? ''.htmlentities((string)((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'</textarea>
'.$sp.'			</div>
'.$sp.'			<div class="flow-form-actions flow-form-collapsible">
'.$sp.'				<button data-role="submit"
'.$sp.'					class="mw-ui-button mw-ui-progressive mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', array(array('flow-newtopic-save'),array()), 'encq').'</button>
'.$sp.'				<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-new-topic'),array()), 'encq').'</small>
'.$sp.'			</div>
'.$sp.'' : '').'	</form>
'.$sp.'' : '').'';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="flow-board flow-board-newtopic">
'.LCRun3::p($cx, 'flow_newtopic_form', array(array($in),array()), '	').'</div>
';
}
?>