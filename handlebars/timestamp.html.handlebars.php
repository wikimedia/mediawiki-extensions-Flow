<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
        ),
        'helpers' => Array(),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'path' => Array(),

    );
    return '					'.((LCRun2::ifvar(((is_array($in) && isset($in['time_ago'])) ? $in['time_ago'] : null))) ? '
						<time datetime="'.LCRun2::enc(((is_array($in) && isset($in['time_iso'])) ? $in['time_iso'] : null), $cx).'" class="flow-timestamp">
							<span class="flow-timestamp-now">'.LCRun2::enc(((is_array($in) && isset($in['time_readable'])) ? $in['time_readable'] : null), $cx).'</span>
							<span id="'.LCRun2::enc(((is_array($in) && isset($in['guid'])) ? $in['guid'] : null), $cx).'" class="flow-timestamp-ago">'.LCRun2::enc(((is_array($in) && isset($in['time_ago'])) ? $in['time_ago'] : null), $cx).'</span>
						</time>
					' : '
						<time datetime="'.LCRun2::enc(((is_array($in) && isset($in['time_iso'])) ? $in['time_iso'] : null), $cx).'" class="flow-timestamp">'.LCRun2::enc(((is_array($in) && isset($in['time_readable'])) ? $in['time_readable'] : null), $cx).'</time>
					').'
';
}
?>