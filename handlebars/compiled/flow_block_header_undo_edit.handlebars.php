function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board">
'.$sp.''.((LR::ifvar($cx, ((isset($in['undo']) && is_array($in['undo']) && isset($in['undo']['possible'])) ? $in['undo']['possible'] : null), false)) ? '		<p>'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-undo-edit-content'),array()), 'encq', $in)).'</p>
'.$sp.'' : '		<p class="error">'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-undo-edit-failure'),array()), 'encq', $in)).'</p>
'.$sp.'').'
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()),0, '	').'
'.$sp.''.((LR::ifvar($cx, ((isset($in['undo']) && is_array($in['undo']) && isset($in['undo']['possible'])) ? $in['undo']['possible'] : null), false)) ? '		'.LR::encq($cx, LR::hbch($cx, 'diffUndo', array(array(((isset($in['undo']) && is_array($in['undo']) && isset($in['undo']['diff_content'])) ? $in['undo']['diff_content'] : null)),array()), 'encq', $in)).'
'.$sp.'' : '').'
'.$sp.'	<form method="POST" action="'.LR::encq($cx, ((isset($in['links']['undo-edit-header']) && is_array($in['links']['undo-edit-header']) && isset($in['links']['undo-edit-header']['url'])) ? $in['links']['undo-edit-header']['url'] : null)).'" class="flow-post" data-module="header">
'.$sp.'		<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, (isset($cx['sp_vars']['root']['rootBlock']['editToken']) ? $cx['sp_vars']['root']['rootBlock']['editToken'] : null)).'" />
'.$sp.'		<input type="hidden" name="header_prev_revision" value="'.LR::encq($cx, ((isset($in['current']) && is_array($in['current']) && isset($in['current']['revisionId'])) ? $in['current']['revisionId'] : null)).'" />
'.$sp.'
'.$sp.'		<div class="flow-editor">
'.$sp.'			<textarea name="header_content" class="mw-ui-input mw-editfont-'.LR::encq($cx, (isset($cx['sp_vars']['root']['rootBlock']['editFont']) ? $cx['sp_vars']['root']['rootBlock']['editFont'] : null)).'" data-role="content">'.((LR::ifvar($cx, ((isset($in['submitted']) && is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null), false)) ? ''.LR::encq($cx, ((isset($in['submitted']) && is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null)).'' : ''.((LR::ifvar($cx, ((isset($in['undo']) && is_array($in['undo']) && isset($in['undo']['possible'])) ? $in['undo']['possible'] : null), false)) ? ''.LR::encq($cx, ((isset($in['undo']) && is_array($in['undo']) && isset($in['undo']['content'])) ? $in['undo']['content'] : null)).'' : ''.LR::encq($cx, ((isset($in['current']['content']) && is_array($in['current']['content']) && isset($in['current']['content']['content'])) ? $in['current']['content']['content'] : null)).'').'').'</textarea>
'.$sp.'		</div>
'.$sp.'
'.$sp.'		<div class="flow-form-actions flow-form-collapsible">
'.$sp.'			<button class="mw-ui-button mw-ui-progressive">'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-edit-header-submit'),array()), 'encq', $in)).'</button>
'.$sp.'			<small class="flow-terms-of-use plainlinks">'.LR::encq($cx, LR::hbch($cx, 'l10nParse', array(array('flow-terms-of-use-edit'),array()), 'encq', $in)).'
'.$sp.'			</small>
'.$sp.'		</div>
'.$sp.'	</form>
'.$sp.'</div>
'.$sp.'
';}