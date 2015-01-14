<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'mustsec' => false,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' => array(),
        'helpers' => array(),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array(),
        'scopes' => array($in),
        'sp_vars' => array('root' => $in),

    );
    
    return ''.((LCRun3::ifvar($cx, ((isset($in['guid']) && is_array($in)) ? $in['guid'] : null))) ? '	<time datetime="'.htmlentities((string)((isset($in['time_iso']) && is_array($in)) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp">
' : '	<time datetime="'.htmlentities((string)((isset($in['time_iso']) && is_array($in)) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'"
	      class="flow-timestamp flow-load-interactive"
	      data-flow-load-handler="timestamp"
	      data-time-ago-only="'.htmlentities((string)((isset($in['time_ago_only']) && is_array($in)) ? $in['time_ago_only'] : null), ENT_QUOTES, 'UTF-8').'"
	>
').'	<span class="flow-timestamp-user-formatted">'.htmlentities((string)((isset($in['time_readable']) && is_array($in)) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</span>
	<span id="'.htmlentities((string)((isset($in['guid']) && is_array($in)) ? $in['guid'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp-ago">'.htmlentities((string)((isset($in['time_ago']) && is_array($in)) ? $in['time_ago'] : null), ENT_QUOTES, 'UTF-8').'</span>
</time>
';
}
?>