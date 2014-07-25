<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => false,
            'debug' => $debugopt,
        ),
        'helpers' => Array(),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board-watch-link flow-watch-link">
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isAlwaysWatched'])) ? $in['isAlwaysWatched'] : null))) ? '
		<span class="flow-ui-quiet flow-ui-constructive flow-ui-active">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-star"></span>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</span>
	' : '
		<a href="'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isWatched'])) ? $in['isWatched'] : null))) ? ''.htmlentities(((is_array($in['links']['unwatch-board']) && isset($in['links']['unwatch-board']['url'])) ? $in['links']['unwatch-board']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in['links']['watch-board']) && isset($in['links']['watch-board']['url'])) ? $in['links']['watch-board']['url'] : null), ENT_QUOTES, 'UTF-8').'').'"
		   class="flow-ui-quiet flow-ui-constructive'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isWatched'])) ? $in['isWatched'] : null))) ? 'flow-watch-link-unwatch flow-ui-active' : 'flow-watch-link-watch').'"
		   data-flow-api-handler="watchItem"
		   data-flow-api-target="< .flow-topic-watchlist"
		   data-flow-api-method="POST">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-star"></span>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').''.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-unstar"></span>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</a>
	').'
</div>';
}
?>