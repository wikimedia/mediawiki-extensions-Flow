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
				<span class="flow-pipelist">'.htmlentities(((is_array($in) && isset($in['noop'])) ? $in['noop'] : null), ENT_QUOTES, 'UTF-8').'<span>'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['diff-cur'])) ? $in['links']['diff-cur'] : null))) ? '<a href="'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['url'])) ? $in['links']['diff-cur']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['title'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['diff-cur']) && isset($in['links']['diff-cur']['title'])) ? $in['links']['diff-cur']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun3::ch($cx, 'l10n', Array(Array('cur'),Array()), 'encq').'
						').'
					</span>
					<span>
						'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['diff-prev'])) ? $in['links']['diff-prev'] : null))) ? '
							<a href="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['url'])) ? $in['links']['diff-prev']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['diff-prev']) && isset($in['links']['diff-prev']['title'])) ? $in['links']['diff-prev']['title'] : null), ENT_QUOTES, 'UTF-8').'</a>' : ''.LCRun3::ch($cx, 'l10n', Array(Array('last'),Array()), 'encq').'
						').'</span>'.((LCRun3::ifvar($cx, ((is_array($in['links']) && isset($in['links']['topic'])) ? $in['links']['topic'] : null))) ? '
						<span><a href="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['url'])) ? $in['links']['topic']['url'] : null), ENT_QUOTES, 'UTF-8').'" title="'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'">'.htmlentities(((is_array($in['links']['topic']) && isset($in['links']['topic']['title'])) ? $in['links']['topic']['title'] : null), ENT_QUOTES, 'UTF-8').'</a></span>' : '').'</span>

				'.LCRun3::ch($cx, 'historyTimestamp', Array(Array($in),Array()), 'encq').'

				<span class="mw-changeslist-separator">. .</span>
				'.LCRun3::ch($cx, 'historyDescription', Array(Array($in),Array()), 'encq').'

				'.((LCRun3::ifvar($cx, ((is_array($in) && isset($in['size'])) ? $in['size'] : null))) ? '
					<span class="mw-changeslist-separator">. .</span>
					'.LCRun3::ch($cx, 'showCharacterDifference', Array(Array(((is_array($in['size']) && isset($in['size']['old'])) ? $in['size']['old'] : null),((is_array($in['size']) && isset($in['size']['new'])) ? $in['size']['new'] : null)),Array()), 'encq').'
				' : '').'


				<ul class="flow-history-moderation-menu">'.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '<li class="flow-history-moderation-action">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="mw-ui-anchor mw-ui-quiet"
							   href="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
							   title="'.htmlentities(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
							   data-flow-interactive-handler="moderationDialog"
							   data-template="flow_moderate_post"
							   data-role="hide">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-hide-post'),Array()), 'encq').'</a>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['unhide'])) ? $in['actions']['unhide'] : null))) ? '<li class="flow-history-moderation-action">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
							   href="'.htmlentities(((is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['url'])) ? $in['actions']['unhide']['url'] : null), ENT_QUOTES, 'UTF-8').'"
							   title="'.htmlentities(((is_array($in['actions']['unhide']) && isset($in['actions']['unhide']['title'])) ? $in['actions']['unhide']['title'] : null), ENT_QUOTES, 'UTF-8').'"
							   data-flow-interactive-handler="moderationDialog"
							   data-template="flow_moderate_post"
							   data-role="unhide">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-unhide-post'),Array()), 'encq').'</a>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '<li class="flow-history-moderation-action">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="mw-ui-anchor mw-ui-quiet"
							   href="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
							   title="'.htmlentities(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
							   data-flow-interactive-handler="moderationDialog"
							   data-template="flow_moderate_post"
							   data-role="delete">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-delete-post'),Array()), 'encq').'</a>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['undelete'])) ? $in['actions']['undelete'] : null))) ? '<li class="flow-history-moderation-action">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
							   href="'.htmlentities(((is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['url'])) ? $in['actions']['undelete']['url'] : null), ENT_QUOTES, 'UTF-8').'"
							   title="'.htmlentities(((is_array($in['actions']['undelete']) && isset($in['actions']['undelete']['title'])) ? $in['actions']['undelete']['title'] : null), ENT_QUOTES, 'UTF-8').'"
							   data-flow-interactive-handler="moderationDialog"
							   data-template="flow_moderate_post"
							   data-role="undelete">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-undelete-post'),Array()), 'encq').'</a>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '<li class="flow-history-moderation-action">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="mw-ui-anchor mw-ui-destructive mw-ui-quiet"
							   href="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
							   title="'.htmlentities(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['title'])) ? $in['actions']['suppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
							   data-flow-interactive-handler="moderationDialog"
							   data-template="flow_moderate_post"
							   data-role="suppress">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-suppress-post'),Array()), 'encq').'</a>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').''.((LCRun3::ifvar($cx, ((is_array($in['actions']) && isset($in['actions']['unsuppress'])) ? $in['actions']['unsuppress'] : null))) ? '<li class="flow-history-moderation-action">'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'<a class="mw-ui-anchor mw-ui-progressive mw-ui-quiet"
							   href="'.htmlentities(((is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['url'])) ? $in['actions']['unsuppress']['url'] : null), ENT_QUOTES, 'UTF-8').'"
							   title="'.htmlentities(((is_array($in['actions']['unsuppress']) && isset($in['actions']['unsuppress']['title'])) ? $in['actions']['unsuppress']['title'] : null), ENT_QUOTES, 'UTF-8').'"
							   data-flow-interactive-handler="moderationDialog"
							   data-template="flow_moderate_post"
							   data-role="unsuppress">'.LCRun3::ch($cx, 'l10n', Array(Array('flow-post-action-unsuppress-post'),Array()), 'encq').'</a>'.htmlentities(((is_array($in) && isset($in['null'])) ? $in['null'] : null), ENT_QUOTES, 'UTF-8').'</li>' : '').'</ul>
			</li>
		';}).'
	</ul>
</div>
';
}
?>