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
    return '<a href="#collapser/full"    data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-compact flow-board-navigator-right flow-board-navigator-cap"><span class="wikiglyph wikiglyph-stripe-compact flow-ui-tooltip-target" title="'.LCRun3::ch($cx, 'l10n', Array('flow-toggle-topics-posts'), 'encq').'"></span></a>
<a href="#collapser/compact" data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-topics flow-board-navigator-right flow-board-navigator-cap"><span class="wikiglyph wikiglyph-stripe-toc flow-ui-tooltip-target" title="'.LCRun3::ch($cx, 'l10n', Array('flow-toggle-small-topics'), 'encq').'"></span></a>
<a href="#collapser/topics"  data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-full flow-board-navigator-right flow-board-navigator-cap"><span class="wikiglyph wikiglyph-stripe-expanded flow-ui-tooltip-target" title="'.LCRun3::ch($cx, 'l10n', Array('flow-toggle-topics'), 'encq').'"></span></a>
';
}
?>