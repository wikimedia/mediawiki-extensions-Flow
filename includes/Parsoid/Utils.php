<?php

namespace Flow\Parsoid;

use DOMDocument;
use DOMNode;
use Language;
use OutputPage;
use RequestContext;
use Title;
use User;
use Flow\Exception\WikitextException;
use Flow\Exception\InvalidDataException;

abstract class Utils {
	/**
	 * Convert from/to wikitext/html.
	 *
	 * @param string $from Format of content to convert: html|wikitext
	 * @param string $to Format to convert to: html|wikitext
	 * @param string $content
	 * @param Title $title
	 * @return string
	 * @throws InvalidDataException When $title does not exist
	 */
	public static function convert( $from, $to, $content, Title $title ) {
		if ( $from === $to || $content === '' ) {
			return $content;
		}

		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );

		try {
			// use VE API (which connects to Parsoid) if available...
			$res = self::parsoid( $from, $to, $content, $title );
		} catch ( NoParsoidException $e ) {
			// ... otherwise default to parser
			$res = self::parser( $from, $to, $content, $title );
		}
		return $res;
	}

	/**
	 * Basic conversion of html to plaintext for use in recent changes, history,
	 * and other places where a roundtrip is undesired.
	 *
	 * @param string $html
	 * @param int|null $truncateLength Maximum length (including ellipses) or null for whole string.
	 * @param Language $lang Language to use for truncation.  Defaults to $wgLang
	 * @return string plaintext
	 */
	public static function htmlToPlaintext( $html, $truncateLength = null, Language $lang = null ) {
		/** @var Language $wgLang */
		global $wgLang;

		$plain = trim( html_entity_decode( strip_tags( $html ) ) );

		if ( $truncateLength === null ) {
			return $plain;
		} else {
			$lang = $lang ?: $wgLang;
			return $lang->truncate( $plain, $truncateLength );
		}
	}

	/**
	 * Convert from/to wikitext/html via Parsoid.
	 *
	 * This will assume Parsoid is installed.
	 *
	 * @param string $from Format of content to convert: html|wikitext
	 * @param string $to Format to convert to: html|wikitext
	 * @param string $content
	 * @param Title $title
	 * @return string
	 * @throws NoParsoidException When parsoid configuration is not available
	 * @throws WikitextException When conversion is unsupported
	 */
	protected static function parsoid( $from, $to, $content, Title $title ) {
		list( $parsoidURL, $parsoidPrefix, $parsoidTimeout, $parsoidForwardCookies ) = self::parsoidConfig();
		if ( !isset( $parsoidURL ) || !$parsoidURL ) {
			throw new NoParsoidException( 'Flow Parsoid configuration is unavailable' );
		}

		if ( $from == 'html' ) {
			$from = 'html';
		} elseif ( in_array( $from, array( 'wt', 'wikitext' ) ) ) {
			$from = 'wt';
		} else {
			throw new WikitextException( 'Unknown source format: ' . $from, 'process-wikitext' );
		}

		$request = \MWHttpRequest::factory(
			$parsoidURL . '/' . $parsoidPrefix . '/' . $title->getPrefixedDBkey(),
			array(
				'method' => 'POST',
				'postData' => wfArrayToCgi( array( $from => $content ) ),
				'body' => true,
				'timeout' => $parsoidTimeout,
				'connectTimeout' => 'default',
			)
		);
		if ( $parsoidForwardCookies && !User::isEveryoneAllowed( 'read' ) ) {
			$req->setHeader( 'Cookie', RequestContext::getMain()->getRequest()->getHeader( 'Cookie' ) );
		}
		$status = $request->execute();
		if ( !$status->isOK() ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failed contacting parsoid: ' . $status->getMessage()->text() );
			throw new WikitextException( 'Failed contacting Parsoid', 'process-wikitext' );
		}
		$response = $request->getContent();

		// HTML is wrapped in <body> tag, undo that.
		// unless $response is empty
		if ( $to == 'html' && $response ) {
			/*
			 * Workaround because DOMDocument can't guess charset.
			 * Parsoid provides utf-8. Alternative "workarounds" would be to
			 * provide the charset in $response, as either:
			 * * <?xml encoding="utf-8" ?>
			 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			 */
			$response = mb_convert_encoding( $response, 'HTML-ENTITIES', 'UTF-8' );

			$dom = self::createDOM( $response );
			$body = $dom->getElementsByTagName( 'body' )->item(0);

			$response = '';
			foreach( $body->childNodes as $child ) {
				$response .= $child->ownerDocument->saveHTML( $child );
			}
		} elseif ( !in_array( $to, array( 'wt', 'wikitext' ) ) ) {
			throw new WikitextException( "Unknown format requested: " . $to, 'process-wikitext' );
		}

		return $response;
	}

	/**
	 * Convert from/to wikitext/html using Parser.
	 *
	 * This only supports wikitext to HTML.
	 *
	 * @param string $from Format of content to convert: wikitext
	 * @param string $to Format to convert to: html
	 * @param string $content
	 * @param Title $title
	 * @return string
	 * @throws WikitextException When the conversion is unsupported
	 */
	protected static function parser( $from, $to, $content, Title $title ) {
		if ( $from !== 'wikitext' && $to !== 'html' ) {
			throw new WikitextException( 'Parser only supports wikitext to HTML conversion', 'process-wikitext' );
		}

		global $wgParser;

		$options = new \ParserOptions;
		$options->setTidy( true );
		$options->setEditSection( false );

		$output = $wgParser->parse( $content, $title, $options );
		return $output->getText();
	}

	/**
	 * Returns Flow's Parsoid config. $wgFlowParsoid* variables can be used to
	 * specify a certain Parsoid installation. If none specified, we'll piggy-
	 * back on VisualEditor's Parsoid setup.
	 *
	 * @return array Parsoid config, in array(URL, prefix, timeout, forwardCookies) format
	 */
	protected static function parsoidConfig() {
		global
			$wgFlowParsoidURL, $wgFlowParsoidPrefix, $wgFlowParsoidTimeout, $wgFlowParsoidForwardCookies,
			$wgVisualEditorParsoidURL, $wgVisualEditorParsoidPrefix, $wgVisualEditorParsoidTimeout,
			$wgVisualEditorParsoidForwardCookies;

		return array(
			$wgFlowParsoidURL ?: $wgVisualEditorParsoidURL,
			$wgFlowParsoidPrefix ?: $wgVisualEditorParsoidPrefix,
			$wgFlowParsoidTimeout ?: $wgVisualEditorParsoidTimeout,
			isset( $wgFlowParsoidForwardCookies )
				? $wgFlowParsoidForwardCookies
				: $wgVisualEditorParsoidForwardCookies,
		);
	}

	/**
	 * Turns given $content string into a DOMDocument object.
	 *
	 * Some libxml errors are forgivable, libxml errors that aren't
	 * ignored will throw a WikitextException.
	 *
	 * The default error codes allowed are:
	 * 	513 - allow multiple tags with same id
	 * 	801 - allow unrecognized tags like figcaption
	 *
	 * @param string $content
	 * @param array[optional] $ignoreErrorCodes
	 * @return DOMDocument
	 * @throws WikitextException
	 * @see http://www.xmlsoft.org/html/libxml-xmlerror.html
	 */
	public static function createDOM( $content, $ignoreErrorCodes = array( 513, 801 ) ) {
		$dom = new DOMDocument();

		// Otherwise the parser may attempt to load the dtd from an external source.
		// See: https://www.mediawiki.org/wiki/XML_External_Entity_Processing
		$loadEntities = libxml_disable_entity_loader( true );

		// don't output warnings
		$useErrors = libxml_use_internal_errors( true );

		$dom->loadHTML( $content );

		libxml_disable_entity_loader( $loadEntities );

		// check error codes; if not in the supplied list of ignorable errors,
		// throw an exception
		$errors = array_filter(
			libxml_get_errors(),
			function( $error ) use( $ignoreErrorCodes ) {
				return !in_array( $error->code, $ignoreErrorCodes );
			}
		);

		// restore libxml state before anything else
		libxml_clear_errors();
		libxml_use_internal_errors( $useErrors );

		if ( $errors ) {
			throw new WikitextException(
				implode( "\n", array_map( function( $error ) { return $error->message; }, $errors ) ),
				'process-wikitext'
			);
		}

		return $dom;
	}

	/**
	 * Handler for FlowAddModules, avoids rest of Flow having to be aware if
	 * Parsoid is in use.
	 *
	 * @param OutputPage $out OutputPage object
	 * @return bool
	 */
	public static function onFlowAddModules( OutputPage $out ) {

		list( $parsoidURL ) = self::parsoidConfig();
		if ( isset( $parsoidURL ) && $parsoidURL ) {
			// XXX We only need the Parsoid CSS if some content being
			// rendered has getContentFormat() === 'html'.
			$out->addModules( 'mediawiki.skinning.content.parsoid' );
		}
		return true;
	}

	/**
	 * Retrieves the html of the nodes children.
	 *
	 * @param DOMNode|null $node
	 * @return string html of the nodes children
	 */
	public static function getInnerHtml( DOMNode $node = null ) {
		$html = array();
		if ( $node ) {
			$dom = $node instanceof DOMDocument ? $node : $node->ownerDocument;
			foreach ( $node->childNodes as $child ) {
				$html[] = $dom->saveHTML( $child );
			}
		}
		return implode( '', $html );
	}

	/**
	 * Subpage links from Parsoid don't contain any direct context, its applied via
	 * a <base href="..."> tag, so here we apply a similar rule resolving against
	 * $title
	 *
	 * @param string $text
	 * @param Title $title Title to resolve relative links against
	 * @return Title|null
	 */
	public static function createRelativeTitle( $text, Title $title ) {
		// currently parsoid always uses enough ../ or ./ to go
		// back to the root, a bit of a kludge but just assume we
		// can strip and will end up with a non-relative text.
		$text = preg_replace( '|(\.\.?/)+|', '', $text );

		if ( $text && ( $text[0] === '/' || $text[0] === '#' ) ) {
			return Title::newFromText( $title->getDBkey() . $text, $title->getNamespace() );
		}

		return Title::newFromText( $text );
	}
}

class NoParsoidException extends \MWException {}
