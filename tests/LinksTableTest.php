<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\ReferenceRecorder;
use Flow\Exception\WikitextException;
use Flow\LinksTableUpdater;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Parsoid\ReferenceExtractor;
use Flow\Parsoid\Utils;
use LinksUpdate;
use ParserOutput;
use Title;
use User;

/**
 * @group Flow
 * @group Database
 */
class LinksTableTest extends PostRevisionTestCase {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var ReferenceExtractor
	 */
	protected $extractor;

	/**
	 * @var ReferenceRecorder
	 */
	protected $recorder;

	/**
	 * @var LinksTableUpdater
	 */
	protected $updater;

	public function setUp() {
		parent::setUp();
		$this->storage = Container::get( 'storage' );
		$this->extractor = Container::get( 'reference.extractor' );
		$this->recorder = Container::get( 'reference.recorder' );
		$this->updater = Container::get( 'reference.updater.links-tables' );

		// Check for Parsoid
		try {
			Utils::convert( 'html', 'wikitext', 'Foo', self::getTestTitle() );
		} catch ( WikitextException $excep ) {
			$this->markTestSkipped( 'Parsoid not enabled' );
		}
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
		$content = Utils::convert( 'wikitext', 'html', $content, self::getTestTitle() );
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
		list( $workflow, $revision, $title ) = $this->getBlandTestObjects();

		$references = $this->expandReferences( $workflow, $revision, $references );

		$this->storage->multiPut( $references );

		$foundReferences = $this->recorder
			->getExistingReferences( $revision->getRevisionType(), $revision->getCollectionId() );

		$this->assertReferenceListsEqual( $references, $foundReferences );
	}

	public static function provideReferenceDiff() {
		$references = self::getSampleReferences();

		return array(
			// Just adding a few
			array(
				array(),
				array(
					$references['fooLink'],
					$references['barLink']
				),
				array(
					$references['fooLink'],
					$references['barLink'],
				),
				array(),
			),
			// Removing one
			array(
				array(
					$references['fooLink'],
					$references['barLink']
				),
				array(
					$references['fooLink'],
				),
				array(
				),
				array(
					$references['barLink'],
				),
			),
			// Equality robustness
			array(
				array(
					$references['fooLink'],
				),
				array(
					$references['fooLink2'],
				),
				array(
				),
				array(
				),
			),
			// Inequality robustness
			array(
				array(
					$references['fooLink'],
				),
				array(
					$references['barLink'],
				),
				array(
					$references['barLink'],
				),
				array(
					$references['fooLink'],
				),
			),
		);
	}

	/**
	 * @dataProvider provideReferenceDiff
	 */
	public function testReferenceDiff( $old, $new, $expectedAdded, $expectedRemoved ) {
		list( $workflow, $revision, $title ) = $this->getBlandTestObjects();

		foreach( array( 'old', 'new', 'expectedAdded', 'expectedRemoved' ) as $varName ) {
			$$varName = $this->expandReferences( $workflow, $revision, $$varName );
		}

		list( $added, $removed ) = $this->recorder->referencesDifference( $old, $new );

		$this->assertReferenceListsEqual( $added, $expectedAdded );
		$this->assertReferenceListsEqual( $removed, $expectedRemoved );
	}

	public static function provideMutateLinksUpdate() {
		$references = self::getSampleReferences();

		return array(
			array(
				array( // references
					$references['fooLink'],
					$references['fooTemplate'],
					$references['googleLink'],
					$references['fooImage'],
				),
				array(
					'mLinks' => array(
						NS_MAIN => array( 'Foo' => 0, ),
					),
					'mTemplates' => array(
						NS_TEMPLATE => array( 'Foo' => 0, ),
					),
					'mImages' => array(
						'Foo.jpg' => true,
					),
					'mExternals' => array(
						'http://www.google.com' => true,
					),
				),
			),
			array(
				array(
					$references['subpageLink'],
				),
				array(
					'mLinks' => array(
						NS_MAIN => array( 'UTPage/Subpage' => 0, )
					),
				),
			),
		);
	}

	/**
	 * @dataProvider provideMutateLinksUpdate
	 */
	public function testMutateLinksUpdate( $references, $expectedItems ) {
		list( $workflow, $revision, $title ) = $this->getBlandTestObjects();
		$references = $this->expandReferences( $workflow, $revision, $references );
		$parserOutput = new ParserOutput;
		$linksUpdate = new LinksUpdate( self::getTestTitle(), $parserOutput );

		// Clear the LinksUpdate to allow clean testing
		foreach( array_keys( $expectedItems ) as $fieldName ) {
			$linksUpdate->$fieldName = array();
		}

		$this->updater->mutateLinksUpdate( $linksUpdate, $references );

		foreach( $expectedItems as $field => $content ) {
			$this->assertEquals( $content, $linksUpdate->$field, $field );
		}
	}

	protected function getBlandTestObjects() {
		return array(
			/* workflow = */ self::getTestWorkflow( self::getTestTitle() ),
			/* revision = */ $this->generateObject(),
			/* title = */ self::getTestTitle(),
		);
	}

	protected function expandReferences( Workflow $workflow, AbstractRevision $revision, array $references ) {
		$referenceObjs = array();

		foreach( $references as $ref ) {
			$srcObjId = $revision->getCollectionId();
			if ( isset( $foreign ) ) {
				// From some random place
				$srcObjId = UUID::create();
			}

			$referenceObjs[] = $this->extractor->instantiateReference(
				$workflow,
				$revision->getRevisionType(),
				$srcObjId,
				$ref['targetType'],
				$ref['refType'],
				$ref['$value']
			);
		}

		return $referenceObjs;
	}

	protected static function getSampleReferences() {
		return array(
			'fooLink' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => 'Foo',
			),
			'subpageLink' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => '/Subpage',
			),
			'fooLink2' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => 'foo',
			),
			'barLink' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => 'Bar',
			),
			'fooTemplate' => array(
				'targetType' => 'wiki',
				'refType' => 'template',
				'value' => 'Template:Foo',
			),
			'googleLink' => array(
				'targetType' => 'url',
				'refType' => 'link',
				'value' => 'http://www.google.com'
			),
			'fooImage' => array(
				'targetType' => 'wiki',
				'refType' => 'file',
				'value' => 'File:Foo.jpg',
			),
			'foreignFoo' => array(
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
