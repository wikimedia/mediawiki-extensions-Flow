use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'ifAnonymous' => 'Flow\TemplateHelper::ifAnonymous',
            'tooltip' => 'Flow\TemplateHelper::tooltip',
            'progressiveEnhancement' => 'Flow\TemplateHelper::progressiveEnhancement',
);
    $partials = array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, ((isset($in['html']) && is_array($in)) ? $in['html'] : null)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_anon_warning' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-anon-warning">
'.$sp.'	<div class="flow-anon-warning-mobile">
'.$sp.''.LR::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'down','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in)use($sp){return ''.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'';}).'	</div>
'.$sp.'
'.$sp.''.LR::hbch($cx, 'progressiveEnhancement', array(array(),array()), $in, false, function($cx, $in)use($sp){return '		<div class="flow-anon-warning-desktop">
'.$sp.''.LR::hbch($cx, 'tooltip', array(array(),array('positionClass'=>'left','contextClass'=>'progressive','extraClass'=>'flow-form-collapsible','isBlock'=>true)), $in, false, function($cx, $in)use($sp){return ''.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'';}).'		</div>
'.$sp.'';}).'</div>
';},
'flow_form_cancel_button' => function ($cx, $in, $sp) {return ''.$sp.'<button data-flow-interactive-handler="cancelForm"
'.$sp.'        data-role="cancel"
'.$sp.'        type="reset"
'.$sp.'        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"
'.$sp.'
'.$sp.'>
'.$sp.''.((LR::ifvar($cx, ((isset($in['msg']) && is_array($in)) ? $in['msg'] : null), false)) ? ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'' : ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'').'</button>
';},
'flow_newtopic_form' => function ($cx, $in, $sp) {return ''.$sp.''.((LR::ifvar($cx, ((isset($in['actions']['newtopic']) && is_array($in['actions'])) ? $in['actions']['newtopic'] : null), false)) ? '	<form action="'.LR::encq($cx, ((isset($in['actions']['newtopic']['url']) && is_array($in['actions']['newtopic'])) ? $in['actions']['newtopic']['url'] : null)).'" method="POST" class="flow-newtopic-form" data-flow-initial-state="'.((LR::ifvar($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null), false)) ? 'expanded' : 'collapsed').'">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '		').'
'.$sp.''.LR::hbch($cx, 'ifAnonymous', array(array(),array()), $in, false, function($cx, $in)use($sp){return ''.LR::p($cx, 'flow_anon_warning', array(array($in),array()), '			').'';}).'
'.$sp.'		<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, ((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null)).'" />
'.$sp.'		<input type="hidden" name="topiclist_replyTo" value="'.LR::encq($cx, ((isset($in['workflowId']) && is_array($in)) ? $in['workflowId'] : null)).'" />
'.$sp.'		<input name="topiclist_topic" class="mw-ui-input mw-ui-input-large"
'.$sp.'			required
'.$sp.'			'.((LR::ifvar($cx, ((isset($in['submitted']['topic']) && is_array($in['submitted'])) ? $in['submitted']['topic'] : null), false)) ? 'value="'.LR::encq($cx, ((isset($in['submitted']['topic']) && is_array($in['submitted'])) ? $in['submitted']['topic'] : null)).'"' : '').'
'.$sp.'			type="text"
'.$sp.'			placeholder="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'			data-role="title"
'.$sp.'		/>
'.$sp.'		<div class="flow-editor">
'.$sp.'			<textarea name="topiclist_content"
'.$sp.'			          class="mw-ui-input flow-form-collapsible mw-ui-input-large'.((LR::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null), false)) ? ' flow-form-collapsible-collapsed' : '').'"
'.$sp.'			          placeholder="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
'.$sp.'			          data-role="content"
'.$sp.'			          required
'.$sp.'			>'.((LR::ifvar($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null), false)) ? ''.LR::encq($cx, ((isset($in['submitted']['content']) && is_array($in['submitted'])) ? $in['submitted']['content'] : null)).'' : '').'</textarea>
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<div class="flow-form-actions flow-form-collapsible'.((LR::ifvar($cx, ((isset($in['isOnFlowBoard']) && is_array($in)) ? $in['isOnFlowBoard'] : null), false)) ? ' flow-form-collapsible-collapsed' : '').'">
'.$sp.'			<button data-role="submit"
'.$sp.'				class="mw-ui-button mw-ui-constructive mw-ui-flush-right">'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'</button>
'.$sp.''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()), '			').'			<small class="flow-terms-of-use plainlinks">'.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'</small>
'.$sp.'		</div>
'.$sp.'	</form>
'.$sp.'' : '').'';});
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
'.LR::p($cx, 'flow_newtopic_form', array(array($in),array()), '	').'</div>
';
};