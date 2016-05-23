<?php

namespace Flow\Formatter;

use Flow\RevisionActionPermissions;
use Flow\WorkflowLoaderFactory;
use Linker;

/**
 * Formats single lines for inclusion on the page renders in
 * NS_CATEGORY.  Expects that all rows passed in are topic
 * titles.
 */
class CategoryViewerFormatter {
	/**
	 * @var RevisionActionPermissions
	 */
	protected $permissions;

	public function __construct( RevisionActionPermissions $permissions ) {
		$this->permissions = $permissions;
	}

	public function format( FormatterRow $row ) {
		if ( !$this->permissions->isAllowed( $row->revision, 'view' ) ) {
			return '';
		}

		$topic = Linker::link(
			WorkflowLoaderFactory::getPrettyArticleTitle( $row->workflow, $row->rootPost ),
			htmlspecialchars( $row->revision->getContent( 'topic-title-wikitext' ) ),
			array( 'class' => 'mw-title' )
		);

		$board = Linker::link( $row->workflow->getOwnerTitle() );

		return wfMessage( 'flow-rc-topic-of-board' )->rawParams( $topic, $board )->escaped();
	}
}
