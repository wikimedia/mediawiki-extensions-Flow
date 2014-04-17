<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'timestamp' => 'Flow\TemplateHelper::timestamp',
            'html' => 'Flow\TemplateHelper::html',
            'math' => 'Flow\TemplateHelper::math',
            'post' => 'Flow\TemplateHelper::post',
),
        'blockhelpers' => Array(            'eachPost' => 'Flow\TemplateHelper::eachPost',
),
        'scopes' => Array($in),
        'path' => Array(),

    );
    return '					
					<div class="flow-board-navigation">
						<a href="'.LCRun2::enc(((is_array($in['links']['search']) && isset($in['links']['search']['url'])) ? $in['links']['search']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['links']['search']) && isset($in['links']['search']['title'])) ? $in['links']['search']['title'] : null), $cx).'" class="flow-board-navigator-last"><span class="wikicon wikicon-magnifying-glass flow-ui-tooltip-target" title="Search"></span></a>
						<div>
							'.LCRun2::enc(((is_array($in) && isset($in['progressiveEnhancement'])) ? $in['progressiveEnhancement'] : null), $cx).'"></span></a>
					<a href="#collapser/compact" data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-topics flow-board-navigator-right flow-board-navigator-cap"><span class="wikicon wikicon-stripe-toc flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('Toggle_small_topics'), 'enc', $cx).'"></span></a>
					<a href="#collapser/topics"  data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-full flow-board-navigator-right flow-board-navigator-cap"><span class="wikicon wikicon-stripe-expanded flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('Toggle_topics_only'), 'enc', $cx).'"></span></a>
