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
),
        'blockhelpers' => array(),
        'hbhelpers' => array(            'ifCond' => 'Flow\TemplateHelper::ifCond',
),
        'partials' => array('flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LCRun3::ifvar($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null))) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LCRun3::sec($cx, ((isset($cx['sp_vars']['root']['errors']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['errors'] : null), $in, true, function($cx, $in)use($sp){return '				<li>'.LCRun3::ch($cx, 'html', array(array(((isset($in['message']) && is_array($in)) ? $in['message'] : null)),array()), 'encq').'</li>
'.$sp.'';}).'		</ul>
'.$sp.'	</div>
'.$sp.'' : '').'</div>
';},'flow_form_cancel_button' => function ($cx, $in, $sp) {return ''.$sp.'<button data-flow-interactive-handler="cancelForm"
'.$sp.'        data-role="cancel"
'.$sp.'        type="reset"
'.$sp.'        class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right flow-js"
'.$sp.'
'.$sp.'>
'.$sp.''.((LCRun3::ifvar($cx, ((isset($in['msg']) && is_array($in)) ? $in['msg'] : null))) ? ''.LCRun3::ch($cx, 'l10n', array(array(((isset($in['msg']) && is_array($in)) ? $in['msg'] : null)),array()), 'encq').'' : ''.LCRun3::ch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq').'').'</button>
';},),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return '<div class="flow-topic-summary-container">
	<div class="flow-topic-summary">
		<form class="flow-edit-form" data-flow-initial-state="collapsed" method="POST" action="'.htmlentities((string)((isset($in['revision']['actions']['summarize']['url']) && is_array($in['revision']['actions']['summarize'])) ? $in['revision']['actions']['summarize']['url'] : null), ENT_QUOTES, 'UTF-8').'">
'.LCRun3::p($cx, 'flow_errors', array(array($in),array()), '			').'			<input type="hidden" name="wpEditToken" value="'.htmlentities((string)((isset($in['editToken']) && is_array($in)) ? $in['editToken'] : null), ENT_QUOTES, 'UTF-8').'" />

'.((LCRun3::ifvar($cx, ((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null))) ? '				<input type="hidden" name="'.htmlentities((string)((isset($in['type']) && is_array($in)) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_prev_revision" value="'.htmlentities((string)((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null), ENT_QUOTES, 'UTF-8').'" />
' : '').'
			<div class="flow-editor">
				<textarea class="mw-ui-input"
				          name="'.htmlentities((string)((isset($in['type']) && is_array($in)) ? $in['type'] : null), ENT_QUOTES, 'UTF-8').'_summary"
				          type="text"
				          placeholder="'.LCRun3::ch($cx, 'l10n', array(array('flow-edit-summary-placeholder'),array()), 'encq').'"
				          data-role="content"
				>'.((LCRun3::ifvar($cx, ((isset($in['submitted']['summary']) && is_array($in['submitted'])) ? $in['submitted']['summary'] : null))) ? ''.htmlentities((string)((isset($in['submitted']['summary']) && is_array($in['submitted'])) ? $in['submitted']['summary'] : null), ENT_QUOTES, 'UTF-8').'' : ''.((LCRun3::ifvar($cx, ((isset($in['revision']['revisionId']) && is_array($in['revision'])) ? $in['revision']['revisionId'] : null))) ? ''.htmlentities((string)((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null), ENT_QUOTES, 'UTF-8').'' : '').'').'</textarea>
			</div>

			<div class="flow-form-actions flow-form-collapsible">
				<button
					data-role="submit"
					class="mw-ui-button mw-ui-constructive"
					data-flow-interactive-handler="apiRequest"
					data-flow-api-handler="summarizeTopic"
					data-flow-api-target="< .flow-topic-summary-container">
						'.LCRun3::ch($cx, 'l10n', array(array('flow-topic-action-update-topic-summary'),array()), 'encq').'
				</button>

'.LCRun3::hbch($cx, 'ifCond', array(array(((isset($in['action']) && is_array($in)) ? $in['action'] : null),'===','summarize'),array()), $in, false, function($cx, $in) {return ''.LCRun3::p($cx, 'flow_form_cancel_button', array(array($in),array()), '					').'';}, function($cx, $in) {return ''.((!LCRun3::ifvar($cx, ((isset($in['submitted']['summary']) && is_array($in['submitted'])) ? $in['submitted']['summary'] : null))) ? ''.((!LCRun3::ifvar($cx, ((isset($in['revision']['content']['content']) && is_array($in['revision']['content'])) ? $in['revision']['content']['content'] : null))) ? ''.LCRun3::p($cx, 'flow_form_cancel_button', array(array($in),array('msg'=>'flow-skip-summary')), '							').'' : '').'' : '').'';}).'				<small class="flow-terms-of-use plainlinks">'.LCRun3::ch($cx, 'l10nParse', array(array('flow-terms-of-use-summarize'),array()), 'encq').'</small>
			</div>
		</form>
	</div>
</div>
';
}
?>