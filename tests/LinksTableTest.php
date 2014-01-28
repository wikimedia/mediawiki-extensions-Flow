<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\AbstractRevision;
use Flow\Model\Reference;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\ParsoidUtils;
use MediaWikiTestCase;
use Title;
use User;

/**
 * @group Database
 * @group 
 */
class LinksTableTest extends PostRevisionTestCase {

	public function setUp() {
		parent::setUp();
		$this->storage = Container::get( 'storage' );
		$this->extractor = Container::get( 'reference.extractor' );
		$this->recorder = Container::get( 'reference.recorder' );
		$this->updater = Container::get( 'reference.updater.links-tables' );
	}

	protected function generateTopic( $overrides ) {
		$parentRevision = $this->generateObject();

		$revision = $this->generateObject( $overrides + array(
			'rev_parent_id' => $parentRevision->getRevisionId(),
		) );

		$parentRevision->setChildren( array( $revision ) );

		return $revision;
	}

	protected static function getTestWorkflow( Title $testTitle ) {
		static $workflow = false;

		if ( ! $workflow ) {
			global $wgFlowDefaultWorkflow;
			$definition = Container::get( 'storage' )->find( 'Definition', array(
				'definition_name' => strtolower( $wgFlowDefaultWorkflow ),
				'definition_wiki' => wfWikiId(),
			) );

			$workflow = Workflow::create( reset( $definition ), self::getTestUser(), $testTitle );
		}

		return $workflow;
	}

	protected static function getTestTitle() {
		return Title::newFromText( 'UTPage' );
	}

	protected static function getTestUser() {
		return User::newFromName( 'Test user' );
	}

	public static function provideGetReferencesFromRevisionContent() {
		return array(
			array(
				'[[Foo]]',
				array(
					array(
						'targetType' => 'wiki',
						'refType' => 'link',
						'value' => 'Foo',
					),
				),
			),
			array(
				'[http://www.google.com Foo]',
				array(
					array(
						'targetType' => 'url',
						'refType' => 'link',
						'value' => 'http://www.google.com',
					),
				),
			),
			array(
				'[[File:Foo.jpg]]',
				array(
					array(
						'targetType' => 'wiki',
						'refType' => 'file',
						'value' => 'File:Foo.jpg',
					),
				),
			),
			array(
				'{{Foo}}',
				array(
					array(
						'targetType' => 'wiki',
						'refType' => 'template',
						'value' => 'Template:Foo',
					),
				),
			),
			array(
				'{{Foo}} [[Foo]] [[File:Foo.jpg]] {{Foo}} [[Bar]]',
				array(
					array(
						'targetType' => 'wiki',
						'refType' => 'template',
						'value' => 'Template:Foo',
					),
					array(
						'targetType' => 'wiki',
						'refType' => 'link',
						'value' => 'Foo',
					),
					array(
						'targetType' => 'wiki',
						'refType' => 'file',
						'value' => 'File:Foo.jpg',
					),
					array(
						'targetType' => 'wiki',
						'refType' => 'link',
						'value' => 'Bar',
					),
				),
			)
		);
	}

	/**
	 * @dataProvider provideGetReferencesFromRevisionContent
	 */
	public function testGetReferencesFromRevisionContent( $content, $expectedReferences ) {
		$content = ParsoidUtils::convert( 'wikitext', 'html', $content, self::getTestTitle() );
		$revision = $this->generateTopic( array( 'rev_content' => $content ) );
		$workflow = self::getTestWorkflow( self::getTestTitle() );

		$expectedReferences = $this->expandReferences( $workflow, $revision, $expectedReferences );

		$foundReferences = $this->recorder->getReferencesFromRevisionContent( $workflow, $revision );

		$this->assertReferenceListsEqual( $expectedReferences, $foundReferences );
	}

	public static function provideGetExistingReferences() {
		return array( /* list of test runs */
			array( /* list of arguments */
				array( /* list of references */
					array( /* list of parameters */
						'targetType' => 'wiki',
						'refType' => 'template',
						'value' => 'Template:Foo',
					),
					array(
						'targetType' => 'wiki',
						'refType' => 'link',
						'value' => 'Foo',
					),
					array(
						'targetType' => 'wiki',
						'refType' => 'file',
						'value' => 'File:Foo.jpg',
					),
					array(
						'targetType' => 'wiki',
						'refType' => 'link',
						'value' => 'Bar',
					),
				),
			),
		);
	}

