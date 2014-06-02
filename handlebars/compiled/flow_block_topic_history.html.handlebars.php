<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
            'historyDescription' => 'Flow\TemplateHelper::historyDescription',
            'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),
        'path' => Array(),

    );
    return '<div class="flow-board">
	<div class="flow-topic-histories">
		<ul>
			'.LCRun2::sec(((is_array($in) && isset($in['revisions'])) ? $in['revisions'] : null), $cx, $in, true, function($cx, $in) {return '
				<li>
					('.((LCRun2::ifvar(((is_array($in['links']) && isset($in['links']['diff-cur'])) ? $in['links']['diff-cur'] : null))) ? '<a href="'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['url'])) ? $in['links']['diff-cur']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['title'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['title'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun2::ch('l10n', Array('cur'), 'enc', $cx).'
						').'
						'.LCRun2::ch('l10n', Array('pipe-separator'), 'enc', $cx).'
						'.((LCRun2::ifvar(((is_array($in['links']) && isset($in['links']['diff-prev'])) ? $in['links']['diff-prev'] : null))) ? '
							<a href="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['url'])) ? $in['links']['diff-prev']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun2::ch('l10n', Array('last'), 'enc', $cx).'
						').''.((LCRun2::ifvar(((is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null))) ? '
							'.LCRun2::ch('l10n', Array('pipe-separator'), 'enc', $cx).'
							<a href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : '').')

					'.LCRun2::ch('historyTimestamp', Array($in), 'enc', $cx).'

					<span class="mw-changeslist-separator">. .</span>
					'.LCRun2::ch('historyDescription', Array($in), 'enc', $cx).'

					'.((LCRun2::ifvar(((is_array($in) && isset($in['size'])) ? $in['size'] : null))) ? '
						<span class="mw-changeslist-separator">. .</span>
						'.LCRun2::ch('showCharacterDifference', Array(((is_array($in['size']) && isset($in['size']['old'])) ? $in['size']['old'] : null),((is_array($in['size']) && isset($in['size']['new'])) ? $in['size']['new'] : null)), 'enc', $cx).'
					' : '').'
				</li>
			';}).'
		</ul>
	</div>
</div>
';
}
?>