<?php

use Flow\Container;
use Flow\Model\AbstractRevision;
use Flow\Model\Reference;
use Flow\Model\UUID;
use Flow\Model\Workflow;

/**
 * @group Database
 * @group 
 */
class LinksTableTest extends MediaWikiTestCase {

	public function setUp() {
		$this->recorder = Container::get( 'reference.recorder' );
		$this->updater = Container::get( 'reference.updater.links-tables' );
	}

	public static function provideGetReferencesFromRevisionContent() {

	}

	/**
	 * @dataProvider provideGetReferencesFromRevisionContent
	 */
	public function testGetReferencesFromRevisionContent( $workflow, $revision, $expectedReferences ) {
		$foundReferences = $this->recorder->getReferencesFromRevisionContent( $workflow, $revision );

		$this->assertReferenceListsEqual( $expectedReferences, $foundReferences );
	}

	public static function provideGetExistingReferences() {

	}

	/**
	 * @dataProvider @provideGetExistingReferences
	 */
	public function testGetExistingReferences( $revType, UUID $revId, array $references ) {
		$storage = Container::get( 'storage' );
		$storage->multiPut( $references );

		$foundReferences = $this->recorder
			->getExistingReferences( $revType, $revId );

		$this->assertReferenceListsEqual( $input1, $input2 );
	}

	public static function provideReferenceDiff() {

	}

	/**
	 * @dataProvider @provideReferenceDiff
	 */
	public function testReferenceDiff( $old, $new, $expectedAdded, $expectedRemoved ) {
		list( $added, $removed ) = $this->recorder->referencesDifference( $old, $new );

		$this->assertReferenceListsEqual( $added, $expectedAdded );
		$this->assertReferenceListsEqual( $removed, $expectedRemoved );
	}

	public static function provideLinksTableUpdate() {

	}

	/**
	 * @dataProvider @provideLinksTableUpdate
	 */
	public function testLinksTableUpdate( $objectId, $title, $initialReferences, $addReferences, $removeReferences, $initialRows, $expectedRows ) {
		Container::get( 'storage' )->multiPut( $initialReferences );

		$db = wfGetDB( DB_MASTER );
		foreach( $initialRows as $tableName => $rows ) {
			$db->insert( $tableName, $rows, __METHOD__ );
		}

		$this->updater->updateLinksTables( $objectId, $title, $addReferences, $removeReferences );

		foreach( $expectedRows as $tableName => $rows ) {
			$res = $dbr->select( $tableName, '*', 1, __METHOD__ );

			$rows = array();
			foreach( $res as $row ) {
				$rows[] = (array)$row;
			}

			$this->assertEquals( $res, $rows );
		}
	}

	protected function assertReferenceListsEqual( $input1, $input2 ) {
		$list1 = array();
		$list2 = array();

		foreach( $input1 as $reference ) {
			$list1[$reference->getUniqueIdentifier()] = $reference;
		}

		ksort( $list1 );

		foreach( $input2 as $reference ) {
			$list2[$reference->getUniqueIdentifier()] = $reference;
		}

		ksort( $list2 );

		$this->assertTrue( array_keys( $list1 ) === array_keys( $list2 ) );
	}
}