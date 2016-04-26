<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\NotificationController;
use Flow\Model\UUID;
use TestingAccessWrapper;

// See also NotitifiedUsersTest
/**
 * @group Flow
 */
class NotificationControllerTest extends \MediaWikiTestCase {
	protected $notificationController;

	protected function setUp() {
		$this->notificationController = Container::get( 'controller.notification' );

		parent::setUp();
	}

	public static function getDeepestCommonRootProvider() {
		return array(
			array(
				UUID::create( 't2f2m1hexpxgi9oy' ),
				'Siblings, same length',
				array(
					't2f2m1hexpxgi9oy' => array(
						UUID::create( 't2et4aiiijstihea' ),
						UUID::create( 't2et4aiwjnpnk5ua' ),
						UUID::create( 't2et6t35psmz1ac2' ),
						UUID::create( 't2f2m1hexpxgi9oy' ),
						UUID::create( 't2f2n66t66w0j636' ),
					),
					't2f2mruymt1k4wia' => array(
						UUID::create( 't2et4aiiijstihea' ),
						UUID::create( 't2et4aiwjnpnk5ua' ),
						UUID::create( 't2et6t35psmz1ac2' ),
						UUID::create( 't2f2m1hexpxgi9oy' ),
						UUID::create( 't2f2nio0fzrqybo2' )
					),
				)
			),
			array(
				UUID::create( 't2et6t35psmz1ac2' ),
				'First shorter',
				array(
					't2f2mdjbw1dcfkia' => array(
						UUID::create( 't2et4aiiijstihea' ),
						UUID::create( 't2et4aiwjnpnk5ua' ),
						UUID::create( 't2et6t35psmz1ac2' ),
						UUID::create( 't2f2mdjbw1dcfkia' ),
					),
					't2f2n66t66w0j636' => array(
						UUID::create( 't2et4aiiijstihea' ),
						UUID::create( 't2et4aiwjnpnk5ua' ),
						UUID::create( 't2et6t35psmz1ac2' ),
						UUID::create( 't2f2m1hexpxgi9oy' ),
						UUID::create( 't2f2n66t66w0j636' )
					),
				),
			),
			array(
				UUID::create( 't2f2re4e901we1zm' ),
				'First longer, second truncated version of first',
				array(
					't2feoifdgpa2rt02' => array(
						UUID::create( 't2et4aiiijstihea' ),
						UUID::create( 't2et4aiwjnpnk5ua' ),
						UUID::create( 't2et6t35psmz1ac2' ),
						UUID::create( 't2f2mdjbw1dcfkia' ),
						UUID::create( 't2f2re4e901we1zm' ),
						UUID::create( 't2feoifdgpa2rt02' ),
					),
					't2f2re4e901we1zm' => array(
						UUID::create( 't2et4aiiijstihea' ),
						UUID::create( 't2et4aiwjnpnk5ua' ),
						UUID::create( 't2et6t35psmz1ac2' ),
						UUID::create( 't2f2mdjbw1dcfkia' ),
						UUID::create( 't2f2re4e901we1zm' ),
					),
				),
			),
		);
	}

	/**
	 * @dataProvider getDeepestCommonRootProvider
	 */
	public function testGetDeepestCommonRoot( $expectedDeepest, $message, $rootPaths ) {
		$actualDeepest = TestingAccessWrapper::newFromObject( $this->notificationController )->getDeepestCommonRoot( $rootPaths );
		$this->assertEquals( $expectedDeepest, $actualDeepest, $message );
	}

	public static function getFirstPreorderDepthFirstProvider() {
		// This isn't necessarily the actual structure returned by
		// fetchSubtreeIdentityMap.  It's the part we use
		$tree = array(
			't2et6t35psmz1ac2' => array(
				'children' => array(
					't2f2m1hexpxgi9oy' => &$tree['t2f2m1hexpxgi9oy'],
					't2f2mdjbw1dcfkia' => &$tree['t2f2mdjbw1dcfkia'],
					't2f2mruymt1k4wia' => &$tree['t2f2mruymt1k4wia'],
				),
			),
			't2f2m1hexpxgi9oy' => array(
				'children' => array(
					't2f2n66t66w0j636' => &$tree['t2f2n66t66w0j636'],
					't2f2nio0fzrqybo2' => &$tree['t2f2nio0fzrqybo2'],
				),
			),
			't2f2mdjbw1dcfkia' => array(
				'children' => array(
					't2f2re4e901we1zm' => &$tree['t2f2re4e901we1zm'],
				),
			),
			't2f2mruymt1k4wia' => array(
				'children' => array(),
			),

			't2f2n66t66w0j636' => array(
				'children' => array(),
			),

			't2f2nio0fzrqybo2' => array(
				'children' => array(),
			),

			't2f2re4e901we1zm' => array(
				'children' => array(),
			),
		);

		$treeRoot = UUID::create( 't2et6t35psmz1ac2' );

		return array(
			array(
				$treeRoot,
				'Topmost is root',
				array(
					't2et6t35psmz1ac2' => $treeRoot,
					't2f2mruymt1k4wia' => UUID::create( 't2f2mruymt1k4wia' ),
					't2f2nio0fzrqybo2' => UUID::create( 't2f2nio0fzrqybo2' ),
					't2f2re4e901we1zm' => UUID::create( 't2f2re4e901we1zm' ),
				),
				$treeRoot,
				$tree,
			),
			array(
				UUID::create( 't2f2n66t66w0j636' ),
				'Topmost is not root',
				array(
					't2f2mdjbw1dcfkia' => UUID::create( 't2f2mdjbw1dcfkia' ),
					't2f2n66t66w0j636' => UUID::create( 't2f2n66t66w0j636' ),
					't2f2re4e901we1zm' => UUID::create( 't2f2re4e901we1zm' ),
				),
				$treeRoot,
				$tree,
			),
		);
	}

	/**
	 * @dataProvider getFirstPreorderDepthFirstProvider
	 */
	public function testGetFirstPreorderDepthFirst( UUID $expectedFirst, $message, array $relevantPostIds, UUID $root, array $tree ) {
		$actualFirst = TestingAccessWrapper::newFromObject( $this->notificationController )->getFirstPreorderDepthFirst( $relevantPostIds, $root, $tree );
		$this->assertEquals( $expectedFirst, $actualFirst, $message );
	}
}