"}}
							
							<a href="'.LCRun2::enc(((is_array($in['links']['board']) && isset($in['links']['board']['topics'])) ? $in['links']['board']['topics'] : null), $cx).'" class="flow-board-navigator-active flow-board-navigator-first flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array(((is_array($in) && isset($in['\'You'])) ? $in['\'You'] : null),((is_array($in) && isset($in['are'])) ? $in['are'] : null),((is_array($in) && isset($in['currently'])) ? $in['currently'] : null),((is_array($in) && isset($in['reading'])) ? $in['reading'] : null),((is_array($in) && isset($in['the'])) ? $in['the'] : null),((is_array($in) && isset($in['newest'])) ? $in['newest'] : null),((is_array($in) && isset($in['topics'])) ? $in['topics'] : null),((is_array($in) && isset($in['first'])) ? $in['first'] : null),((is_array($in) && isset($in['Click'])) ? $in['Click'] : null),((is_array($in) && isset($in['for'])) ? $in['for'] : null),((is_array($in) && isset($in['more'])) ? $in['more'] : null),((is_array($in) && isset($in['sorting'])) ? $in['sorting'] : null),((is_array($in['options']) && isset($in['options']['\''])) ? $in['options']['\''] : null)), 'enc', $cx).'">'.LCRun2::ch('l10n', Array('Newest_topics'), 'enc', $cx).'</a>
						</div>
					</div>


					<div class="flow-board">
											<ul class="flow-topic-navigation">
						<li class="flow-topic-navigation-heading"><h5>'.LCRun2::ch('l10n', Array('Topics_n',((is_array($in['topics']) && isset($in['topics']['length'])) ? $in['topics']['length'] : null)), 'enc', $cx).'</h5></li>
						'.LCRun2::bch('eachPost', Array($in,((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)), $cx, $in, function($cx, $in) {return '
								<li class="flow-topic-navigator-wrap"><a href="#flow-topic-'.LCRun2::enc(((is_array($in) && isset($in['id'])) ? $in['id'] : null), $cx).'" class="flow-topic-navigator">'.LCRun2::ch('math', Array($cx['sp_vars']['index'],'+',((is_array($in) && isset($in['1'])) ? $in['1'] : null)), 'enc', $cx).'. '.LCRun2::ch('html', Array(((is_array($in) && isset($in['content'])) ? $in['content'] : null)), 'enc', $cx).' <span class="flow-topic-navigator-meta">'.((LCRun2::ifvar(((is_array($in) && isset($in['update_time'])) ? $in['update_time'] : null))) ? ''.LCRun2::ch('timestamp', Array(((is_array($in) && isset($in['update_time'])) ? $in['update_time'] : null),'active_ago'), 'enc', $cx).'' : ''.LCRun2::ch('timestamp', Array(((is_array($in) && isset($in['start_time'])) ? $in['start_time'] : null),'started_ago'), 'enc', $cx).'').'</span></a></li>
						';}).'
						<li class="flow-topic-navigation-footer">
							'.LCRun2::ch('l10n', Array('topic_count_sidebar',$in), 'enc', $cx).'
							<a href="'.LCRun2::enc(((is_array($in['links']['pagination']['load_more']) && isset($in['links']['pagination']['load_more']['url'])) ? $in['links']['pagination']['load_more']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['links']['pagination']['load_more']) && isset($in['links']['pagination']['load_more']['title'])) ? $in['links']['pagination']['load_more']['title'] : null), $cx).'" class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin"><span class="wikicon wikicon-article"></span> '.LCRun2::ch('l10n', Array('Load_More'), 'enc', $cx).'</a>
						</li>
					</ul>

											<form class="flow-newtopic-form" data-flow-initial-state="collapsed">
						<input class="mw-ui-input" type="text" placeholder="'.LCRun2::ch('l10n', Array('Start_a_new_topic'), 'enc', $cx).'" required="required" />
						<textarea class="mw-ui-input flow-form-collapsible" placeholder="'.LCRun2::ch('l10n', Array('topic_details_placeholder'), 'enc', $cx).'"></textarea>

						<div class="flow-form-actions flow-form-collapsible">
							<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Add_Topic'), 'enc', $cx).'</button>
							<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Preview'), 'enc', $cx).'</button>
							<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Cancel'), 'enc', $cx).'</button>

							<small class="flow-terms-of-use plainlinks">'.LCRun2::ch('l10n', Array('topic_TOU'), 'enc', $cx).'</small>
						</div>
					</form>


						<div class="flow-topics">
							'.LCRun2::bch('eachPost', Array($in,((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)), $cx, $in, function($cx, $in) {return '
								<!-- eachPost topiclist -->
													<div class="flow-topic" id="flow-topic-'.LCRun2::enc(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), $cx).'">
						<div class="flow-topic-titlebar flow-click-interactive" data-flow-interactive-handler="topicCollapserToggle" tabindex="0">
							<h2 class="flow-topic-title">'.LCRun2::enc(((is_array($in) && isset($in['content'])) ? $in['content'] : null), $cx).'</h2>
							<span class="flow-author">'.LCRun2::ch('l10n', Array('started_with_participants',$in), 'enc', $cx).'</span>
							<div class="flow-topic-meta">
								<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-inline" href="#flow-topic-'.LCRun2::enc(((is_array($in) && isset($in['id'])) ? $in['id'] : null), $cx).'-form-content">'.LCRun2::ch('l10n', Array('Reply'), 'enc', $cx).'</a>
								&bull; '.LCRun2::ch('l10n', Array('comment_count',$in), 'enc', $cx).' &bull;
								'.((LCRun2::ifvar(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null))) ? '
									<!--span class="wikicon wikicon-speech-bubbles"></span--> '.LCRun2::ch('timestamp', Array(((is_array($in) && isset($in['lastUpdated'])) ? $in['lastUpdated'] : null),'active_ago'), 'enc', $cx).'
								' : '
									<!--span class="wikicon wikicon-speech-bubble"></span--> '.LCRun2::ch('uuidTimestamp', Array(((is_array($in) && isset($in['revisionId'])) ? $in['revisionId'] : null),'started_ago'), 'enc', $cx).'
								').'
							</div>
							<span class="flow-reply-count"><span class="wikicon wikicon-speech-bubble"></span><span class="flow-reply-count-number">'.LCRun2::enc(((is_array($in) && isset($in['reply_count'])) ? $in['reply_count'] : null), $cx).'</span></span>

							<div class="flow-menu">
								<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="wikicon wikicon-ellipsis"></span></a></div>
								<ul class="flow-ui-button-container">
									'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null))) ? '
										<li><a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null), $cx).'"><span class="wikicon wikicon-lock"></span> '.LCRun2::ch('l10n', Array('Lock'), 'enc', $cx).'</a></li>
									' : '').'
									'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
										<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null), $cx).'"><span class="wikicon wikicon-eye-lid"></span> '.LCRun2::ch('l10n', Array('Hide'), 'enc', $cx).'</a></li>
									' : '').'
									'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
										<li><a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null), $cx).'"><span class="wikicon wikicon-trash-slash"></span> '.LCRun2::ch('l10n', Array('Delete'), 'enc', $cx).'</a></li>
									' : '').'
									'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
										<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null), $cx).'"><span class="wikicon wikicon-block-slash"></span> '.LCRun2::ch('l10n', Array('Suppress'), 'enc', $cx).'</a></li>
									' : '').'
								</ul>
							</div>
						</div>

						'.LCRun2::bch('eachPost', Array(reset($cx['scopes']),((is_array($in) && isset($in['replies'])) ? $in['replies'] : null)), $cx, $in, function($cx, $in) {return '
							<!-- eachPost topic -->
							'.LCRun2::ch('post', Array(reset($cx['scopes']),$in), 'enc', $cx).'
						';}).'

											<form class="flow-reply-form" data-flow-initial-state="collapsed">
						<textarea id="flow-post-'.LCRun2::enc(((is_array($in) && isset($in['id'])) ? $in['id'] : null), $cx).'-form-content" data-flow-expandable="true" class="mw-ui-input" type="text" placeholder="'.LCRun2::ch('l10n', Array('Reply_to_author_name',$in), 'enc', $cx).'" required="required"></textarea>

						<div class="flow-form-actions flow-form-collapsible">
							<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Reply'), 'enc', $cx).'</button>
							<button data-role="action" class="flow-ui-button flow-ui-progressive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Preview'), 'enc', $cx).'</button>
							<button data-flow-interactive-handler="cancelForm" data-role="cancel" class="flow-ui-button flow-ui-destructive flow-ui-quiet">'.LCRun2::ch('l10n', Array('Cancel'), 'enc', $cx).'</button>

							<small class="flow-terms-of-use plainlinks">'.LCRun2::ch('l10n', Array('reply_TOU'), 'enc', $cx).'</small>
						</div>
					</form>

					</div>

							';}).'
						</div>
					</div>
';
}
?>