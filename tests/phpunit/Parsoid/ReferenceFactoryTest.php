<?php

namespace Flow\Tests\Parsoid;

use Flow\Model\UUID;
use Flow\Parsoid\ReferenceFactory;
use Title;

/**
 * @group Flow
 */
class ReferenceFactoryTest extends \MediaWikiTestCase {
	public function testAcceptsParsoidHrefs() {
		$workflow = $this->getMock( 'Flow\Model\Workflow' );
		$workflow->expects( $this->any() )
			->method( 'getId' )
			->will( $this->returnValue( UUID::create() ) );
		$workflow->expects( $this->any() )
			->method( 'getArticleTitle' )
			->will( $this->returnValue( Title::newMainPage() ) );

		$factory = new ReferenceFactory(
			$workflow,
			'foo',
			UUID::create()
		);

		$ref = $factory->createWikiReference( 'file', './File:Foo.jpg' );
		$this->assertInstanceOf( 'Flow\\Model\\WikiReference', $ref );
		$this->assertEquals( 'title:File:Foo.jpg', $ref->getTargetIdentifier() );
	}
}
