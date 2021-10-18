<?php

namespace Flow\Tests\Parsoid;

use Flow\Model\UUID;
use Flow\Model\WikiReference;
use Flow\Parsoid\ReferenceFactory;
use Title;

/**
 * @covers \Flow\Parsoid\ReferenceFactory
 *
 * @group Flow
 */
class ReferenceFactoryTest extends \MediaWikiIntegrationTestCase {
	public function testAcceptsParsoidHrefs() {
		$workflow = $this->createMock( \Flow\Model\Workflow::class );
		$workflow->method( 'getId' )
			->willReturn( UUID::create() );
		$workflow->method( 'getArticleTitle' )
			->willReturn( Title::newMainPage() );

		$factory = new ReferenceFactory(
			$workflow,
			'foo',
			UUID::create()
		);

		$ref = $factory->createWikiReference( 'file', './File:Foo.jpg' );
		$this->assertInstanceOf( WikiReference::class, $ref );
		$this->assertEquals( 'title:File:Foo.jpg', $ref->getTargetIdentifier() );
	}
}
