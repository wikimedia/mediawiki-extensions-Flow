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
),
        'blockhelpers' => Array(),
        'hbhelpers' => Array(),
        'scopes' => Array($in),
        'sp_vars' => Array(),

    );
    return ''.'
<div id="flow-post-'.htmlentities(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), ENT_QUOTES, 'UTF-8').'">
	<div class="flow-post-main">
		<div class="error">'.LCRun3::ch($cx, 'l10n', Array(Array(((is_array($in) && isset($in['\'flow-stub-post-content\''])) ? $in['\'flow-stub-post-content\''] : null)),Array()), 'encq').'</div>
	</div>
</div>
';
}
?>