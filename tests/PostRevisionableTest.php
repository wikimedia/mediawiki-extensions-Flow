<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\PostRevisionable;

/**
 * @group Flow
 * @group Database
 */
class PostRevisionableTest extends PostRevisionTestCase {
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

	public function testGetRevisionable() {
		$revision = $this->revisions[0];
		$revisionable = $revision->getRevisionable();
		$this->assertTrue( $revisionable instanceof PostRevisionable );
	}

	public function testNewFromId() {
		$uuidPost = $this->revisions[0]->getPostId();
		$revisionable = PostRevisionable::newFromId( $uuidPost );
		$this->assertTrue( $revisionable instanceof PostRevisionable );
	}

	public function testNewFromRevision() {
		$revision = $this->revisions[0];
		$revisionable = PostRevisionable::newFromRevision( $revision );
		$this->assertTrue( $revisionable instanceof PostRevisionable );
	}

	public function testGetRevision() {
		$revisionable = $this->revisions[0]->getRevisionable();

		$expected = $this->revisions[1];
		$revision = $revisionable->getRevision( $expected->getRevisionId() );

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetLastRevision() {
		$revisionable = $this->revisions[0]->getRevisionable();

		$expected = end( $this->revisions );
		$revision = $revisionable->getLastRevision();

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetFirstRevision() {
		$revisionable = $this->revisions[1]->getRevisionable();

		$expected = reset( $this->revisions );
		$revision = $revisionable->getFirstRevision();

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetNextRevision() {
		$start = $this->revisions[0];
		$revisionable = $start->getRevisionable();

		$expected = $this->revisions[1];
		$revision = $revisionable->getNextRevision( $start );

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetPreviousRevision() {
		$start = $this->revisions[1];
		$revisionable = $start->getRevisionable();

		$expected = $this->revisions[0];
		$revision = $revisionable->getPreviousRevision( $start );

		$this->assertTrue( $expected->getRevisionId()->equals( $revision->getRevisionId() ) );
	}

	public function testGetAllRevision() {
		$revisionable = $this->revisions[1]->getRevisionable();

		$revisions = $revisionable->getAllRevisions();

		$this->assertEquals( count( $this->revisions ), count( $revisions ) );
	}
}
