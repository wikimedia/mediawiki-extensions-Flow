<?php

namespace Flow\Formatter;

use IContextSource;

class RevisionDiffViewFormatter {

	protected $revisionViewFormatter;

	public function __construct( RevisionViewFormatter $revisionViewFormatter ) {
		$this->revisionViewFormatter = $revisionViewFormatter;
	}

	/**
	 * Diff would format against two revisions
	 */
	public function formatApi( FormatterRow $newRow, FormatterRow $oldRow, IContextSource $ctx ) {
		$oldRes = $this->revisionViewFormatter->formatApi( $oldRow, $ctx );
		$newRes = $this->revisionViewFormatter->formatApi( $newRow, $ctx );

		$oldContent = $oldRow->revision->getContent( 'wikitext' );
		$newContent = $newRow->revision->getContent( 'wikitext' );

		$differenceEngine = new \DifferenceEngine();

		$differenceEngine->setContent(
			new \TextContent( $oldContent ),
			new \TextContent( $newContent )
		);

		return array( 'new' => $newRes, 'old' => $oldRes, 'diff_content' => $differenceEngine->getDiffBody() );
	}
}
