<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
        ),
        'helpers' => Array(            'l10n' => 'Flow\TemplateHelper::l10n',
            'uuidTimestamp' => 'Flow\TemplateHelper::uuidTimestamp',
            'timestamp' => 'Flow\TemplateHelper::timestamp',
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
						'.((LCRun2::ifvar(((is_array($in['links']) && isset($in['links']['search'])) ? $in['links']['search'] : null))) ? '
							<a href="'.LCRun2::enc(((is_array($in['links']['search']) && isset($in['links']['search']['url'])) ? $in['links']['search']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['links']['search']) && isset($in['links']['search']['title'])) ? $in['links']['search']['title'] : null), $cx).'" class="flow-board-navigator-last"><span class="WikiFont WikiFont-magnifying-glass flow-ui-tooltip-target" title="Search"></span></a>
						' : '
							<!-- @todo make disabled class do something? -->
							<a href="#" class="flow-board-navigator-last disabled"><span class="WikiFont WikiFont-magnifying-glass flow-ui-tooltip-target" title="Search"></span></a>
						').'
						<div>
							
												<a href="#collapser/full"    data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-compact flow-board-navigator-right flow-board-navigator-cap"><span class="WikiFont WikiFont-stripe-compact flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('Toggle_topics_and_posts'), 'enc', $cx).'"></span></a>
					<a href="#collapser/compact" data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-topics flow-board-navigator-right flow-board-navigator-cap"><span class="WikiFont WikiFont-stripe-toc flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('Toggle_small_topics'), 'enc', $cx).'"></span></a>
					<a href="#collapser/topics"  data-flow-interactive-handler="collapserToggle" class="flow-board-collapser-full flow-board-navigator-right flow-board-navigator-cap"><span class="WikiFont WikiFont-stripe-expanded flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('Toggle_topics_only'), 'enc', $cx).'"></span></a>


							
							<a href="'.LCRun2::enc(((is_array($in['links']) && isset($in['links']['unknown'])) ? $in['links']['unknown'] : null), $cx).'" class="flow-board-navigator-active flow-board-navigator-first flow-ui-tooltip-target" title="'.LCRun2::ch('l10n', Array('Sorting_tooltip'), 'enc', $cx).'">'.LCRun2::ch('l10n', Array('Newest_topics'), 'enc', $cx).'</a>
						</div>
					</div>


					<div class="flow-board">
											<ul class="flow-topic-navigation">
						<li class="flow-topic-navigation-heading"><h5>'.LCRun2::ch('l10n', Array('Topics_n',$in), 'enc', $cx).'</h5></li>
						'.LCRun2::bch('eachPost', Array($in,((is_array($in) && isset($in['roots'])) ? $in['roots'] : null)), $cx, $in, function($cx, $in) {return '
							<li class="flow-topic-navigator-wrap">
								<a href="#flow-topic-'.LCRun2::enc(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), $cx).'" class="flow-topic-navigator">
									'.LCRun2::ch('math', Array($cx['sp_vars']['index'],'+','1'), 'enc', $cx).'. '.LCRun2::enc(((is_array($in) && isset($in['content'])) ? $in['content'] : null), $cx).'
									<span class="flow-topic-navigator-meta">
										'.((LCRun2::ifvar(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null))) ? '
											'.LCRun2::ch('timestamp', Array(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null),'active_ago'), 'enc', $cx).'
										' : '
											'.LCRun2::ch('uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'started_ago'), 'enc', $cx).'
										').'
									</span>
								</a>
							</li>
						';}).'
						<li class="flow-topic-navigation-footer">
							'.LCRun2::ch('l10n', Array('topic_count_sidebar',$in), 'enc', $cx).'
							'.((LCRun2::ifvar(((is_array($in['links']) && isset($in['links']['pagination'])) ? $in['links']['pagination'] : null))) ? '
								<a href="'.LCRun2::enc(((is_array($in['links']['pagination']['load_more']) && isset($in['links']['pagination']['load_more']['url'])) ? $in['links']['pagination']['load_more']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['links']['pagination']['load_more']) && isset($in['links']['pagination']['load_more']['title'])) ? $in['links']['pagination']['load_more']['title'] : null), $cx).'" class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin"><span class="WikiFont WikiFont-article"></span> '.LCRun2::ch('l10n', Array('Load_More'), 'enc', $cx).'</a>
							' : '
								<!-- @todo make disabled class do something? -->
								<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin disabled"><span class="WikiFont WikiFont-article"></span> '.LCRun2::ch('l10n', Array('Load_More'), 'enc', $cx).'</a>
							').'
						</li>
					</ul>

											<form class="flow-newtopic-form" data-flow-initial-state="collapsed">
						<input class="mw-ui-input" type="text" placeholder="'.LCRun2::ch('l10n', Array('Start_a_new_topic'), 'enc', $cx).'"/>
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
								<a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-inline" href="#flow-topic-'.LCRun2::enc(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), $cx).'-form-content">'.LCRun2::ch('l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'</a>
								&bull; '.LCRun2::ch('l10n', Array('comment_count',$in), 'enc', $cx).' &bull;
								'.((LCRun2::ifvar(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null))) ? '
									<!--span class="WikiFont WikiFont-speech-bubbles"></span--> '.LCRun2::ch('timestamp', Array(((is_array($in) && isset($in['last_updated'])) ? $in['last_updated'] : null),'active_ago'), 'enc', $cx).'
								' : '
									<!--span class="WikiFont WikiFont-speech-bubble"></span--> '.LCRun2::ch('uuidTimestamp', Array(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null),'started_ago'), 'enc', $cx).'
								').'
							</div>
							<span class="flow-reply-count"><span class="WikiFont WikiFont-speech-bubble"></span><span class="flow-reply-count-number">'.LCRun2::enc(((is_array($in) && isset($in['reply_count'])) ? $in['reply_count'] : null), $cx).'</span></span>

							<div class="flow-menu">
								<div class="flow-menu-js-drop"><a href="javascript:void(0);"><span class="WikiFont WikiFont-ellipsis"></span></a></div>
								<ul class="flow-ui-button-container">
									'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['lock'])) ? $in['actions']['lock'] : null))) ? '
										<li><a class="flow-ui-button flow-ui-progressive flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['url'])) ? $in['actions']['lock']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['actions']['lock']) && isset($in['actions']['lock']['title'])) ? $in['actions']['lock']['title'] : null), $cx).'"><span class="WikiFont WikiFont-lock"></span> '.LCRun2::ch('l10n', Array('Lock'), 'enc', $cx).'</a></li>
									' : '').'
									'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['hide'])) ? $in['actions']['hide'] : null))) ? '
										<li><a class="flow-ui-button flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['url'])) ? $in['actions']['hide']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['actions']['hide']) && isset($in['actions']['hide']['title'])) ? $in['actions']['hide']['title'] : null), $cx).'"><span class="WikiFont WikiFont-eye-lid"></span> '.LCRun2::ch('l10n', Array('Hide'), 'enc', $cx).'</a></li>
									' : '').'
									'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['delete'])) ? $in['actions']['delete'] : null))) ? '
										<li><a class="flow-ui-button flow-ui-regressive flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['url'])) ? $in['actions']['delete']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), $cx).'"><span class="WikiFont WikiFont-trash-slash"></span> '.LCRun2::ch('l10n', Array('Delete'), 'enc', $cx).'</a></li>
									' : '').'
									'.((LCRun2::ifvar(((is_array($in['actions']) && isset($in['actions']['suppress'])) ? $in['actions']['suppress'] : null))) ? '
										<li><a class="flow-ui-button flow-ui-destructive flow-ui-quiet flow-ui-thin" href="'.LCRun2::enc(((is_array($in['actions']['suppress']) && isset($in['actions']['suppress']['url'])) ? $in['actions']['suppress']['url'] : null), $cx).'" title="'.LCRun2::enc(((is_array($in['actions']['delete']) && isset($in['actions']['delete']['title'])) ? $in['actions']['delete']['title'] : null), $cx).'"><span class="WikiFont WikiFont-block-slash"></span> '.LCRun2::ch('l10n', Array('Suppress'), 'enc', $cx).'</a></li>
									' : '').'
								</ul>
							</div>
						</div>

						'.LCRun2::bch('eachPost', Array(reset($cx['scopes']),((is_array($in) && isset($in['replies'])) ? $in['replies'] : null)), $cx, $in, function($cx, $in) {return '
							<!-- eachPost topic -->
							'.LCRun2::ch('post', Array(reset($cx['scopes']),$in), 'enc', $cx).'
						';}).'

											<form class="flow-reply-form" data-flow-initial-state="collapsed">
						<textarea id="flow-post-'.LCRun2::enc(((is_array($in) && isset($in['postId'])) ? $in['postId'] : null), $cx).'-form-content" data-flow-expandable="true" class="mw-ui-input" type="text" placeholder="'.LCRun2::ch('l10n', Array('Reply_to_author_name',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'"></textarea>

						<div class="flow-form-actions flow-form-collapsible">
							<button data-role="submit" class="flow-ui-button flow-ui-constructive">'.LCRun2::ch('l10n', Array('Reply',((is_array($in) && isset($in['author'])) ? $in['author'] : null)), 'enc', $cx).'</button>
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