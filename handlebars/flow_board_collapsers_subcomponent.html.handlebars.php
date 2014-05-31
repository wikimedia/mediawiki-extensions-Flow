<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<a href="#collapser/full"    data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-compact flow-board-navigator-right flow-board-navigator-cap"><span class="wikicon wikicon-stripe-compact flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('flow-toggle-topics-posts'), 'enc', $cx).'"></span></a>
<a href="#collapser/compact" data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-topics flow-board-navigator-right flow-board-navigator-cap"><span class="wikicon wikicon-stripe-toc flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('flow-toggle-small-topics'), 'enc', $cx).'"></span></a>
<a href="#collapser/topics"  data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-full flow-board-navigator-right flow-board-navigator-cap"><span class="wikicon wikicon-stripe-expanded flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('flow-toggle-topics'), 'enc', $cx).'"></span></a>
';
}
?>