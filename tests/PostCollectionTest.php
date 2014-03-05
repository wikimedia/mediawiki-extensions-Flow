<?php

namespace Flow\Tests;

use Flow\Container;
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

	/**
	 * @var array Array of PostRevision objects
	 */
	protected $revisions = array();

	/**
	 * @var ObjectManager
	 */
	protected $storage;

	protected function setUp() {
		parent::setUp();

		// recent changes isn't fully setup here, just skip it
		$this->clearRecentChangesLifecycleHandlers();

		// generate a post with multiple revisions
		$this->revisions[] = $revision = $this->generateObject( array(
			'rev_content' => 'first revision',
		) );

		$this->revisions[] = $revision = $this->generateObject( array(
			'rev_content' => 'second revision',
			'rev_change_type' => 'edit-post',
			'rev_parent_id' => $revision->getRevisionId()->getBinary(),
			'tree_rev_descendant_id' => $revision->getPostId()->getBinary(),
			'rev_type_id' => $revision->getPostId()->getBinary(),
		) );

		$this->revisions[] = $revision = $this->generateObject( array(
			'rev_content' => 'third revision',
			'rev_change_type' => 'edit-post',
			'rev_parent_id' => $revision->getRevisionId()->getBinary(),
			'tree_rev_descendant_id' => $revision->getPostId()->getBinary(),
			'rev_type_id' => $revision->getPostId()->getBinary(),
		) );

		$this->storage = Container::get( 'storage.post' );
		foreach ( $this->revisions as $revision ) {
			$this->storage->put( $revision );
		}
	}

	protected function tearDown() {
		parent::tearDown();

		foreach ( $this->revisions as $revision ) {
			$this->storage->remove( $revision );
		}
	}

	public function testGetCollection() {
		$revision = $this->revisions[0];
		$collection = $revision->getCollection();
		$this->assertTrue( $collection instanceof PostCollection );
	}

	public function testNewFromId() {
		$uuidPost = $this->revisions[0]->getPostId();
		$collection = PostCollection::newFromId( $uuidPost );
		$this->assertTrue( $collection instanceof PostCollection );
	}

	public function testNewFromRevision() {
		$revision = $this->revisions[0];
		$collection = PostCollection::newFromRevision( $revision );
		$this->assertTrue( $collection instanceof PostCollection );
	}

	public function testGetRevision() {
		$collection = $this->revisions[0]->getCollection();

		$expected = $this->revisions[1];
		$revision = $collection->getRevision( $expected->getRevisionId() );
		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetLastRevision() {
		$collection = $this->revisions[0]->getCollection();

		$expected = end( $this->revisions );
		$revision = $collection->getLastRevision();

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetFirstRevision() {
		$collection = $this->revisions[1]->getCollection();

		$expected = reset( $this->revisions );
		$revision = $collection->getFirstRevision();

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetNextRevision() {
		$start = $this->revisions[0];
		$collection = $start->getCollection();

		$expected = $this->revisions[1];
		$revision = $collection->getNextRevision( $start );

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetPrevRevision() {
		$start = $this->revisions[1];
		$collection = $start->getCollection();

		$expected = $this->revisions[0];
		$revision = $collection->getPrevRevision( $start );

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetAllRevision() {
		$collection = $this->revisions[1]->getCollection();

		$revisions = $collection->getAllRevisions();

		$this->assertEquals( count( $this->revisions ), count( $revisions ) );
	}
}
