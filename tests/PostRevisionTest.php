<?php

namespace Flow\Tests;

use Flow\Model\PostRevision;

class PostRevisionTest extends PostRevisionTestCase {
	/**
	 * Tests that a PostRevision::fromStorageRow & ::toStorageRow roundtrip
	 * returns the same DB data.
	 */
	public function testRoundtrip() {
		$row = $this->generateRow();
		$object = PostRevision::fromStorageRow( $row );

		// toStorageRow will add a bogus column 'rev_content_url' - that's ok.
		// It'll be caught in code to distinguish between external content and
		// content to be saved in rev_content, and, before inserting into DB,
		// it'll be unset. We'll ignore this column here.
		$roundtripRow = PostRevision::toStorageRow( $object );
		unset( $roundtripRow['rev_content_url'] );

		$this->assertEquals( $roundtripRow, $row );
	}
}
