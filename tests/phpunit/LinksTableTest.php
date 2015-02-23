<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\Listener\ReferenceRecorder;
use Flow\Exception\WikitextException;
use Flow\LinksTableUpdater;
use Flow\Model\AbstractRevision;
use Flow\Model\Workflow;
use Flow\Parsoid\ReferenceExtractor;
use Flow\Parsoid\ReferenceFactory;
use Flow\Parsoid\Utils;
use ParserOutput;
use Title;

/**
 * @group Flow
 * @group Database
 */
class LinksTableTest extends PostRevisionTestCase {
	/**
	 * @var array
	 */
	protected $tablesUsed = array( 'flow_ext_ref', 'flow_wiki_ref', 'flow_revision', 'flow_tree_revision', 'flow_workflow' );

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
			Utils::convert( 'html', 'wikitext', 'Foo', $this->workflow->getOwnerTitle() );
		} catch ( WikitextException $excep ) {
			$this->markTestSkipped( 'Parsoid not enabled' );
		}

		// These tests don't provide sufficient data to properly run all listeners
		$this->clearExtraLifecycleHandlers();
	}

	protected function generatePost( $overrides ) {
		$parentRevision = $this->generateObject();

		$revision = $this->generateObject( $overrides + array(
			'tree_parent_id' => $parentRevision->getRevisionId(),
		) );

		return $revision;
	}

	protected static function getTestTitle() {
		return Title::newFromText( 'UTPage' );
	}

	public static function provideGetReferencesFromRevisionContent() {
		return array(
			array(
				'[[Foo]]',
				array(
					array(
						'factoryMethod' => 'createWikiReference',
						'refType' => 'link',
						'value' => 'Foo',
					),
				),
			),
			array(
				'[http://www.google.com Foo]',
				array(
					array(
						'factoryMethod' => 'createUrlReference',
						'refType' => 'link',
						'value' => 'http://www.google.com',
					),
				),
			),
			array(
				'[[File:Foo.jpg]]',
				array(
					array(
						'factoryMethod' => 'createWikiReference',
						'refType' => 'file',
						'value' => 'File:Foo.jpg',
					),
				),
			),
			array(
				'{{Foo}}',
				array(
					array(
						'factoryMethod' => 'createWikiReference',
						'refType' => 'template',
						'value' => 'Template:Foo',
					),
				),
			),
			array(
				'{{Foo}} [[Foo]] [[File:Foo.jpg]] {{Foo}} [[Bar]]',
				array(
					array(
						'factoryMethod' => 'createWikiReference',
						'refType' => 'template',
						'value' => 'Template:Foo',
					),
					array(
						'factoryMethod' => 'createWikiReference',
						'refType' => 'link',
						'value' => 'Foo',
					),
					array(
						'factoryMethod' => 'createWikiReference',
						'refType' => 'file',
						'value' => 'File:Foo.jpg',
					),
					array(
						'factoryMethod' => 'createWikiReference',
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
		$content = Utils::convert( 'wikitext', 'html', $content, $this->workflow->getOwnerTitle() );
		$revision = $this->generatePost( array( 'rev_content' => $content ) );

		$expectedReferences = $this->expandReferences( $this->workflow, $revision, $expectedReferences );

		$foundReferences = $this->recorder->getReferencesFromRevisionContent( $this->workflow, $revision );

		$this->assertReferenceListsEqual( $expectedReferences, $foundReferences );
	}

	/**
	 * @dataProvider provideGetReferencesFromRevisionContent
	 */
	public function testGetReferencesAfterRevisionInsert( $content, $expectedReferences ) {
		$content = Utils::convert( 'wikitext', 'html', $content, $this->workflow->getOwnerTitle() );
		$revision = $this->generatePost( array( 'rev_content' => $content ) );

		// Save to storage to test if ReferenceRecorder listener picks this up
		$this->store( $revision );

		$expectedReferences = $this->expandReferences( $this->workflow, $revision, $expectedReferences );

		// References will be stored as linked from Topic:<id>
		$title = Title::newFromText( $revision->getPostId()->getAlphadecimal(), NS_TOPIC );

		// Retrieve references from storage
		$foundReferences = $this->updater->getReferencesForTitle( $title );

		$this->assertReferenceListsEqual( $expectedReferences, $foundReferences );
	}

	public static function provideGetExistingReferences() {
		return array( /* list of test runs */
			array( /* list of arguments */
				array( /* list of references */
					array( /* list of parameters */
						'factoryMethod' => 'createWikiReference',
						'refType' => 'template',
						'value' => 'Template:Foo',
					),
					array(
						'factoryMethod' => 'createWikiReference',
						'refType' => 'link',
						'value' => 'Foo',
					),
					array(
						'factoryMethod' => 'createWikiReference',
						'refType' => 'file',
						'value' => 'File:Foo.jpg',
					),
					array(
						'factoryMethod' => 'createWikiReference',
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
					$references['FooLink'],
				),
				array(
				),
				array(
				),
				array( // test is only valid if Foo and foo are same page
					'wgCapitalLinks' => true,
				)
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
	public function testReferenceDiff( $old, $new, $expectedAdded, $expectedRemoved, $globals = array() ) {
		if ( $globals ) {
			$this->setMwGlobals( $globals );
		}
		list( $workflow, $revision, $title ) = $this->getBlandTestObjects();

		foreach( array( 'old', 'new', 'expectedAdded', 'expectedRemoved' ) as $varName ) {
			$$varName = $this->expandReferences( $workflow, $revision, $$varName );
		}

		list( $added, $removed ) = $this->recorder->referencesDifference( $old, $new );

		$this->assertReferenceListsEqual( $added, $expectedAdded );
		$this->assertReferenceListsEqual( $removed, $expectedRemoved );
	}

	public static function provideMutateParserOutput() {
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
					'getLinks' => array(
						NS_MAIN => array( 'Foo' => 0, ),
					),
					'getTemplates' => array(
						NS_TEMPLATE => array( 'Foo' => 0, ),
					),
					'getImages' => array(
						'Foo.jpg' => true,
					),
					'getExternalLinks' => array(
						'http://www.google.com' => true,
					),
				),
			),
			array(
				array(
					$references['subpageLink'],
				),
				array(
					'getLinks' => array(
						// NS_MAIN is the namespace of static::getTestTitle()
						NS_MAIN => array( static::getTestTitle()->getDBkey() . '/Subpage' => 0, )
					),
				),
			),
		);
	}

	/**
	 * @dataProvider provideMutateParserOutput
	 */
	public function testMutateParserOutput( $references, $expectedItems ) {
		list( $workflow, $revision, $title ) = $this->getBlandTestObjects();

		/*
		 * Because the data provider is static, we can't access $this->workflow
		 * in there. Once of the things being tested is a subpage link.
		 * Thus, we would have to provide the correct namespace & title for
		 * $this->workflow->getArticleTitle(), under which the subpage will be
		 * created.
		 * Let's work around this by overwriting $workflow->title to a "known"
		 * value, so that we can hardcode that into the expected return value in
		 * the static provider.
		 */
		$title = static::getTestTitle();
		$reflectionWorkflow = new \ReflectionObject( $workflow );
		$reflectionProperty = $reflectionWorkflow->getProperty( 'title' );
		$reflectionProperty->setAccessible( true );
		$reflectionProperty->setValue( $workflow, $title );

		$references = $this->expandReferences( $workflow, $revision, $references );
		$parserOutput = new \ParserOutput;

		// Clear the LinksUpdate to allow clean testing
		foreach( array_keys( $expectedItems ) as $fieldName ) {
			$parserOutput->$fieldName = array();
		}

		$this->updater->mutateParserOutput( $title, $parserOutput, $references );

		foreach( $expectedItems as $method => $content ) {
			$this->assertEquals( $content, $parserOutput->$method(), $method );
		}
	}

	protected function getBlandTestObjects() {
		return array(
			/* workflow = */ $this->workflow,
			/* revision = */ $this->revision,
			/* title = */ $this->workflow->getArticleTitle(),
		);
	}

	protected function expandReferences( Workflow $workflow, AbstractRevision $revision, array $references ) {
		$referenceObjs = array();
		$factory = new ReferenceFactory( $workflow, $revision->getRevisionType(), $revision->getCollectionId() );

		foreach( $references as $ref ) {
			$referenceObjs[] = $factory->{$ref['factoryMethod']}( $ref['refType'], $ref['value'] );
		}

		return $referenceObjs;
	}

	protected static function getSampleReferences() {
		return array(
			'fooLink' => array(
				'factoryMethod' => 'createWikiReference',
				'refType' => 'link',
				'value' => 'Foo',
			),
			'subpageLink' => array(
				'factoryMethod' => 'createWikiReference',
				'refType' => 'link',
				'value' => '/Subpage',
			),
			'FooLink' => array(
				'factoryMethod' => 'createWikiReference',
				'refType' => 'link',
				'value' => 'foo',
			),
			'barLink' => array(
				'factoryMethod' => 'createWikiReference',
				'refType' => 'link',
				'value' => 'Bar',
			),
			'fooTemplate' => array(
				'factoryMethod' => 'createWikiReference',
				'refType' => 'template',
				'value' => 'Template:Foo',
			),
			'googleLink' => array(
				'factoryMethod' => 'createUrlReference',
				'refType' => 'link',
				'value' => 'http://www.google.com'
			),
			'fooImage' => array(
				'factoryMethod' => 'createWikiReference',
				'refType' => 'file',
				'value' => 'File:Foo.jpg',
			),
			'foreignFoo' => array(
				'factoryMethod' => 'createWikiReference',
				'refType' => 'link',
				'value' => 'Foo',
			),
		);
	}

	protected function flattenReferenceList( $input ) {
		$list = array();

		foreach( $input as $reference ) {
			$list[$reference->getUniqueIdentifier()] = $reference;
		}

		ksort( $list );
		return array_keys( $list );
	}

	protected function assertReferenceListsEqual( $input1, $input2 ) {
		$list1 = $this->flattenReferenceList( $input1 );
		$list2 = $this->flattenReferenceList( $input2 );

		$this->assertEquals( $list1, $list2 );
	}
}
