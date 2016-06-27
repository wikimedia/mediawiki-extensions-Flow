use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in, $options = null) {
    $helpers = array();
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
';},
'flow_topic_titlebar_lock' => function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board">
'.$sp.'	<div class="flow-topic-summary-container">
'.$sp.'		<div class="flow-topic-summary">
'.$sp.'			<form class="flow-edit-form" data-flow-initial-state="expanded" method="POST"
'.$sp.'				  action="'.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? ''.LR::encq($cx, ((isset($in['actions']['unlock']['url']) && is_array($in['actions']['unlock'])) ? $in['actions']['unlock']['url'] : null)).'' : ''.LR::encq($cx, ((isset($in['actions']['lock']['url']) && is_array($in['actions']['lock'])) ? $in['actions']['lock']['url'] : null)).'').'">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()), '				').'				<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, ((isset($cx['sp_vars']['root']['editToken']) && is_array($cx['sp_vars']['root'])) ? $cx['sp_vars']['root']['editToken'] : null)).'" />
'.$sp.'				<input type="hidden" name="flow_reason" value="'.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'' : ''.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'').'" />
'.$sp.'				<div class="flow-form-actions flow-form-collapsible">
'.$sp.'					<button data-role="submit"
'.$sp.'					        class="mw-ui-button mw-ui-constructive"
'.$sp.'					>
'.$sp.''.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? '							'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
'.$sp.'' : '							'.LR::encq($cx, ((isset($in['l10n']) && is_array($in)) ? $in['l10n'] : null)).'
'.$sp.'').'					</button>
'.$sp.''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()), '					').'					<small class="flow-terms-of-use plainlinks">
'.$sp.''.((LR::ifvar($cx, ((isset($in['isLocked']) && is_array($in)) ? $in['isLocked'] : null), false)) ? '							'.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'
'.$sp.'' : '							'.LR::encq($cx, ((isset($in['l10nParse']) && is_array($in)) ? $in['l10nParse'] : null)).'
'.$sp.'').'					</small>
'.$sp.'				</div>
'.$sp.'			</form>
'.$sp.'		</div>
'.$sp.'	</div>
'.$sp.'</div>
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
    
    return ''.LR::p($cx, 'flow_topic_titlebar_lock', array(array($in),array())).'
';
};