<?php

namespace Flow\Parsoid;

use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Title;

class Controller {
	/**
	 * @var ContentFixer[] Array of ContentFixer objects
	 */
	protected $contentFixers = array();

	/**
	 * @var int[PostRevision[]] Multi-dimensional map of contentfixer indexes to
	 * map of recursion identifier to related revision
	 */
	protected $identifiers = array();

	/**
	 * @var array Array of registered PostRevision objects [revision id => true]
	 */
	protected $registered = array();

	/**
	 * Accepts multiple content fixers.
	 *
	 * @param ContentFixer $contentFixer...
	 * @throws FlowException When provided arguments are not an instance of ContentFixer
	 */
	public function __construct( ContentFixer $contentFixer /* [, ContentFixer $contentFixer2 [, ...]] */ ) {
		$this->contentFixers = func_get_args();

		// validate data
		foreach ( $this->contentFixers as $contentFixer ) {
			if ( !$contentFixer instanceof ContentFixer ) {
				throw new FlowException( 'Invalid content fixer', 'default' );
			}
		}
	}

	/**
	 * @param AbstractRevision $revision
	 * @return string
	 */
	public function getContent( AbstractRevision $revision ) {
		return $this->apply(
			$revision->getContent( 'html' ),
			$revision->getCollection()->getTitle()
		);
	}

	/**
	 * @param string $content Html
	 * @param Title $title
	 * @return string Html
	 */
	public function apply( $content, Title $title ) {
		// resolve all registered recursive callbacks
		$this->resolveRecursive();

		foreach ( $this->contentFixers as $contentFixer ) {
			$content = $contentFixer->apply( $content, $title );
		}

		return $content;
	}

	/**
	 * Registers callback function to pre-process multiple revisions at once.
	 * This could come in useful to perform multiple operations in batch.
	 * Redlinker, for example, will have to resolve links & those will be batch-
	 * loaded and added to LinkCache all at once.
	 *
	 * This can be registered on multiple revisions to batch-load as much as
	 * possible; all of the identifiers have to be saved and will be processed
	 * as soon as they first are needed.
	 *
	 * @param PostRevision $revision
	 */
	public function registerRecursive( PostRevision $revision ) {
		// only register once
		$revisionId = $revision->getRevisionId()->getAlphadecimal();
		if ( isset( $this->registered[$revisionId] ) ) {
			return;
		}
		$this->registered[$revisionId] = true;

		foreach ( $this->contentFixers as $i => $contentFixer ) {
			$identifier = $revision->registerRecursive( array( $contentFixer, 'recursive' ), array(), __METHOD__ );
			$this->identifiers[$i][$identifier] = $revision;
		}
	}

	/**
	 * Descends all registered callbacks recursively for all registered
	 * revisions, for all content fixers. The results are passed to all content
	 * fixers' resolve() method.
	 */
	public function resolveRecursive() {
		if ( !$this->identifiers ) {
			return;
		}

		foreach ( $this->contentFixers as $i => $contentFixer ) {
			$results = array();

			foreach ( $this->identifiers[$i] as $identifier => $revision ) {
				/** @var PostRevision $revision */

				/*
				 * Adding additional content fixers will not cause the recursive
				 * function to be run more - all content fixers will be resolved
				 * at once, the next content fixer fetching his results will
				 * just have them fed from the revision object's inner cache.
				 */
				$revisionId = $revision->getRevisionId()->getAlphadecimal();
				$results[$revisionId] = $revision->getRecursiveResult( $identifier );
			}

			/*
			 * Notify content fixer with all recursive results, so it can do
			 * whatever needs to be done (like batch-loading stuff)
			 */
			$contentFixer->resolve( $results );
		}
	}
}
