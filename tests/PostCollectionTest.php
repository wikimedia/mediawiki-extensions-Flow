<?php

namespace Flow\Tests;

use Flow\Collection\PostCollection;

/**
 * @group Flow
 * @group Database
 */
class PostCollectionTest extends PostRevisionTestCase {
	/**
	 * @var array
	 */
	protected $tablesUsed = array( 'flow_revision', 'flow_tree_revision' );

	protected function setUp() {
		parent::setUp();

		// recent changes isn't fully setup here, just skip it
		$this->clearRecentChangesLifecycleHandlers();

		// generate a post with multiple revisions
		$revision = $this->generateObject( array(
			'rev_content' => 'first revision',
		) );
		$this->store( $revision );

		$revision = $this->generateObject( array(
			'rev_content' => 'second revision',
			'rev_change_type' => 'edit-post',
			'rev_parent_id' => $revision->getRevisionId()->getBinary(),
			'tree_rev_descendant_id' => $revision->getPostId()->getBinary(),
			'rev_type_id' => $revision->getPostId()->getBinary(),
		) );
		$this->store( $revision );

		$revision = $this->generateObject( array(
			'rev_content' => 'third revision',
			'rev_change_type' => 'edit-post',
			'rev_parent_id' => $revision->getRevisionId()->getBinary(),
			'tree_rev_descendant_id' => $revision->getPostId()->getBinary(),
			'rev_type_id' => $revision->getPostId()->getBinary(),
		) );
		$this->store( $revision );
	}

	public function testGetCollection() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$revision = $this->revisions[0];
		$collection = $revision->getCollection();
		$this->assertTrue( $collection instanceof PostCollection );
	}

	public function testNewFromId() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$uuidPost = $this->revisions[0]->getPostId();
		$collection = PostCollection::newFromId( $uuidPost );
		$this->assertTrue( $collection instanceof PostCollection );
	}

	public function testNewFromRevision() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$revision = $this->revisions[0];
		$collection = PostCollection::newFromRevision( $revision );
		$this->assertTrue( $collection instanceof PostCollection );
	}

	public function testGetRevision() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$collection = $this->revisions[0]->getCollection();

		$expected = $this->revisions[1];
		$revision = $collection->getRevision( $expected->getRevisionId() );
		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetLastRevision() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$collection = $this->revisions[0]->getCollection();

		$expected = end( $this->revisions );
		$revision = $collection->getLastRevision();

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetFirstRevision() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$collection = $this->revisions[1]->getCollection();

		$expected = reset( $this->revisions );
		$revision = $collection->getFirstRevision();

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetNextRevision() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$start = $this->revisions[0];
		$collection = $start->getCollection();

		$expected = $this->revisions[1];
		$revision = $collection->getNextRevision( $start );

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetPrevRevision() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$start = $this->revisions[1];
		$collection = $start->getCollection();

		$expected = $this->revisions[0];
		$revision = $collection->getPrevRevision( $start );

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetAllRevision() {
		$this->markTestSkipped( 'Ignoring intermittently broken tests utilizing collections' );
		return;
		$collection = $this->revisions[1]->getCollection();

		$revisions = $collection->getAllRevisions();

		$this->assertEquals( count( $this->revisions ), count( $revisions ) );
	}
}
