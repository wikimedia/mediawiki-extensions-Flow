<?php

namespace Flow\Tests\Import;

use Flow\Import\TemplateHelper;

class TemplateHelperTest extends \MediaWikiTestCase {

	public function removeFromHtmlDataProvider() {
		return array(
			array( // the template is NOT in the html
				'<body data-parsoid="{stuff}"><p name="asdf">hi there</p></body>',
				'I am not a real template',
				'<body data-parsoid="{stuff}"><p name="asdf">hi there</p></body>'
			),
			array( // the template IS in the html ONCE
				'<body data-parsoid="{stuff}"><p>hi there<span typeof="mw:Transclusion" data-mw=\'{"parts":[{"template":{"target":{"wt":"I am a template","href":"./Template:I_am_a_template"}}}]}\'></span></p></body>',
				'I am a template',
				'<body data-parsoid="{stuff}"><p>hi there</p></body>'
			),
			array( // the template IS in the html MANY TIMES
				'<body data-parsoid="{stuff}"><p>a<span typeof="mw:Transclusion" data-mw=\'{"parts":[{"template":{"target":{"wt":"I am a template","href":"./Template:I_am_a_template"}}}]}\'></span>b<span typeof="mw:Transclusion" data-mw=\'{"parts":[{"template":{"target":{"wt":"I am a template","href":"./Template:I_am_a_template"}}}]}\'></span>c</p></body>',
				'I am a template',
				'<body data-parsoid="{stuff}"><p>abc</p></body>'
			),
			array( // somewhat malformed data-mw
				'<body data-parsoid="{stuff}"><p>hi there<span typeof="mw:Transclusion" data-mw=\'{"parts":[{}]}\'></span></p></body>',
				'Template name',
				'<body data-parsoid="{stuff}"><p>hi there<span typeof="mw:Transclusion" data-mw=\'{"parts":[{}]}\'></span></p></body>'
			),
			array( // multinode template using 'about' attribute
				'<body data-parsoid="{stuff}">' .
					'<p>hi there</p>' .
					'<span typeof="mw:Transclusion" about="#mwt5" data-mw=\'{"parts":[{"template":{"target":{"wt":"I am a template","href":"./Template:I_am_a_template"}}}]}\'></span>' .
					'<span about="#mwt5">random sibling node</span>' .
					'<span about="#mwt5">and then another one</span>' .
				'</body>',
				'I am a template',
				'<body data-parsoid="{stuff}"><p>hi there</p></body>'
			)
		);
	}

	/**
	 * @dataProvider removeFromHtmlDataProvider
	 */
	public function testRemoveFromHtml( $originalHtml, $templateToRemove, $expectedHtml ) {
		$actualHTML = TemplateHelper::removeFromHtml( $originalHtml, $templateToRemove );
		$this->assertEquals(
			$expectedHtml,
			$actualHTML );
	}

}