function ($cx, $in, $sp) {return ''.$sp.'<div class="flow-board-header">
'.$sp.''.LR::p($cx, 'flow_header_title', array(array($in),array()),0, '	').'	<div class="flow-board-header-edit-view">
'.$sp.'		<form method="POST" action="'.LR::encq($cx, ((isset($in['revision']['actions']['edit']) && is_array($in['revision']['actions']['edit']) && isset($in['revision']['actions']['edit']['url'])) ? $in['revision']['actions']['edit']['url'] : null)).'" flow-api-action="edit-header" class="edit-header-form">
'.$sp.''.LR::p($cx, 'flow_errors', array(array($in),array()),0, '			').'			<input type="hidden" name="wpEditToken" value="'.LR::encq($cx, (isset($cx['sp_vars']['root']['editToken']) ? $cx['sp_vars']['root']['editToken'] : null)).'" />
'.$sp.''.((LR::ifvar($cx, ((isset($in['revision']) && is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null), false)) ? '				<input type="hidden" name="header_prev_revision" value="'.LR::encq($cx, ((isset($in['revision']) && is_array($in['revision']) && isset($in['revision']['revisionId'])) ? $in['revision']['revisionId'] : null)).'" />
'.$sp.'' : '').'
'.$sp.'			<div class="flow-editor">
'.$sp.'				<textarea name="header_content"
'.$sp.'				          class="mw-ui-input mw-editfont-'.LR::encq($cx, (isset($cx['sp_vars']['root']['editFont']) ? $cx['sp_vars']['root']['editFont'] : null)).'"
'.$sp.'				          placeholder="'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-edit-header-placeholder'),array()), 'encq', $in)).'"
'.$sp.'				          data-role="content"
'.$sp.'				>'.((LR::ifvar($cx, ((isset($in['submitted']) && is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null), false)) ? ''.LR::encq($cx, ((isset($in['submitted']) && is_array($in['submitted']) && isset($in['submitted']['content'])) ? $in['submitted']['content'] : null)).'' : ''.LR::encq($cx, ((isset($in['revision']['content']) && is_array($in['revision']['content']) && isset($in['revision']['content']['content'])) ? $in['revision']['content']['content'] : null)).'').'</textarea>
'.$sp.'			</div>
'.$sp.'
'.$sp.'			<div class="flow-form-actions flow-form-collapsible">
'.$sp.'				<button data-role="submit"
'.$sp.'					class="mw-ui-button mw-ui-progressive">'.LR::encq($cx, LR::hbch($cx, 'l10n', array(array('flow-edit-header-submit'),array()), 'encq', $in)).'</button>
'.$sp.''.LR::p($cx, 'flow_form_cancel_button', array(array($in),array()),0, '				').'				<small class="flow-terms-of-use plainlinks">'.LR::encq($cx, LR::hbch($cx, 'l10nParse', array(array('flow-terms-of-use-edit'),array()), 'encq', $in)).'</small>
'.$sp.'			</div>
'.$sp.'		</form>
'.$sp.'	</div>
'.$sp.'</div>
';}