	/**
	 * @dataProvider provideGetExistingReferences
	 */
	public function testGetExistingReferences( array $references ) {
		extract( $this->getBlandTestObjects() );

		$references = $this->expandReferences( $workflow, $revision, $references );

		$this->storage->multiPut( $references );

		$foundReferences = $this->recorder
			->getExistingReferences( $revision->getRevisionType(), $revision->getObjectId() );

		$this->assertReferenceListsEqual( $references, $foundReferences );
	}

	public static function provideReferenceDiff() {
		extract( self::getSampleReferences() );

		return array(
			// Just adding a few
			array(
				array(),
				array(
					$fooLinkReference,
					$barLinkReference
				),
				array(
					$fooLinkReference,
					$barLinkReference,
				),
				array(),
			),
			// Removing one
			array(
				array(
					$fooLinkReference,
					$barLinkReference
				),
				array(
					$fooLinkReference,
				),
				array(
				),
				array(
					$barLinkReference,
				),
			),
			// Equality robustness
			array(
				array(
					$fooLinkReference,
				),
				array(
					$fooLinkReference2,
				),
				array(
				),
				array(
				),
			),
			// Inequality robustness
			array(
				array(
					$fooLinkReference,
				),
				array(
					$barLinkReference,
				),
				array(
					$barLinkReference,
				),
				array(
					$fooLinkReference,
				),
			),
		);
	}

	/**
	 * @dataProvider provideReferenceDiff
	 */
	public function testReferenceDiff( $old, $new, $expectedAdded, $expectedRemoved ) {
		extract( $this->getBlandTestObjects() );

		foreach( array( 'old', 'new', 'expectedAdded', 'expectedRemoved' ) as $varName ) {
			$$varName = $this->expandReferences( $workflow, $revision, $$varName );
		}

		list( $added, $removed ) = $this->recorder->referencesDifference( $old, $new );

		$this->assertReferenceListsEqual( $added, $expectedAdded );
		$this->assertReferenceListsEqual( $removed, $expectedRemoved );
	}

