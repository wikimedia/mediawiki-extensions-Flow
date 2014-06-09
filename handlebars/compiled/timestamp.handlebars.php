<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return ''.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['time_ago'])) ? $in['time_ago'] : null))) ? '
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['guid'])) ? $in['guid'] : null))) ? '
		<time datetime="'.htmlentities(((is_array($in) && isset($in['time_iso'])) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp">
			<span class="flow-timestamp-now">'.htmlentities(((is_array($in) && isset($in['time_readable'])) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</span>
			<span id="'.htmlentities(((is_array($in) && isset($in['guid'])) ? $in['guid'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp-ago">'.htmlentities(((is_array($in) && isset($in['time_ago'])) ? $in['time_ago'] : null), ENT_QUOTES, 'UTF-8').'</span>
		</time>
	' : '
		<time datetime="'.htmlentities(((is_array($in) && isset($in['time_iso'])) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp flow-load-interactive" data-flow-load-handler="timestamp" data-time-str="'.htmlentities(((is_array($in) && isset($in['time_str'])) ? $in['time_str'] : null), ENT_QUOTES, 'UTF-8').'" data-time-ago-only="'.htmlentities(((is_array($in) && isset($in['time_ago_only'])) ? $in['time_ago_only'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in) && isset($in['time_readable'])) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</time>
	').'
' : '
	<time datetime="'.htmlentities(((is_array($in) && isset($in['time_iso'])) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp">'.htmlentities(((is_array($in) && isset($in['time_readable'])) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</time>
').'
';
}
?>