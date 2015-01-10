<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'mustsec' => false,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<a href="#topics/full"    data-flow-interactive-handler="collapserGroupToggle" class="flow-board-collapser-compact flow-board-navigator-right flow-board-navigator-cap"><span class="wikiglyph wikiglyph-stripe-compact flow-ui-tooltip-target" title="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-toggle-topics-posts'),Array()), 'encq').'"></span></a>
<a href="#topics/compact" data-flow-interactive-handler="collapserGroupToggle" class="flow-board-collapser-topics flow-board-navigator-right flow-board-navigator-cap"><span class="wikiglyph wikiglyph-stripe-toc flow-ui-tooltip-target" title="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-toggle-small-topics'),Array()), 'encq').'"></span></a>
<a href="#topics/topics"  data-flow-interactive-handler="collapserGroupToggle" class="flow-board-collapser-full flow-board-navigator-right flow-board-navigator-cap"><span class="wikiglyph wikiglyph-stripe-expanded flow-ui-tooltip-target" title="'.LCRun3::ch($cx, 'l10n', Array(Array('flow-toggle-topics'),Array()), 'encq').'"></span></a>
';
}
?>
