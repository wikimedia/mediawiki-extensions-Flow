<?php return function ($in) {
    $cx = Array(
        'flags' => Array(
            'jstrue' => false,
            'jsobj' => false,
        ),
        'helpers' => Array(            'block' => 'Flow\TemplateHelper::block',
            'formElement' => 'Flow\TemplateHelper::formElement',
),
        'blockhelpers' => Array(),
        'scopes' => Array($in),
        'path' => Array(),

    );
    return '					<div class="flow-component" data-flow-component="board" data-flow-id="'.LCRun2::enc(((is_array($in) && isset($in['workflow'])) ? $in['workflow'] : null), $cx).'">
						'.LCRun2::sec(((is_array($in) && isset($in['blocks'])) ? $in['blocks'] : null), $cx, $in, true, function($cx, $in) {return '
							'.LCRun2::ch('block', Array($in), 'enc', $cx).'
						';}).'

						<div class="flow-board">
							<p>wikicon: <span class="wikicon wikicon-cap-w"></span><span class="wikicon wikicon-lower-w"></span><span class="wikicon wikicon-magnifying-glass"></span><span class="wikicon wikicon-arrow-left"></span><span class="wikicon wikicon-tick"></span><span class="wikicon wikicon-x"></span><span class="wikicon wikicon-x-circle"></span><span class="wikicon wikicon-unstar"></span><span class="wikicon wikicon-star"></span><span class="wikicon wikicon-sun"></span><span class="wikicon wikicon-star-circle"></span><span class="wikicon wikicon-funnel"></span><span class="wikicon wikicon-eye"></span><span class="wikicon wikicon-eye-lid"></span><span class="wikicon wikicon-bookmark"></span><span class="wikicon wikicon-printer"></span><span class="wikicon wikicon-puzzle"></span><span class="wikicon wikicon-clock"></span><span class="wikicon wikicon-dice"></span><span class="wikicon wikicon-move"></span><span class="wikicon wikicon-gear"></span><span class="wikicon wikicon-ellipsis"></span><span class="wikicon wikicon-envelope"></span><span class="wikicon wikicon-pin"></span><span class="wikicon wikicon-stripe-compact"></span><span class="wikicon wikicon-stripe-toc"></span><span class="wikicon wikicon-stripe-expanded"></span><span class="wikicon wikicon-article"></span><span class="wikicon wikicon-article-check"></span><span class="wikicon wikicon-article-search"></span><span class="wikicon wikicon-trash"></span><span class="wikicon wikicon-trash-slash"></span><span class="wikicon wikicon-block"></span><span class="wikicon wikicon-block-slash"></span><span class="wikicon wikicon-flag"></span><span class="wikicon wikicon-flag-slash"></span><span class="wikicon wikicon-play"></span><span class="wikicon wikicon-stop"></span><span class="wikicon wikicon-lock"></span><span class="wikicon wikicon-user-bust"></span><span class="wikicon wikicon-user-smile"></span><span class="wikicon wikicon-user-sleep"></span><span class="wikicon wikicon-translate"></span><span class="wikicon wikicon-pencil"></span><span class="wikicon wikicon-pencil-revert"></span><span class="wikicon wikicon-pencil-lock"></span><span class="wikicon wikicon-speech-bubble"></span><span class="wikicon wikicon-speech-bubbles"></span><span class="wikicon wikicon-speech-bubble-add"></span><span class="wikicon wikicon-speech-bubble-smile"></span><span class="wikicon wikicon-link"></span><span class="wikicon wikicon-quotes"></span><span class="wikicon wikicon-quotes-add"></span><span class="wikicon wikicon-image"></span><span class="wikicon wikicon-image-lock"></span><span class="wikicon wikicon-image-add"></span><span class="wikicon wikicon-image-main-placeholder"></span><span class="wikicon wikicon-folder"></span><span class="wikicon wikicon-folder-main-placeholder"></span><span class="wikicon wikicon-wikitrail"></span></p>

							<p>Links: <a href="#" class="flow-ui-progressive">Progressive</a> <a href="#" class="flow-ui-regressive">Regressive</a> <a href="#" class="flow-ui-constructive">Constructive</a> <a href="#" class="flow-ui-destructive">Destructive</a></p>
							<p>Quiet Links: <a href="#" class="flow-ui-quiet flow-ui-progressive">Progressive</a> <a href="#" class="flow-ui-quiet flow-ui-regressive">Regressive</a> <a href="#" class="flow-ui-quiet flow-ui-constructive">Constructive</a> <a href="#" class="flow-ui-quiet flow-ui-destructive">Destructive</a> <a href="#" class="flow-ui-quiet">x</a></p>
							<p>Buttons: <button class="flow-ui-button flow-ui-progressive">Progressive</button> <a href="#" class="flow-ui-button flow-ui-regressive">Regressive</a> <a href="#" class="flow-ui-button flow-ui-constructive">Constructive</a> <a href="#" class="flow-ui-button flow-ui-destructive">Destructive</a> <a href="#" class="flow-ui-button">x</a></p>
							<p>Thin Buttons: <button class="flow-ui-button flow-ui-thin flow-ui-progressive">Progressive</button> <a href="#" class="flow-ui-button flow-ui-thin flow-ui-regressive">Regressive</a> <a href="#" class="flow-ui-button flow-ui-thin flow-ui-constructive">Constructive</a> <a href="#" class="flow-ui-button flow-ui-thin flow-ui-destructive">Destructive</a> <a href="#" class="flow-ui-button flow-ui-thin">x</a></p>
							<p>Quiet Buttons: <button class="flow-ui-button flow-ui-quiet flow-ui-progressive">Progressive</button> <a href="#" class="flow-ui-button flow-ui-quiet flow-ui-regressive">Regressive</a> <a href="#" class="flow-ui-button flow-ui-quiet flow-ui-constructive">Constructive</a> <a href="#" class="flow-ui-button flow-ui-quiet flow-ui-destructive">Destructive</a> <a href="#" class="flow-ui-button flow-ui-quiet">x</a></p>
							<p>Quiet+Thin Buttons: <button class="flow-ui-button flow-ui-quiet flow-ui-thin flow-ui-progressive">Progressive</button> <a href="#" class="flow-ui-button flow-ui-quiet flow-ui-thin flow-ui-regressive">Regressive</a> <a href="#" class="flow-ui-button flow-ui-quiet flow-ui-thin flow-ui-constructive">Constructive</a> <a href="#" class="flow-ui-button flow-ui-quiet flow-ui-thin flow-ui-destructive">Destructive</a> <a href="#" class="flow-ui-button flow-ui-quiet flow-ui-thin">x</a></p>
							<p>Sleeper Buttons: <button class="flow-ui-button flow-ui-sleeper flow-ui-progressive">Progressive</button> <a href="#" class="flow-ui-button flow-ui-sleeper flow-ui-regressive">Regressive</a> <a href="#" class="flow-ui-button flow-ui-sleeper flow-ui-constructive">Constructive</a> <a href="#" class="flow-ui-button flow-ui-sleeper flow-ui-destructive">Destructive</a> <a href="#" class="flow-ui-button flow-ui-sleeper">x</a></p>
							<p>Sleeper+Thin Buttons: <button class="flow-ui-button flow-ui-sleeper flow-ui-thin flow-ui-progressive">Progressive</button> <a href="#" class="flow-ui-button flow-ui-sleeper flow-ui-thin flow-ui-regressive">Regressive</a> <a href="#" class="flow-ui-button flow-ui-sleeper flow-ui-thin flow-ui-constructive">Constructive</a> <a href="#" class="flow-ui-button flow-ui-sleeper flow-ui-thin flow-ui-destructive">Destructive</a> <a href="#" class="flow-ui-button flow-ui-sleeper flow-ui-thin">x</a></p>

							<form style="background: #eeeaea; margin: 1em; padding: 1em; border-radius: 2px;" data-flow-initial-state="collapsed">
								<h5>formElement test -- to be removed</h5>
								<p>'.LCRun2::ch('formElement', Array($in,'text',((is_array($in) && isset($in['name="texty"'])) ? $in['name="texty"'] : null),((is_array($in) && isset($in['placeholder=\'text\''])) ? $in['placeholder=\'text\''] : null)), 'enc', $cx).'</p>
								<p>'.LCRun2::ch('formElement', Array($in,'number',((is_array($in) && isset($in['name="numnum"'])) ? $in['name="numnum"'] : null),((is_array($in) && isset($in['placeholder=\'number'])) ? $in['placeholder=\'number'] : null),((is_array($in) && isset($in['required\''])) ? $in['required\''] : null),((is_array($in) && isset($in['required=true'])) ? $in['required=true'] : null)), 'enc', $cx).''.LCRun2::ch('formElement', Array($in,'date',((is_array($in) && isset($in['name="dat"'])) ? $in['name="dat"'] : null),((is_array($in) && isset($in['placeholder=\'date'])) ? $in['placeholder=\'date'] : null),((is_array($in) && isset($in['required\''])) ? $in['required\''] : null),((is_array($in) && isset($in['required=true'])) ? $in['required=true'] : null)), 'enc', $cx).''.LCRun2::ch('formElement', Array($in,'time',((is_array($in) && isset($in['name="time"'])) ? $in['name="time"'] : null),((is_array($in) && isset($in['placeholder=\'time'])) ? $in['placeholder=\'time'] : null),((is_array($in) && isset($in['required\''])) ? $in['required\''] : null),((is_array($in) && isset($in['required=true'])) ? $in['required=true'] : null)), 'enc', $cx).''.LCRun2::ch('formElement', Array($in,'email',((is_array($in) && isset($in['name="email"'])) ? $in['name="email"'] : null),((is_array($in) && isset($in['placeholder=\'email'])) ? $in['placeholder=\'email'] : null),((is_array($in) && isset($in['required\''])) ? $in['required\''] : null),((is_array($in) && isset($in['required=true'])) ? $in['required=true'] : null)), 'enc', $cx).''.LCRun2::ch('formElement', Array($in,'url',((is_array($in) && isset($in['name="uri"'])) ? $in['name="uri"'] : null),((is_array($in) && isset($in['placeholder=\'url\''])) ? $in['placeholder=\'url\''] : null)), 'enc', $cx).''.LCRun2::ch('formElement', Array($in,'url',((is_array($in) && isset($in['name="pass"'])) ? $in['name="pass"'] : null),((is_array($in) && isset($in['placeholder=\'password'])) ? $in['placeholder=\'password'] : null),((is_array($in) && isset($in['required\''])) ? $in['required\''] : null),((is_array($in) && isset($in['required=true'])) ? $in['required=true'] : null)), 'enc', $cx).''.LCRun2::ch('formElement', Array($in,'search',((is_array($in) && isset($in['name="q"'])) ? $in['name="q"'] : null),((is_array($in) && isset($in['placeholder=\'search\''])) ? $in['placeholder=\'search\''] : null)), 'enc', $cx).''.''.'</p>
								<p>'.LCRun2::ch('formElement', Array($in,'textarea',((is_array($in) && isset($in['name="textyarea"'])) ? $in['name="textyarea"'] : null),((is_array($in) && isset($in['placeholder=\'textarea'])) ? $in['placeholder=\'textarea'] : null),((is_array($in) && isset($in['expandable'])) ? $in['expandable'] : null),((is_array($in) && isset($in['required\''])) ? $in['required\''] : null),((is_array($in) && isset($in['required=true'])) ? $in['required=true'] : null),((is_array($in) && isset($in['expandable=true'])) ? $in['expandable=true'] : null)), 'enc', $cx).'</p>
								<p>'.LCRun2::ch('formElement', Array($in,'radio',((is_array($in) && isset($in['name="rad"'])) ? $in['name="rad"'] : null),((is_array($in) && isset($in['value="1"'])) ? $in['value="1"'] : null),((is_array($in) && isset($in['content=\'radio-1\''])) ? $in['content=\'radio-1\''] : null)), 'enc', $cx).' '.LCRun2::ch('formElement', Array($in,'radio',((is_array($in) && isset($in['name="rad"'])) ? $in['name="rad"'] : null),((is_array($in) && isset($in['value="2"'])) ? $in['value="2"'] : null),((is_array($in) && isset($in['content=\'radio-2\''])) ? $in['content=\'radio-2\''] : null)), 'enc', $cx).' '.LCRun2::ch('formElement', Array($in,'checkbox',((is_array($in) && isset($in['name="checky"'])) ? $in['name="checky"'] : null),((is_array($in) && isset($in['content=\'checkbox\''])) ? $in['content=\'checkbox\''] : null)), 'enc', $cx).'</p>
								'.LCRun2::ch('formElement', Array($in,'submit',((is_array($in) && isset($in['content=\'submit'])) ? $in['content=\'submit'] : null),((is_array($in) && isset($in['(constructive)'])) ? $in['(constructive)'] : null),((is_array($in) && isset($in['button\''])) ? $in['button\''] : null)), 'enc', $cx).'
								'.LCRun2::ch('formElement', Array($in,'button',((is_array($in) && isset($in['role="action"'])) ? $in['role="action"'] : null),((is_array($in) && isset($in['content=\'action'])) ? $in['content=\'action'] : null),((is_array($in) && isset($in['(progressive)'])) ? $in['(progressive)'] : null),((is_array($in) && isset($in['button'])) ? $in['button'] : null),((is_array($in) && isset($in['+'])) ? $in['+'] : null),((is_array($in) && isset($in['thin\''])) ? $in['thin\''] : null)), 'enc', $cx).'
								'.LCRun2::ch('formElement', Array($in,'button',((is_array($in) && isset($in['role="regressive"'])) ? $in['role="regressive"'] : null),((is_array($in) && isset($in['content=\'regressive'])) ? $in['content=\'regressive'] : null),((is_array($in) && isset($in['button\''])) ? $in['button\''] : null)), 'enc', $cx).'
								'.LCRun2::ch('formElement', Array($in,'button',((is_array($in) && isset($in['role="cancel"'])) ? $in['role="cancel"'] : null),((is_array($in) && isset($in['content=\'cancel'])) ? $in['content=\'cancel'] : null),((is_array($in) && isset($in['(destructive)\''])) ? $in['(destructive)\''] : null)), 'enc', $cx).'
								<span class="flow-ui-tooltip flow-ui-tooltip-left">wow<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-tooltip-up">such tool<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-tooltip-right">many tip<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-tooltip-down">very cascading<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-constructive flow-ui-tooltip-left">wow<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-constructive flow-ui-tooltip-up">such tool<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-constructive flow-ui-tooltip-right">many tip<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-constructive flow-ui-tooltip-down">very cascading<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-destructive flow-ui-tooltip-left">wow<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-destructive flow-ui-tooltip-up">such tool<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-destructive flow-ui-tooltip-right">many tip<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-destructive flow-ui-tooltip-down">very cascading<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-progressive flow-ui-tooltip-left">wow<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-progressive flow-ui-tooltip-up">such tool<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-progressive flow-ui-tooltip-right">many tip<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-progressive flow-ui-tooltip-down">very cascading<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-regressive flow-ui-tooltip-left">wow<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-regressive flow-ui-tooltip-up">such tool<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-regressive flow-ui-tooltip-right">many tip<span class="flow-ui-tooltip-triangle"></span></span>
								<span class="flow-ui-tooltip flow-ui-regressive flow-ui-tooltip-down">very cascading<span class="flow-ui-tooltip-triangle"></span></span>
							</form>
						</div>
					</div>
';
}
?>