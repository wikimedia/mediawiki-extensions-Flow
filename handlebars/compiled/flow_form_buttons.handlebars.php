<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<button data-flow-api-handler="preview"
        data-flow-api-target="< form textarea"
        name="preview"
        data-role="action"
        class="mw-ui-button mw-ui-progressive mw-ui-quiet mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-preview'),Array()), 'encq').'</button>
<button data-flow-interactive-handler="cancelForm" data-role="cancel"
	class="mw-ui-button mw-ui-destructive mw-ui-quiet mw-ui-flush-right">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-cancel'),Array()), 'encq').'</button>
';
}
?>