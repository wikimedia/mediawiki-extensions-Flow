<?php

namespace Flow\Tests\Repository\UserName;

use Flow\Container;
use Flow\Repository\UserName\OneStepUserNameQuery;
use MediaWiki\Block\DatabaseBlock;
use MediaWiki\WikiMap\WikiMap;

/**
 * @group Database
 * @covers \Flow\Repository\UserName\OneStepUserNameQuery
 */
class OneStepUserNameQueryTest extends \MediaWikiIntegrationTestCase {
	/** @var string[] */
	protected $tablesUsed = [ 'user', 'ipblocks' ];

	public function testExecute() {
		$query = new OneStepUserNameQuery(
			Container::get( 'db.factory' ),
			$this->getServiceContainer()->getHideUserUtils()
		);

		$admin = $this->getMutableTestUser( [ 'block' ] )->getUser();

		$u1 = $this->getMutableTestUser()->getUser();
		$u2 = $this->getMutableTestUser()->getUser();
		$u3 = $this->getMutableTestUser()->getUser();
		$u4 = $this->getMutableTestUser()->getUser();

		$blockStore = $this->getServiceContainer()->getDatabaseBlockStore();
		$blockStore->insertBlock( new DatabaseBlock( [
			'address' => $u2,
			'by' => $admin,
			'hideName' => true
		] ) );

		$result = $query->execute(
			WikiMap::getCurrentWikiId(), [
				$u1->getId(),
				$u2->getId(),
				$u3->getId(),
				137117931
			]
		);
		$this->assertSame( 2, $result->numRows() );
		$this->assertEquals(
			[ 'user_id' => $u1->getId(), 'user_name' => $u1->getName() ],
			(array)$result->fetchObject()
		);
		$this->assertEquals(
			[ 'user_id' => $u3->getId(), 'user_name' => $u3->getName() ],
			(array)$result->fetchObject()
		);
	}
}
