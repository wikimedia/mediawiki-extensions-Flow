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
<<<<<<< HEAD
        'helpers' => Array(            'block' => 'Flow\TemplateHelper::block',
=======
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'html' => 'Flow\TemplateHelper::htmlHelper',
            'block' => 'Flow\TemplateHelper::block',
>>>>>>> 05c76de... Update wikiglyph to support inline text for screen readers on icons
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-component" data-flow-component="board" data-flow-id="'.htmlentities(((is_array($in) && isset($in['workflow'])) ? $in['workflow'] : null), ENT_QUOTES, 'UTF-8').'">
<<<<<<< HEAD
=======
	'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['watchable'])) ? $in['watchable'] : null))) ? '
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
		   class="mw-ui-constructive
		   '.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['isWatched'])) ? $in['isWatched'] : null))) ? 'flow-watch-link-unwatch' : 'mw-ui-quiet flow-watch-link-watch').'"
		   data-flow-api-handler="watchItem"
		   data-flow-api-target="< .flow-topic-watchlist"
		   data-flow-api-method="POST">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-star">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-unwatch'),Array()), 'encq').'</span>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').''.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<span class="wikiglyph wikiglyph-unstar">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-watch'),Array()), 'encq').'</span>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</a>
	</div>
' : '').'

>>>>>>> 05c76de... Update wikiglyph to support inline text for screen readers on icons
	'.LCRun3::sec($cx, ((is_array($in) && isset($in['blocks'])) ? $in['blocks'] : null), $in, true, function($cx, $in) {return '
	'.LCRun3::ch($cx, 'block', Array(Array($in),Array()), 'encq').'
';}).'
</div>';
}
?>