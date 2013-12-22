<?php

namespace Flow;

use Flow\Model\PostRevision;
use Closure;
use DOMDocument;
use DOMNode;
use LinkBatch;
use Linker;
use Title;

/**
 * Applys red links to an html fragment from parsoid. Relative
 * links are resolved in relation the the provided title.
 */
class Redlinker {

	/**
	 * @var Title To resolve relative links against
	 */
	protected $title;

	/**
	 * @var LinkBatch
	 */
	protected $batch;

	/**
	 * @var array
	 */
	protected $identifiers = array();

	/**
	 * @var array Array of registered post Ids
	 */
	protected $registered = array();

	/**
	 * @var array Array of processed post Ids
	 */
	protected $processed = array();

	/**
	 * @param Title $title To resolve relative links against
	 * @param LinkBatch $batch
	 */
	public function __construct( \Title $title, \LinkBatch $batch ) {
		$this->title = $title;
		$this->batch = $batch;
	}

	/**
	 * Registers callback function to find content links in Parsoid html.
	 * The goal is to batch-load and add to LinkCache as much links as possible.
	 *
	 * This can be registered on multiple posts (e.g. multiple topics) to
	 * batch-load as much as possible; all of the identifiers have to be
	 * saved and will be processed as soon as they first are needed.
	 */
	public function register( PostRevision $post ) {
		$revisionId = $post->getRevisionId()->getHex();
		if ( !isset( $this->processed[$revisionId], $this->registered[$revisionId] ) ) {
			$this->registered[$revisionId] = true;
			$identifier = $post->registerRecursive( array( $this, 'collectLinks' ), array(), 'parsoidlinks' );
			$this->identifiers[$identifier] = $post;
		}
	}

	/**
	 * DON'T CALL THIS METHOD!
	 * This is for internal use only: it's a callback function to
	 * PostRevision::registerRecursive, which can be registered via
	 * Templating::registerParsoidLinks.
	 *
	 * Returns an array of linked pages in Parsoid.
	 *
	 * @param PostRevision $post
	 * @param array $result
	 * @return array Return array in the format of [result, continue]
	 */
	public function collectLinks( PostRevision $post, $result ) {
		// topic titles don't contain html
		if ( $post->isTopicTitle() ) {
			return array( array(), true );
		}

		// make sure a post is not checked more than once
		$revisionId = $post->getRevisionId()->getHex();
		if ( isset( $this->processed[$revisionId] ) ) {
			return array( array(), false );
		}
		$this->processed[$revisionId] = true;

		/*
		 * Workaround because DOMDocument can't guess charset.
		 * Content should be utf-8. Alternative "workarounds" would be to
		 * provide the charset in $response, as either:
		 * * <?xml encoding="utf-8" ?>
		 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		 */
		$content = mb_convert_encoding( $post->getContent( 'html' ), 'HTML-ENTITIES', 'UTF-8' );

		// find links in DOM
		if ( $content ) {
			$dom = ParsoidUtils::createDOM( $content );
			$batch = $this->batch;
			self::forEachLink( $dom, function( DOMNode $linkNode, array $parsoid ) use( $batch ) {
				$title = Title::newFromText( $parsoid['sa']['href'] );
				if ( $title !== null ) {
					$batch->addObj( $title );
				}
			} );
		}

		/*
		 * $result will not be used; we'll register this callback multiple
		 * times and will want to gather overlapping results, so they'll
		 * be stored in $this->batch
		 */
		return array( array(), true );
	}

	/**
	 * Execute any pending batched title lookups
	 */
	public function resolveLinkStatus() {
		if ( !$this->identifiers ) {
			return;
		}
		foreach ( $this->identifiers as $identifier => $post ) {
			$post->getRecursiveResult( $identifier );
		}
		$this->identifiers = array();
		if ( !$this->batch->isEmpty() ) {
			$this->batch->execute();
			$this->batch->setArray( array() );
		}
	}

	/**
	 * Parsoid ignores red links. With good reason: redlinks should only be
	 * applied when rendering the content, not when it's created.
	 *
	 * This method will parse a given content, fetch all of its links & let MW's
	 * Linker class build the link HTML (which will take redlinks into account.)
	 * It will then substitute original link HTML for the one Linker generated.
	 *
	 * @param string $content
	 * @return string
	 */
	public function apply( $content ) {
		if ( !$content ) {
			return '';
		}

		$this->resolveLinkStatus();

		/*
		 * Workaround because DOMDocument can't guess charset.
		 * Content should be utf-8. Alternative "workarounds" would be to
		 * provide the charset in $response, as either:
		 * * <?xml encoding="utf-8" ?>
		 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		 */
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );

		$self = $this;
		$dom = ParsoidUtils::createDOM( $content );
		self::forEachLink( $dom, function( DOMNode $linkNode, array $parsoid ) use ( $self, $dom ) {
			$title = $self->createRelativeTitle( $parsoid['sa']['href'] );
			// Don't process invalid links
			if ( $title === null ) {
				return;
			}

			// gather existing link attributes
			$attributes = array();
			foreach ( $linkNode->attributes as $attribute ) {
				$attributes[$attribute->name] = $attribute->value;
			}
			// let MW build link HTML based on Parsoid data
			$html = Linker::link( $title, htmlspecialchars( $linkNode->nodeValue ), $attributes );
			// create new DOM from this MW-built link
			$replacementNode = ParsoidUtils::createDOM( $html )->getElementsByTagName( 'a' )->item( 0 );
			// import MW-built link node into content DOM
			$replacementNode = $dom->importNode( $replacementNode, true );
			// replace Parsoid link with MW-built link
			$linkNode->parentNode->replaceChild( $replacementNode, $linkNode );
		} );

		return $dom->saveHTML();
	}

	/**
	 * Subpage links from parsoid don't contain any direct context, its applied via
	 * a <base href="..."> tag, so here we apply a similar rule resolving against
	 * $wgFlowParsoidTitle falling back to $wgTitle.
	 */
	public function createRelativeTitle( $text ) {
		if ( $text && $text[0] === '/' ) {
			return Title::newFromText( $this->title->getDBkey() . $text, $this->title->getNamespace() );
		} else {
			return Title::newFromText( $text );
		}
	}

	/**
	 * Helper method executes a callback on every anchor that contains
	 * an ['sa']['href'] value in data-parsoid
	 *
	 * @param string $content Html
	 * @param Closure $callback Receives (DOMNode, array)
	 */
	static public function forEachLink( DOMDocument $dom, Closure $callback ) {
		$xpath = new \DOMXPath( $dom );
		$linkNodes = $xpath->query( '//a[@rel="mw:WikiLink"][@data-parsoid]' );

		$links = array();
		foreach ( $linkNodes as $linkNode ) {
			$parsoid = $linkNode->getAttribute( 'data-parsoid' );
			$parsoid = json_decode( $parsoid, true );
			if ( isset( $parsoid['sa']['href'] ) ) {
				$callback( $linkNode, $parsoid );
			}
		}
	}
}

