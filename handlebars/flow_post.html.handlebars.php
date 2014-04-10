<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'html' => 'Flow\TemplateHelper::html',
            'post' => 'Flow\TemplateHelper::post',
),
        'blockhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'path' => Array(),

    );
    return '				'.LCRun2::wi(((is_array($in) && isset($in['revision'])) ? $in['revision'] : null), $cx, $in, function($cx, $in) {return '
					<div class="flow-post">
						'.LCRun2::wi(((is_array($in) && isset($in['author'])) ? $in['author'] : null), $cx, $in, function($cx, $in) {return '
							<span class="flow-author"><a href="'.LCRun2::enc(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['url'])) ? $in['links']['contribs']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['links']['contribs']) && isset($in['links']['contribs']['title'])) ? $in['links']['contribs']['title'] : null), $cx).'" class="mw-userlink flow-ui-tooltip-target">'.LCRun2::enc(((is_array($in) && isset($in['name'])) ? $in['name'] : null), $cx).'</a> <span class="mw-usertoollinks">(<a href="'.LCRun2::enc(((is_array($in['links']['talk']) && isset($in['links']['talk']['url'])) ? $in['links']['talk']['url'] : null), $cx).'" class="new flow-ui-tooltip-target" title="'.LCRun2::enc(((is_array($in['links']['talk']) && isset($in['links']['talk']['title'])) ? $in['links']['talk']['title'] : null), $cx).'">'.LCRun2::ch('l10n', Array('Talk'), 'enc', $cx).'</a>'.LCRun2::ifv(((is_array($in['links']) && isset($in['links']['block'])) ? $in['links']['block'] : null), $cx, $in, function($cx, $in) {return ' | <a class="flow-ui-tooltip-target" href="'.LCRun2::enc(((is_array($in['links']['block']) && isset($in['links']['block']['url'])) ? $in['links']['block']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['links']['block']) && isset($in['links']['block']['title'])) ? $in['links']['block']['title'] : null), $cx).'">'.LCRun2::ch('l10n', Array('block'), 'enc', $cx).'</a>';}).')</span></span>
						';}).'
						<div class="flow-post-content">
							'.LCRun2::ch('html', Array(((is_array($in) && isset($in['content'])) ? $in['content'] : null)), 'enc', $cx).'
						</div>
						<div class="flow-post-meta">
							<span class="flow-post-meta-actions">
								<a href="#flow-post-'.LCRun2::enc(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), $cx).'-form-content" class="flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'</a>
								'.LCRun2::ifv(((is_array($in['actions']) && isset($in['actions']['edit'])) ? $in['actions']['edit'] : null), $cx, $in, function($cx, $in) {return '
									&#8226;
									<a href="'.LCRun2::enc(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['url'])) ? $in['actions']['edit']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['actions']['edit']) && isset($in['actions']['edit']['title'])) ? $in['actions']['edit']['title'] : null), $cx).'" class="flow-ui-regressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Edit'), 'enc', $cx).'</a>
								';}).'
							</span>
							'.LCRun2::ifv(((is_array($in) && isset($in['previousRevisionId'])) ? $in['previousRevisionId'] : null), $cx, $in, function($cx, $in) {return '
								<!--span class="wikicon wikicon-clock"></span--> '.LCRun2::ch('uuidTimestamp', Array(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null),'edited_ago'), 'enc', $cx).'
								&#8226;
							';}).'
							'.LCRun2::ch('uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'time_ago'), 'enc', $cx).'
						</div>

						<div class="flow-menu">
							<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikicon wikicon-ellipsis"></span></a></div>
							<ul class="flow-ui-button-container">
								'.LCRun2::ifv(((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null), $cx, $in, function($cx, $in) {return '
									<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']['actions']) && isset($in['actions']['actions']['hide'])) ? $in['actions']['actions']['hide'] : null), $cx).'"><span class="wikicon wikicon-eye-lid"></span> '.LCRun2::ch('l10n', Array('Hide'), 'enc', $cx).'</a></li>
								';}).'
								'.LCRun2::ifv(((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null), $cx, $in, function($cx, $in) {return '
									<li><a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), $cx).'"><span class="wikicon wikicon-trash-slash"></span> '.LCRun2::ch('l10n', Array('Delete'), 'enc', $cx).'</a></li>
								';}).'
								'.LCRun2::ifv(((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null), $cx, $in, function($cx, $in) {return '
									<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['title'])) ? $in['actions']['suppress']['title'] : null), $cx).'"><span class="wikicon wikicon-block-slash"></span> '.LCRun2::ch('l10n', Array('Suppress'), 'enc', $cx).'</a></li>
								';}).'
							</ul>
						</div>

						'.LCRun2::bch('eachPost', Array(((is_array($cx['scopes'][count($cx['scopes'])-1]) && isset($cx['scopes'][count($cx['scopes'])-1]['rootBlock'])) ? $cx['scopes'][count($cx['scopes'])-1]['rootBlock'] : null),((is_array($in) && isset($in['replies'])) ? $in['replies'] : null)), $cx, $in, function($cx, $in) {return '
							<!-- eachPost nested replies -->
							'.LCRun2::ch('post', Array(((is_array($cx['scopes'][count($cx['scopes'])-2]) && isset($cx['scopes'][count($cx['scopes'])-2]['rootBlock'])) ? $cx['scopes'][count($cx['scopes'])-2]['rootBlock'] : null),$in), 'enc', $cx).'
						';}).'
					</div>
				';}).'
';
}
?>