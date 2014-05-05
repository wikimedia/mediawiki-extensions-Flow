<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Exception\WikitextException;
use Flow\Model\AbstractRevision;
use Flow\Model\Reference;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Article;
use MediaWikiTestCase;
use Title;
use User;

/**
 * @group Database
 */
class LinksTableTest extends PostRevisionTestCase {

	public function setUp() {
		parent::setUp();
		$this->storage = Container::get( 'storage' );
		$this->extractor = Container::get( 'reference.extractor' );
		$this->recorder = Container::get( 'reference.recorder' );
		$this->updater = Container::get( 'reference.updater.links-tables' );

		// Check for Parsoid
		try {
			\Flow\Parsoid\Utils::convert( 'html', 'wikitext', 'Foo', self::getTestTitle() );
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
		$content = \Flow\Parsoid\Utils::convert( 'wikitext', 'html', $content, self::getTestTitle() );
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
			->getExistingReferences( $revision->getRevisionType(), $revision->getCollectionId() );

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

	public static function provideMutateLinksUpdate() {
		extract( self::getSampleReferences() );

		return array(
			array(
				array( // references
					$fooLinkReference,
					$fooTemplateReference,
					$googleLinkReference,
					$fooImageReference,
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
					$subpageLinkReference,
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
		extract( $this->getBlandTestObjects() );
		$references = $this->expandReferences( $workflow, $revision, $references );
		$parserOutput = new \ParserOutput;
		$linksUpdate = new \LinksUpdate( self::getTestTitle(), $parserOutput );

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
			'workflow' => self::getTestWorkflow( self::getTestTitle() ),
			'revision' => $this->generateObject(),
			'title' => self::getTestTitle(),
		);
	}

	protected function expandReferences( Workflow $workflow, AbstractRevision $revision, array $references ) {
		$referenceObjs = array();

		foreach( $references as $ref ) {
			extract( $ref );
			$srcObjId = $revision->getCollectionId();
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
			'subpageLinkReference' => array(
				'targetType' => 'wiki',
				'refType' => 'link',
				'value' => '/Subpage',
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
