<?php

namespace Flow\Parsoid;

use DOMDocument;
use DOMNode;
use FauxResponse;
use Flow\Container;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\NoParsoidException;
use Flow\Exception\WikitextException;
use Language;
use OutputPage;
use RequestContext;
use Title;
use User;

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
		/** @var Converter */
		$converter = Container::get( 'content_converter' );
		return $converter->convert( $from, $to, $content, $title );
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
				implode( "\n", array_map( function( $error ) { return $error->message; }, $errors ) )
				. "\n\nFrom source content:\n" . $content,
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
		/** @var Converter */
		$converter = Container::get( 'content_converter' );
		$modules = $converter->getRequiredModules();
		if ( $modules ) {
			$out->addModules( $modules );
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

	// @todo move into FauxRequest
	public static function generateForwardedCookieForCli() {
		global $wgCookiePrefix;

		$user = Container::get( 'occupation_controller' )->getTalkpageManager();
		// This takes a request object, but doesnt set the cookies against it.
		// patch at https://gerrit.wikimedia.org/r/177403
		$user->setCookies( null, null, /* rememberMe */ true );
		$response = RequestContext::getMain()->getRequest()->response();
		if ( !$response instanceof FauxResponse ) {
			throw new FlowException( 'Expected a FauxResponse in CLI environment' );
		}
		// FauxResponse does not yet expose the full set of cookies
		$reflProp = new \ReflectionProperty( $response, 'cookies' );
		$reflProp->setAccessible( true );
		$cookies = $reflProp->getValue( $response );

		// now we need to convert the array into the cookie format of
		// foo=bar; baz=bang
		$output = array();
		foreach ( $cookies as $key => $value ) {
			$output[] = "$wgCookiePrefix$key=$value";
		}

		return implode( '; ', $output );
	}
}
