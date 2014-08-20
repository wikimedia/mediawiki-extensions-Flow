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
        'helpers' => Array(            'html' => 'Flow\TemplateHelper::htmlHelper',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return ''.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['watchable'])) ? $in['watchable'] : null))) ? '
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isBoardPage'])) ? $in['isBoardPage'] : null))) ? '
		<div class="flow-board-watch-link flow-watch-link">
			<div class="flow-error-container">
'.((LCRun3::ifvar($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null))) ? '
	<div class="flow-errors errorbox">
		<ul>
			'.LCRun3::sec($cx, ((is_array($cx['scopes'][0]) && isset($cx['scopes'][0]['errors'])) ? $cx['scopes'][0]['errors'] : null), $in, true, function($cx, $in) {return '
				<li>'.LCRun3::ch($cx, 'html', Array(Array(((is_array($in) && isset($in['message'])) ? $in['message'] : null)),Array()), 'encq').'</li>
			';}).'
		</ul>
	</div>
' : '').'
</div>

			<a href="'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isWatched'])) ? $in['isWatched'] : null))) ? ''.htmlentities(((is_array($in['links']['unwatch-board']) && isset($in['links']['unwatch-board']['url'])) ? $in['links']['unwatch-board']['url'] : null), ENT_QUOTES, 'UTF-8').'' : ''.htmlentities(((is_array($in['links']['watch-board']) && isset($in['links']['watch-board']['url'])) ? $in['links']['watch-board']['url'] : null), ENT_QUOTES, 'UTF-8').'').'"
			   class="mw-ui-anchor mw-ui-constructive '.((!LCRun3::ifvar($cx, ((is_array($in) && isset($in['isWatched'])) ? $in['isWatched'] : null))) ? 'mw-ui-quiet' : '').'"
			   data-flow-api-handler="watchItem"
			   data-flow-api-target="< .flow-topic-watchlist"
			   data-flow-api-method="POST">'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isWatched'])) ? $in['isWatched'] : null))) ? '<span class="wikiglyph wikiglyph-star"></span>' : '<span class="wikiglyph wikiglyph-unstar"></span>').'</a>
		</div>
	' : '').'
' : '').'
';
}
?>