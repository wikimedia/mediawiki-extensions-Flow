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
use MultiHttpClient;
use OutputPage;
use RequestContext;
use Title;
use User;
use VirtualRESTServiceClient;

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

		try {
			return self::parsoid( $from, $to, $content, $title );
		} catch ( NoParsoidException $e ) {
			// If we have no parsoid config, fallback to the parser.
			return self::parser( $from, $to, $content, $title );
		}
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
		$serviceClient = self::getServiceClient();

		if ( $from == 'html' ) {
			$from = 'html';
		} elseif ( in_array( $from, array( 'wt', 'wikitext' ) ) ) {
			$from = 'wikitext';
		} else {
			throw new WikitextException( 'Unknown source format: ' . $from, 'process-wikitext' );
		}

		$prefixedDbTitle = $title->getPrefixedDBkey();
		$params = array(
			$from => $content,
			'bodyOnly' => 'true',
		);
		if ( $from === 'html' ) {
			$params['scrubWikitext'] = 'true';
		}
		$url = '/restbase/local/v1/transform/' . $from . '/to/' . $to . '/' .
			urlencode( $prefixedDbTitle );
		$request = array(
			'method' => 'POST',
			'url' => $url,
			'body' => $params,
		);
		$response = $serviceClient->run( $request );
		if ( $response['code'] !== 200 ) {
			if ( $response['error'] !== '' ) {
				$statusMsg = $response['error'];
			} else {
				$statusMsg = $response['code'];
			}
			$msg = "Failed contacting Parsoid for title \"$prefixedDbTitle\": $statusMsg";
			wfDebugLog( 'Flow', __METHOD__ . ": $msg" );
			throw new NoParsoidException( "$msg", 'process-wikitext' );
		}

		$content = $response['body'];
		// HACK remove trailing newline inserted by Parsoid (T106925)
		if ( $to === 'wikitext' ) {
			$content = preg_replace( '/\\n$/', '', $content );
		}
		return $content;
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
			throw new WikitextException( "Conversion from '$from' to '$to' was requested, but core's Parser only supports 'wikitext' to 'html' conversion", 'process-wikitext' );
		}

		global $wgParser;

		$options = new \ParserOptions;
		$options->setTidy( true );
		$options->setEditSection( false );

		$output = $wgParser->parse( $content, $title, $options );
		return $output->getText();
	}

	/**
	 * Check to see whether a Parsoid or RESTBase service is configured.
	 *
	 * @return boolean
	 */
	public static function isParsoidConfigured() {
		try {
			self::getServiceClient();
			return true;
		} catch ( NoParsoidException $e ) {
			return false;
		}
	}

	/**
	 * @var VirtualRESTServiceClient
	 */
	protected static $serviceClient = null;

	/**
	 * Returns Flow's Virtual REST Service for Parsoid/RESTBase.
	 * The Parsoid/RESTBase service will be mounted at /restbase/.
	 *
	 * @return VirtualRESTServiceClient
	 * @throws NoParsoidException When parsoid is unconfigured
	 */
	protected static function getServiceClient() {

		if ( self::$serviceClient === null ) {
			$sc = new VirtualRESTServiceClient( new MultiHttpClient( array() ) );
			$sc->mount( '/restbase/', self::getVRSObject() );
			self::$serviceClient = $sc;
		}
		return self::$serviceClient;
	}

	/**
	 * Creates the Virtual REST Service object to be used in Flow's
	 * API calls.  The method determines whether to instantiate a
	 * ParsoidVirtualRESTService or a RestbaseVirtualRESTService
	 * object based on configuration directives: if
	 * `$wgVirtualRestConfig['modules']['restbase']` is defined,
	 * RESTBase is chosen; otherwise Parsoid is used.
	 * For backwards compatibility, $wgFlowParsoid* variables are used
	 * to specify a Parsoid configuration as a fall back.
	 *
	 * @return \VirtualRESTService the VirtualRESTService object to use
	 * @throws NoParsoidException When parsoid is unconfigured
	 */
	private static function getVRSObject() {
		global $wgVirtualRestConfig, $wgFlowParsoidURL, $wgFlowParsoidPrefix,
			$wgFlowParsoidTimeout, $wgFlowParsoidForwardCookies,
			$wgFlowParsoidHTTPProxy;

		// the params array to create the service object with
		$params = array();
		// the VRS class to use; defaults to Parsoid
		$class = 'ParsoidVirtualRESTService';
		// the global virtual rest service config object, if any
		$vrs = $wgVirtualRestConfig;
		if ( isset( $vrs['modules'] ) && isset( $vrs['modules']['restbase'] ) ) {
			// if restbase is available, use it
			$params = $vrs['modules']['restbase'];
			$params['parsoidCompat'] = false; // backward compatibility
			$class = 'RestbaseVirtualRESTService';
		} elseif ( isset( $vrs['modules'] ) && isset( $vrs['modules']['parsoid'] ) ) {
			// there's a global parsoid config, use it next
			$params = $vrs['modules']['parsoid'];
			$params['restbaseCompat'] = true;
		} else {
			// no global modules defined, fall back to old defaults
			if ( !$wgFlowParsoidURL ) {
				throw new NoParsoidException( 'Flow Parsoid configuration is unavailable', 'process-wikitext' );
			}
			$params = array(
				'URL' => $wgFlowParsoidURL,
				'prefix' => $wgFlowParsoidPrefix,
				'timeout' => $wgFlowParsoidTimeout,
				'HTTPProxy' => $wgFlowParsoidHTTPProxy,
				'forwardCookies' => $wgFlowParsoidForwardCookies
			);
		}
		// merge the global and service-specific params
		if ( isset( $vrs['global'] ) ) {
			$params = array_merge( $vrs['global'], $params );
		}
		// set up cookie forwarding
		if ( $params['forwardCookies'] && !User::isEveryoneAllowed( 'read' ) ) {
			if ( PHP_SAPI === 'cli' ) {
				// From the command line we need to generate a cookie
				$params['forwardCookies'] = self::generateForwardedCookieForCli();
			} else {
				$params['forwardCookies'] = RequestContext::getMain()->getRequest()->getHeader( 'Cookie' );
			}
		} else {
			$params['forwardCookies'] = false;
		}
		// create the VRS object and return it
		return new $class( $params );
	}

	/**
	 * Turns given $content string into a DOMDocument object.
	 *
	 * Note that, by default, $content will be prefixed with <?xml encoding="utf-8"?> to force
	 * libxml to interpret the content as UTF-8. If for some reason you don't want this to happen,
	 * or you are certain that your input already has <?xml encoding="utf-8"?> or
	 * <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> , then you can disable
	 * this behavior by setting $utf8Fragment=false to disable this behavior.
	 *
	 * Some libxml errors are forgivable, libxml errors that aren't
	 * ignored will throw a WikitextException.
	 *
	 * The default error codes allowed are:
	 *        9 - allow illegal characters (they are removed, but this option means it
	 *             doesn't trigger an error.
	 * 	 76 - allow unexpected end tag. This is typically old wikitext using deprecated tags.
	 * 	513 - allow multiple tags with same id
	 * 	801 - allow unrecognized tags like figcaption
	 *
	 * @param string $content
	 * @param boolean[optional] $utf8Fragment If true, prefix $content with <?xml encoding="utf-8"?>
	 * @param array[optional] $ignoreErrorCodes
	 * @return DOMDocument
	 * @throws WikitextException
	 * @see http://www.xmlsoft.org/html/libxml-xmlerror.html
	 */
	public static function createDOM( $content, $utf8Fragment = true, $ignoreErrorCodes = array( 9, 76, 513, 801 ) ) {
		$dom = new DOMDocument();

		// Otherwise the parser may attempt to load the dtd from an external source.
		// See: https://www.mediawiki.org/wiki/XML_External_Entity_Processing
		$loadEntities = libxml_disable_entity_loader( true );

		// don't output warnings
		$useErrors = libxml_use_internal_errors( true );

		// Work around DOMDocument's morbid insistence on using iso-8859-1
		// Even $dom = new DOMDocument( '1.0', 'utf-8' ); doesn't work, you have to specify
		// encoding ="utf-8" in the string fed to loadHTML()
		$dom->loadHTML( ( $utf8Fragment ? '<?xml encoding="utf-8"?>' : '' ) . $content );

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

		if ( self::isParsoidConfigured() ) {
			// The module is only necessary when we are using parsoid.
			// XXX We only need the Parsoid CSS if some content being
			// rendered has getContentFormat() === 'html'.
			$out->addModuleStyles( array(
				'mediawiki.skinning.content.parsoid',
				'ext.cite.style',
			) );
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
		$text = preg_replace( '|^(\.\.?/)+|', '', $text );

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
