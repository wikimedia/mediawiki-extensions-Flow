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
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'historyTimestamp' => 'Flow\TemplateHelper::historyTimestamp',
            'historyDescription' => 'Flow\TemplateHelper::historyDescription',
            'showCharacterDifference' => 'Flow\TemplateHelper::showCharacterDifference',
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'partials' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return '<div class="flow-board">
	<div class="flow-topic-histories">
		<ul>
			'.LCRun3::sec($cx, ((isset($in['revisions']) && is_array($in)) ? $in['revisions'] : null), $in, true, function($cx, $in) {return '
				<li>
					<span class="flow-pipelist">'.htmlentities((string)((isset($in['noop']) && is_array($in)) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<span>'.((LCRun3::ifvar($cx, ((isset($in['links']['diff-cur']) && is_array($in['links'])) ? $in['links']['diff-cur'] : null))) ? '<a href="'.htmlentities((string)((isset($in['links']['diff-cur']['url']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities((string)((isset($in['links']['diff-cur']['title']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['links']['diff-cur']['title']) && is_array($in['links']['diff-cur'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun3::ch($cx, 'l10n', Array(Array('cur'),Array()), 'encq').'
							').'
						</span>
						<span>
							'.((LCRun3::ifvar($cx, ((isset($in['links']['diff-prev']) && is_array($in['links'])) ? $in['links']['diff-prev'] : null))) ? '
								<a href="'.htmlentities((string)((isset($in['links']['diff-prev']['url']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities((string)((isset($in['links']['diff-prev']['title']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['links']['diff-prev']['title']) && is_array($in['links']['diff-prev'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun3::ch($cx, 'l10n', Array(Array('last'),Array()), 'encq').'
							').'</span>'.((LCRun3::ifvar($cx, ((isset($in['links']['topic']) && is_array($in['links'])) ? $in['links']['topic'] : null))) ? '
							<span><a href="'.htmlentities((string)((isset($in['links']['topic']['url']) && is_array($in['links']['topic'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities((string)((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)((isset($in['links']['topic']['title']) && is_array($in['links']['topic'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'</a></span>' : '').'</span>

					'.LCRun3::ch($cx, 'historyTimestamp', Array(Array($in),Array()), 'encq').'

					<span class="mw-changeslist-separator">. .</span>
					'.LCRun3::ch($cx, 'historyDescription', Array(Array($in),Array()), 'encq').'

					'.((LCRun3::ifvar($cx, ((isset($in['size']) && is_array($in)) ? $in['size'] : null))) ? '
						<span class="mw-changeslist-separator">. .</span>
						'.LCRun3::ch($cx, 'showCharacterDifference', Array(Array(((isset($in['size']['old']) && is_array($in['size'])) ? $in['size']['old'] : null),((isset($in['size']['new']) && is_array($in['size'])) ? $in['size']['new'] : null)),Array()), 'encq').'
					' : '').'

					<span class="flow-history-moderation-action">
						'.((LCRun3::ifvar($cx, ((isset($in['actions']['hide']) && is_array($in['actions'])) ? $in['actions']['hide'] : null))) ? '
							(<a href="'.htmlentities((string)((isset($in['actions']['hide']['url']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
							   title="'.htmlentities((string)((isset($in['actions']['hide']['title']) && is_array($in['actions']['hide'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
							   data-flow-interactive-handler="moderationDialog"
							   data-template="flow_moderate_post"
							   data-role="hide"
							>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-hide-post'),Array()), 'encq').'</a>)
						' : '').'
						'.((LCRun3::ifvar($cx, ((isset($in['actions']['unhide']) && is_array($in['actions'])) ? $in['actions']['unhide'] : null))) ? '
							(<a href="'.htmlentities((string)((isset($in['actions']['unhide']['url']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
							   title="'.htmlentities((string)((isset($in['actions']['unhide']['title']) && is_array($in['actions']['unhide'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
							   data-flow-interactive-handler="moderationDialog"
							   data-template="flow_moderate_post"
							   data-role="unhide"
							>'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-restore-post'),Array()), 'encq').'</a>)
						' : '').'
					</span>
				</li>
			';}).'
		</ul>
	</div>
</div>
';
}
?>
