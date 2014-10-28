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
        'helpers' => Array(),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return ''.((LCRun3::ifvar($cx, ((isset($in['time_ago']) && is_array($in)) ? $in['time_ago'] : null))) ? '
	'.((LCRun3::ifvar($cx, ((isset($in['guid']) && is_array($in)) ? $in['guid'] : null))) ? '
		<time datetime="'.htmlentities((string)((isset($in['time_iso']) && is_array($in)) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp">
			<span class="flow-timestamp-now">'.htmlentities((string)((isset($in['time_readable']) && is_array($in)) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</span>
			<span id="'.htmlentities((string)((isset($in['guid']) && is_array($in)) ? $in['guid'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp-ago">'.htmlentities((string)((isset($in['time_ago']) && is_array($in)) ? $in['time_ago'] : null), ENT_QUOTES, 'UTF-8').'</span>
		</time>
	' : '
		<time datetime="'.htmlentities((string)((isset($in['time_iso']) && is_array($in)) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp flow-load-interactive" data-flow-load-handler="timestamp" data-time-str="'.htmlentities((string)((isset($in['time_str']) && is_array($in)) ? $in['time_str'] : null), ENT_QUOTES, 'UTF-8').'" data-time-ago-only="'.htmlentities((string)((isset($in['time_ago_only']) && is_array($in)) ? $in['time_ago_only'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['time_readable']) && is_array($in)) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</time>
	').'
' : '
	<time datetime="'.htmlentities((string)((isset($in['time_iso']) && is_array($in)) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp">'.htmlentities((string)((isset($in['time_readable']) && is_array($in)) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</time>
').'
';
}
?>