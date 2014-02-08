<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\PostCollection;
use Flow\Data\RecentChanges as RecentChangesHandler;

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

		// Reset the container
		Container::reset();
		// Recent changes logging is outside the scope of this test, and
		// causes interaction issues
		$c = Container::getContainer();
		foreach ( array( 'header', 'post' ) as $kind ) {
			$key = "storage.$kind.lifecycle-handlers";
			$c[$key] = array_filter(
				$c[$key],
				function( $handler ) {
					return !$handler instanceof RecentChangesHandler;
				}
			);
		}

		// generate a post with multiple revisions
		$this->revisions[] = $revision = $this->generateObject( array(
			'rev_content' => 'first revision',
		) );

		$this->revisions[] = $revision = $this->generateObject( array(
			'rev_content' => 'second revision',
			'rev_change_type' => 'edit-post',
			'rev_parent_id' => $revision->getRevisionId()->getBinary(),
			'tree_rev_descendant_id' => $revision->getPostId()->getBinary(),
		) );

		$this->revisions[] = $revision = $this->generateObject( array(
			'rev_content' => 'third revision',
			'rev_change_type' => 'edit-post',
			'rev_parent_id' => $revision->getRevisionId()->getBinary(),
			'tree_rev_descendant_id' => $revision->getPostId()->getBinary(),
		) );

		$this->storage = $c['storage.post'];
		foreach ( $this->revisions as $revision ) {
			$this->storage->put( $revision );
		}
	}

	protected function tearDown() {
		parent::tearDown();

		foreach ( $this->revisions as $revision ) {
			$this->storage->remove( $revision );
		}
		Container::reset();
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
