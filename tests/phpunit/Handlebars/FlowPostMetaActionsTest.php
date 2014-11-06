<?php

namespace Flow\Tests\Handlebars;

use DOMXPath;
use Flow\Container;
use Flow\Parsoid\Utils;
use Flow\TemplateHelper;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @group Flow
 */
class FlowPostMetaActionsTest extends \MediaWikiTestCase {

	/**
	 * The specific timestamps used inside are not anything
	 * in particular, they just match the post and last edit
	 * uuid's we use in the test.
	 */
	public function timestampEditedProvider() {
		return array(
			array(
				'never been edited',
				// expected
				'02:52, 1 October 2014',
				// args
				array(
					'isOriginalContent' => true,
					'author' => 'creator',
					'creator' => 'creator',
					'lastEditUser' => null,
				),
			),

			array(
				'last edited by post creator',
				// expected
				'Edited 04:21, 9 October 2014',
				// args
				array(
					'isOriginalContent' => false,
					'author' => 'creator',
					'creator' => 'creator',
					'lastEditUser' => 'creator',
				),
			),

			array(
				'last edited by other than post creator',
				// expected
				'Edited by author 04:21, 9 October 2014',
				// args
				array(
					'isOriginalContent' => false,
					'author' => 'author',
					'creator' => 'creator',
					'lastEditUser' => 'author',
				),
			),

			array(
				'most recent revision not a content edit',
				// expected
				'Edited 04:21, 9 October 2014',
				// args
				array(
					'isOriginalContent' => false,
					'author' => 'author',
					'creator' => 'creator',
					'lastEditUser' => 'creator',
				),
			),
		);
	}

	/**
	 * @dataProvider timestampEditedProvider
	 */
	public function testTimestampEdited( $message, $expect, $args ) {
		if ( !class_exists( 'Symfony\Component\DomCrawler\Crawler' ) ) {
			$this->markTestSkipped( 'DomCrawler component is not available.' );
			return;
		}

		$crawler = $this->renderTemplate(
			'flow_post_meta_actions',
			array(
				'actions' => array(),
				'postId' => 's3chebds95i0atkw',
				'lastEditId' => 's3ufwcms95i0atkw',
				'isOriginalContent' => $args['isOriginalContent'],
				'author' => array(
					'name' => $args['author'],
				),
				'creator' => array(
					'name' => $args['creator'],
				),
				'lastEditUser' => array(
					'name' => $args['lastEditUser'],
				),
			)
		);

		$text = $crawler->filter( '.flow-post-timestamp' )->text();
		// normalize whitespace
		$text = trim( preg_replace( '/\s+/', ' ', $text ) );
		$this->assertStringStartsWith( $expect, $text, $message );
	}

	protected function renderTemplate( $templateName, array $args = array() ) {
		$lc = new TemplateHelper(
			Container::get( 'lightncandy.template_dir' ),
			true // force recompile
		);
		$renderer = $lc->getTemplate( $templateName );

		return new Crawler( $renderer( $args ) );
	}
}