	public static function provideLinksTableUpdate() {
		extract( self::getSampleReferences() );

		return array(
			// Test 1: Basic addition
			array(
				array( // initial references
				),
				array( // added references
					$fooLinkReference,
					$barLinkReference,
				),
				array( // removed references
				),
				array( // initial rows
				),
				array( // expected final rows
					'pagelinks' => array(
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Bar',
						),
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Foo',
						),
					),
				),
			),
			// Test 2: Basic subtraction
			array(
				array( // initial references
				),
				array( // added references
				),
				array( // removed references
					$fooLinkReference,
					$barLinkReference,
				),
				array( // initial rows
					'pagelinks' => array(
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Bar',
						),
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Foo',
						),
					),
				),
				array( // expected final rows
					'pagelinks' => array(
						'_fields' => array(
							'pl_from' => false,
							'pl_namespace' => false,
							'pl_title' => false,
						),
					),
				),
			),
			// Test 3: Partial subtraction
			array(
				array( // initial references
				),
				array( // added references
				),
				array( // removed references
					$barLinkReference,
				),
				array( // initial rows
					'pagelinks' => array(
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Bar',
						),
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Foo',
						),
					),
					'templatelinks' => array(
						array(
							'tl_from' => 'articleid',
							'tl_namespace' => '10',
							'tl_title' => 'Foo',
						),
					),
				),
				array( // expected final rows
					'pagelinks' => array(
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Foo',
						),
					),
					'templatelinks' => array(
						array(
							'tl_from' => 'articleid',
							'tl_namespace' => '10',
							'tl_title' => 'Foo',
						),
					),
				),
			),
			// Test 4: Subtraction with remaining references
			array(
				array( // initial references
					$foreignFooReference,
				),
				array( // added references
				),
				array( // removed references
					$fooLinkReference,
					$barLinkReference,
				),
				array( // initial rows
					'pagelinks' => array(
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Foo',
						),
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Bar',
						),
					),
				),
				array( // expected final rows
					'pagelinks' => array(
						array(
							'pl_from' => 'articleid',
							'pl_namespace' => '0',
							'pl_title' => 'Foo',
						),
					),
				),
			),
		);
	}

	/**
	 * @dataProvider provideLinksTableUpdate
	 * @group Destructive
	 */
	public function testLinksTableUpdate( $initialReferences, $addReferences, $removeReferences, $initialRows, $expectedRows ) {
		$db = wfGetDB( DB_MASTER );

		extract( $this->getBlandTestObjects() );
		$initialReferences = $this->expandReferences( $workflow, $revision, $initialReferences );

		// Make sure the tables are clear initially
		$db->delete( 'flow_wiki_ref', 1, array() );
		$db->delete( 'flow_ext_ref', 1, array() );

		$this->storage->multiPut( $initialReferences );

		foreach( $initialRows as $tableName => $rows ) {
			$db->delete( $tableName, 1, __METHOD__ );
			foreach( $rows as $index => $row ) {
				foreach( $row as $field => $value ) {
					if ( $value === 'articleid' ) {
						$rows[$index][$field] = $title->getArticleId();
					}
					$rows[$index][$field] = (string) $rows[$index][$field];
				}
			}

			$db->insert( $tableName, $rows, __METHOD__ );
		}

		$addReferences = $this->expandReferences( $workflow, $revision, $addReferences );
		$removeReferences = $this->expandReferences( $workflow, $revision, $removeReferences );

		$this->updater->updateLinksTables( $revision->getObjectId(), $title, $addReferences, $removeReferences );

		foreach( $expectedRows as $tableName => $rows ) {
			if ( ! count( $rows ) ) {
				continue;
			}

			$fields = false;
			foreach( $rows as $index => $row ) {
				if ( $fields === false ) {
					$fields = array_keys( $row );
				}

				if ( $index === '_fields' ) {
					unset( $rows[$index] );
					continue;
				}

				foreach( $row as $field => $value ) {
					if ( $value === 'articleid' ) {
						$rows[$index][$field] = $title->getArticleId();
					}
					$rows[$index][$field] = (string) $rows[$index][$field];
				}
			}
			$this->assertSelect( $tableName, implode( ',', $fields ), 1, array_map( 'array_values', $rows ) );
		}
	}

	protected function getBlandTestObjects() {
		return array(
			'workflow' => self::getTestWorkflow( self::getTestTitle() ),
			'revision' => $this->generateObject(),
			'title' => self::getTestTitle(),
		);
	}

	protected function expandReferences( Workflow $workflow, AbstractRevision $revision, array $references ) {
		$referenceObjs = array();

		foreach( $references as $ref ) {
			extract( $ref );
			$srcObjId = $revision->getObjectId();
			if ( isset( $foreign ) ) {
				// From some random place
				$srcObjId = UUID::create();
			}

			$referenceObjs[] = $this->extractor->instantiateReference(
				$workflow,
				$revision->getRevisionType(),
				$srcObjId,
				$targetType,
				$refType,
				$value
			);
		}

		return $referenceObjs;
	}

	protected static function getSampleReferences() {
		return array(
			'fooLinkReference' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => 'Foo',
			),
			'fooLinkReference2' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => 'foo',
			),
			'barLinkReference' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => 'Bar',
			),
			'fooTemplateReference' => array(
				'targetType' => 'wiki',
				'refType' => 'template',
				'value' => 'Template:Foo',
			),
			'googleLinkReference' => array(
				'targetType' => 'url',
				'refType' => 'link',
				'value' => 'http://www.google.com'
			),
			'fooImageReference' => array(
				'targetType' => 'wiki',
				'refType' => 'file',
				'value' => 'File:Foo.jpg',
			),
			'foreignFooReference' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => 'Foo',
				'foreign' => true,
			),
		);
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

		$this->assertEquals( array_keys( $list1 ), array_keys( $list2 ) );
	}
}