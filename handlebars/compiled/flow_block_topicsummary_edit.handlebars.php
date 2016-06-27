use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array(            'ifCond' => 'Flow\TemplateHelper::ifCond',
);
    $partials = array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, ((isset($in['html']) && is_array($in)) ? $in['html'] : null)).'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},
'flow_form_cancel_button' => function ($cx, $in, $sp) {return ''.$sp.'<button data-flow-interactive-handler="cancelForm"
'.$sp.'        data-role="cancel"
'.$sp.'        type="reset"
'.$sp.'        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"
'.$sp.'
'.$sp.'>
'.$sp.''.((LR::ifvar($cx, ((isset($in['msg']) && is_array($in)) ? $in['msg'] : null), false)) ? ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'' : ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'').'</button>
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
    
    return '<div class="flow-topic-summary-container">
	<div class="flow-topic-summary">
		<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST" action="'.LR::encq($cx, ((isset($in['revision']['actions']['summarize']['url']) && is_array($in['revision']['actions']['summarize'])) ? $in['revision']['actions']['summarize']['url'] : null)).'">
'.LR::p($cx, 'flow_errors', array(array($in),array()), '			').'			<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, ((isset($in['editToken']) && is_array($in)) ? $in['editToken'] : null)).'" />

'.((LR::ifvar($cx, ((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null), false)) ? '				<input type="hidden" name="'.LR::encq($cx, ((isset($in['type']) && is_array($in)) ? $in['type'] : null)).'_prev_revision" value="'.LR::encq($cx, ((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null)).'" />
' : '').'
			<div class="flow-editor">
				<textarea class="mw-ui-input"
				          name="'.LR::encq($cx, ((isset($in['type']) && is_array($in)) ? $in['type'] : null)).'_summary"
				          type="text"
				          placeholder="'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'"
				          data-role="content"
				>'.((LR::ifvar($cx, ((isset($in['submitted']['summary']) && is_array($in['submitted'])) ? $in['submitted']['summary'] : null), false)) ? ''.LR::encq($cx, ((isset($in['submitted']['summary']) && is_array($in['submitted'])) ? $in['submitted']['summary'] : null)).'' : ''.((LR::ifvar($cx, ((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null), false)) ? ''.LR::encq($cx, ((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null)).'' : '').'').'</textarea>
			</div>

			<div class="flow-form-actions flow-form-collapsible">
				<button
					data-role="submit"
					class="mw-ui-button mw-ui-constructive"
					data-flow-api-target="< .flow-topic-summary-container">
						'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
				</button>

'.LR::hbch($cx, 'ifCond', array(array(((isset($in['action']) && is_array($in)) ? $in['action'] : null),'===','summarize'),array()), $in, false, function($cx, $in) {return ''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()), '					').'';}, function($cx, $in) {return ''.((!LR::ifvar($cx, ((isset($in['submitted']['summary']) && is_array($in['submitted'])) ? $in['submitted']['summary'] : null), false)) ? ''.((!LR::ifvar($cx, ((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null), false)) ? ''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array('msg'=>'flow-skip-summary')), '							').'' : '').'' : '').'';}).'				<small class="flow-terms-of-use plainlinks">'.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'</small>
			</div>
		</form>
	</div>
</div>
';
};