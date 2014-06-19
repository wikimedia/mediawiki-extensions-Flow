<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
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
        data-flow-preview-template="'.htmlentities(((is_array($in) && isset($in['templateName'])) ? $in['templateName'] : null), ENT_QUOTES, 'UTF-8').'"
        name="preview"
        data-role="action"
        class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun3::ch($cx, 'l10n', Array('flow-preview'), 'encq').'</button>';
}
?>