<?php

namespace Flow\Formatter;

use DifferenceEngine;
use Flow\Model\AbstractRevision;
use IContextSource;
use TextContent;

class RevisionUndoViewFormatter {
	protected $revisionViewFormatter;

	public function __construct( RevisionViewFormatter $revisionViewFormatter ) {
		$this->revisionViewFormatter = $revisionViewFormatter;
	}

	/**
	 * Undoes the change that occured between $start and $stop
	 */
	public function formatApi(
		FormatterRow $start,
		FormatterRow $stop,
		FormatterRow $current,
		IContextSource $context
	) {
		$undoContent = $this->getUndoContent(
			$start->revision->getContent( 'wikitext' ),
			$stop->revision->getContent( 'wikitext' ),
			$current->revision->getContent( 'wikitext' )
		);

		$differenceEngine = new DifferenceEngine();
		$differenceEngine->setContent(
			new TextContent( $current->revision->getContent( 'wikitext' ) ),
			new TextContent( $undoContent )
		);

		$this->revisionViewFormatter->setContentFormat( 'wikitext' );

		// @todo if stop === current we could do a little less processing
		return array(
			'start' => $this->revisionViewFormatter->formatApi( $start, $context ),
			'stop' => $this->revisionViewFormatter->formatApi( $stop, $context ),
			'current' => $this->revisionViewFormatter->formatApi( $current, $context ),
			'undo' => array(
				'possible' => $undoContent !== false,
				'content' => $undoContent,
				'diff_content' => $differenceEngine->getDiffBody(),
			),
			'articleTitle' => $start->workflow->getArticleTitle(),
			// overrides the default modules list to only pull in ext.flow.undo
			'modules' => array( 'ext.flow.undo' ),
			'moduleStyles' => array( 'ext.flow.undo.styles' ),
		);
	}

	protected function getUndoContent( $startContent, $stopContent, $currentContent ) {

		if ( $currentContent === $stopContent ) {
			return $startContent;
		} else {
			// 3-way merge
			$ok = wfMerge( $stopContent, $startContent, $currentContent, $result );
			if ( $ok ) {
				return $result;
			} else {
				return false;
			}
		}
	}
}
