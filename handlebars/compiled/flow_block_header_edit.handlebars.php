<?php
use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in = null, $options = null) {
    $helpers = array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'l10nParse' => 'Flow\TemplateHelper::l10nParse',
);
    $partials = array('flow_header_title' => function ($cx, $in, $sp) {return ''.$sp.'<h2 class="flow-board-header-title">
'.$sp.'	<span class="mw-ui-icon mw-ui-icon-before mw-ui-icon-speechBubbles"></span>
'.$sp.'	'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-board-header'),array()), 'encq', $in)).'
'.$sp.'</h2>
';},
'flow_errors' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-error-container">
'.$sp.''.((LR::ifvar($cx, (isset($cx['sp_vars']['root']['errors']) ? $cx['sp_vars']['root']['errors'] : null), false)) ? '	<div class="flow-errors errorbox">
'.$sp.'		<ul>
'.$sp.''.LR::sec($cx, (isset($cx['sp_vars']['root']['errors']) ? $cx['sp_vars']['root']['errors'] : null), null, $in, true, function($cx, $in)use($sp){return '				<li>'.LR::encq($cx, LR::hbch($cx, 'html', array(array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)),array()), 'encq', $in)).'</li>
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
'.$sp.''.((LR::ifvar($cx, ((is_array($in) && isset($in['msg'])) ? $in['msg'] : null), false)) ? ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array(((is_array($in) && isset($in['msg'])) ? $in['msg'] : null)),array()), 'encq', $in)).'' : ''.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-cancel'),array()), 'encq', $in)).'').'</button>
';});
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'jslen' => false,
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
        'partialid' => 0,
        'runtime' => '\LightnCandy\Runtime',
    );
    
    return '<div class="flow-board-header">
'.LR::p($cx, 'flow_header_title', array(array($in),array()),0, '	').'	<div class="flow-board-header-edit-view">
		<form method="POST" action="'.LR::encq($cx, ((isset($in['revision']['actions']['edit']) && is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['url'])) ? $in['revision']['actions']['edit']['url'] : null)).'" flow-api-action="edit-header" class="edit-header-form">
'.LR::p($cx, 'flow_errors', array(array($in),array()),0, '			').'			<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, (isset($cx['sp_vars']['root']['editToken']) ? $cx['sp_vars']['root']['editToken'] : null)).'" />
'.((LR::ifvar($cx, ((isset($in['revision']) && is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null), false)) ? '				<input type="hidden" name="header_prev_revision" value="'.LR::encq($cx, ((isset($in['revision']) && is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null)).'" />
' : '').'
			<div class="flow-editor">
				<textarea name="header_content"
				          class="mw-ui-input mw-editfont-'.LR::encq($cx, (isset($cx['sp_vars']['root']['editFont']) ? $cx['sp_vars']['root']['editFont'] : null)).'"
				          placeholder="'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-edit-header-placeholder'),array()), 'encq', $in)).'"
				          data-role="content"
				>'.((LR::ifvar($cx, ((isset($in['submitted']) && is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null), false)) ? ''.LR::encq($cx, ((isset($in['submitted']) && is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null)).'' : ''.LR::encq($cx, ((isset($in['revision']['content']) && is_array($in['revision']['content']) && isset($in['revision']['content']['content'])) ? $in['revision']['content']['content'] : null)).'').'</textarea>
			</div>

			<div class="flow-form-actions flow-form-collapsible">
				<button data-role="submit"
					class="mw-ui-button mw-ui-progressive">'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-edit-header-submit'),array()), 'encq', $in)).'</button>
'.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()),0, '				').'				<small class="flow-terms-of-use plainlinks">'.LR::encq($cx, LR::hbch($cx, 'l10nParse', array(array('flow-terms-of-use-edit'),array()), 'encq', $in)).'</small>
			</div>
		</form>
	</div>
</div>
';
};