<?php

namespace Flow\Parsoid;

use DOMDocument;
use DOMXPath;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Title;

class ContentFixer {
	/**
	 * @var Fixer[] Array of Fixer objects
	 */
	protected $contentFixers = array();

	/**
	 * @var PostRevision[] Map of recursion identifiers to related revision
	 */
	protected $identifiers = array();

	/**
	 * @var array Array of registered PostRevision objects [revision id => true]
	 */
	protected $registered = array();

	/**
	 * Accepts multiple content fixers.
	 *
	 * @param Fixer $contentFixer...
	 * @throws FlowException When provided arguments are not an instance of Fixer
	 */
	public function __construct( Fixer $contentFixer /* [, Fixer $contentFixer2 [, ...]] */ ) {
		$this->contentFixers = func_get_args();

		// validate data
		foreach ( $this->contentFixers as $contentFixer ) {
			if ( !$contentFixer instanceof Fixer ) {
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

		$dom = self::createDOM( $content );
		$xpath = new DOMXPath( $dom );
		foreach ( $this->contentFixers as $i => $contentFixer ) {
			$found = $xpath->query( $contentFixer->getXPath() );
			if ( !$found ) {
				wfDebugLog( 'Flow', __METHOD__ . ': Invalid XPath from ' . get_class( $contentFixer ) . ' of: ' . $contentFixer->getXPath() );
				unset( $this->contentFixers[$i] );
				continue;
			}

			foreach ( $found as $node ) {
				$contentFixer->apply( $node, $title );
			}
		}

		return Utils::getInnerHtml( $dom->getElementsByTagName( 'body' )->item( 0 ) );
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
		$identifier = $revision->registerRecursive( array( $this, 'recursive' ), array(), __METHOD__ );
		$this->identifiers[$identifier] = $revision;
	}

	/**
	 * @param PostRevision $post
	 * @param array $result
	 * @return array
	 */
	public function recursive( PostRevision $post, $result ) {
		$dom = self::createDOM( $post->getContent( 'html' ) );
		$xpath = new DOMXPath( $dom );
		foreach ( $this->contentFixers as $i => $contentFixer ) {
			if ( !$contentFixer->isRecursive( $post ) ) {
				continue;
			}

			$found = $xpath->query( $contentFixer->getXPath() );
			if ( !$found ) {
				wfDebugLog( 'Flow', __METHOD__ . ': Invalid XPath from ' . get_class( $contentFixer ) . ' of: ' . $contentFixer->getXPath() );
				unset( $this->contentFixers[$i] );
				continue;
			}

			foreach ( $found as $node ) {
				$contentFixer->recursive( $node );
			}
		}

		/**
		 * $result wil not be used; we'll register this callback multiple
		 * times and will want to gather overlapping results, so they'll be
		 * stored in the individual fixers
		 */
		return array( array(), true );
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

		foreach ( $this->identifiers as $identifier => $revision ) {
			/** @var PostRevision $revision */

			/*
			 * Adding additional content fixers will not cause the recursive
			 * function to be run more - all content fixers will be resolved
			 * at once, the next content fixer fetching his results will
			 * just have them fed from the revision object's inner cache.
			 */
			$revision->getRecursiveResult( $identifier );
		}

		/*
		 * Notify content fixer we have finished recursive processing, so it
		 * can do whatever needs to be done (like batch-loading stuff)
		 */
		foreach ( $this->contentFixers as $contentFixer ) {
			$contentFixer->resolve();
		}
	}

	/**
	 * Helper functions creates a DOM extra considerations for BC with
	 * previous parsoid content and encoding issues.
	 *
	 * @param string $content
	 * @return DOMDocument
	 */
	static public function createDOM( $content ) {
		/*
		 * Workaround because DOMDocument can't guess charset.
		 * Content should be utf-8. Alternative "workarounds" would be to
		 * provide the charset in $response, as either:
		 * * <?xml encoding="utf-8" ?>
		 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		 * * mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		 *
		 * The body tag is required otherwise <meta> tags at the top are
		 * magic'd into <head> rather than kept with the content.
		 */
		if ( substr( $content, 0, 5 ) !== '<body' ) {
			// BC: content currently comes from parsoid and is stored wrapped in <body> tags, but prior
			// to feb 2015 we were storing only the contents and not the body tag itself.
			$content = "<body>$content</body>";
		}
		return Utils::createDOM( '<?xml encoding="utf-8"?>' . $content );
	}
}
