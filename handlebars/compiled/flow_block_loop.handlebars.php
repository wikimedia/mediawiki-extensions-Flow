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
        'helpers' => array(            'block' => 'Flow\TemplateHelper::block',
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array(),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
        'lcrun' => 'LCRun3',

    );
    
    return ''.LCRun3::sec($cx, ((isset($in['blocks']) && is_array($in)) ? $in['blocks'] : null), $in, true, function($cx, $in) {return '	'.LCRun3::ch($cx, 'block', array(array($in),array()), 'encq').'
';}).'<div class="flow-ui-load-overlay"></div>
<div style="clear: both"></div>
';
}
?>