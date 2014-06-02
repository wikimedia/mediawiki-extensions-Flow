<?php return function ($in, $debugopt = 1) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'debug' => $debugopt,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
            'historyDescription' => 'Flow\TemplateHelper::historyDescription',
            'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board-history">
	<ul>
		'.LCRun3::sec($cx, ((is_array($in) && isset($in['revisions'])) ? $in['revisions'] : null), $in, true, function($cx, $in) {return '
			<li>
				('.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['diff-cur'])) ? $in['links']['diff-cur'] : null))) ? '<a href="'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['url'])) ? $in['links']['diff-cur']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['title'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['title'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun3::ch($cx, 'l10n', Array('cur'), 'encq').'
					').'
					'.LCRun3::ch($cx, 'l10n', Array('pipe-separator'), 'encq').'
					'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['diff-prev'])) ? $in['links']['diff-prev'] : null))) ? '
						<a href="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['url'])) ? $in['links']['diff-prev']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun3::ch($cx, 'l10n', Array('last'), 'encq').'
					').''.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null))) ? '
						'.LCRun3::ch($cx, 'l10n', Array('pipe-separator'), 'encq').'
						<a href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : '').')

				'.LCRun3::ch($cx, 'historyTimestamp', Array($in), 'encq').'

				<span class="mw-changeslist-separator">. .</span>
				'.LCRun3::ch($cx, 'historyDescription', Array($in), 'encq').'

				'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['size'])) ? $in['size'] : null))) ? '
					<span class="mw-changeslist-separator">. .</span>
					'.LCRun3::ch($cx, 'showCharacterDifference', Array(((is_array($in['size']) && isset($in['size']['old'])) ? $in['size']['old'] : null),((is_array($in['size']) && isset($in['size']['new'])) ? $in['size']['new'] : null)), 'encq').'
				' : '').'
			</li>
		';}).'
	</ul>
</div>
';
}
?>