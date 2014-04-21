<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '					'.((LCRun2::ifvar(((is_array($in) && isset($in['time_ago'])) ? $in['time_ago'] : null))) ? '
						<time datetime="'.htmlentities(((is_array($in) && isset($in['time_iso'])) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp">
							<span class="flow-timestamp-now">'.htmlentities(((is_array($in) && isset($in['time_readable'])) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</span>
							<span id="'.htmlentities(((is_array($in) && isset($in['guid'])) ? $in['guid'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp-ago">'.htmlentities(((is_array($in) && isset($in['time_ago'])) ? $in['time_ago'] : null), ENT_QUOTES, 'UTF-8').'</span>
						</time>
					' : '
						<time datetime="'.htmlentities(((is_array($in) && isset($in['time_iso'])) ? $in['time_iso'] : null), ENT_QUOTES, 'UTF-8').'" class="flow-timestamp">'.htmlentities(((is_array($in) && isset($in['time_readable'])) ? $in['time_readable'] : null), ENT_QUOTES, 'UTF-8').'</time>
					').'
';
}
?>