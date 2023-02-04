<?php

namespace Flow\Conversion;

use DOMDocument;
use DOMElement;
use DOMNode;
use Flow\Exception\NoParserException;
use Flow\Exception\WikitextException;
use Flow\Parsoid\ContentFixer;
use Flow\Parsoid\Fixer\EmptyNodeFixer;
use Html;
use ILanguageConverter;
use Language;
use MediaWiki\MediaWikiServices;
use OutputPage;
use ParserOptions;
use Sanitizer;
use TextContent;
use Title;
use WikitextContent;

abstract class Utils {

	public const PARSOID_VERSION = '2.0.0';

	/**
	 * Convert from/to wikitext <=> html or topic-title-wikitext => topic-title-html.
	 * Only these pairs are supported.  html => wikitext requires Parsoid, and
	 * topic-title-html => topic-title-wikitext is not supported.
	 *
	 * @param string $from Format of content to convert: html|wikitext|topic-title-wikitext
	 * @param string $to Format to convert to: html|wikitext|topic-title-html
	 * @param string $content
	 * @param Title $title
	 * @return string
	 * @throws WikitextException When the requested conversion is unsupported
	 * @throws NoParserException When the conversion fails
	 * @return-taint none
	 */
	public static function convert( $from, $to, $content, Title $title ) {
		if ( $from === $to || $content === '' ) {
			return $content;
		}

		if ( $from === 'wt' ) {
			$from = 'wikitext';
		}

		if ( $from == 'wikitext' && $to == 'html' ) {
			return self::wikitextToHTML( $content, $title );
		} elseif ( $from == 'html' && $to == 'wikitext' ) {
			return self::htmlToWikitext( $content, $title );
		} elseif ( $from === 'topic-title-wikitext' &&
			( $to === 'topic-title-html' || $to === 'topic-title-plaintext' ) ) {
			// FIXME: links need to be proceed by findVariantLinks or equivant function
			return self::getLanguageConverter()->convert( self::commentParser( $from, $to, $content ) );
		} else {
			return self::commentParser( $from, $to, $content );
		}
	}

	/**
	 * @param string $wikitext
	 * @param Title $title
	 *
	 * @return string The converted wikitext to HTML
	 */
	private static function wikitextToHTML( string $wikitext, Title $title ) {
		$parserOptions = ParserOptions::newFromAnon();
		$parserOptions->setRenderReason( __METHOD__ );

		$parserFactory = MediaWikiServices::getInstance()->getParsoidParserFactory()->create();
		$parserOutput = $parserFactory->parse( $wikitext, $title, $parserOptions );

		// $parserOutput->getText() will strip off the body tag, but we want to retain here.
		// So we'll call ->getRawText() here and modify the HTML by ourselves.
		preg_match( "#<body[^>]*>(.*?)</body>#s", $parserOutput->getRawText(), $html );

		return $html[0];
	}

	/**
	 * @param string $html
	 * @param Title $title
	 *
	 * @return string The converted HTML to Wikitext
	 * @throws WikitextException When the conversion is unsupported
	 */
	private static function htmlToWikitext( string $html, Title $title ) {
		$transform = MediaWikiServices::getInstance()->getHtmlTransformFactory()
			->getHtmlToContentTransform( $html, $title );

		$transform->setOptions( [
			'contentmodel' => CONTENT_MODEL_WIKITEXT,
			'offsetType' => 'byte'
		] );

		/** @var TextContent $content */
		$content = $transform->htmlToContent();

		if ( !$content instanceof WikitextContent ) {
			throw new WikitextException( 'Conversion to Wikitext failed' );
		}

		return trim( $content->getTextForSearchIndex() );
	}

	/**
	 * Basic conversion of html to plaintext for use in recent changes, history,
	 * and other places where a roundtrip is undesired.
	 *
	 * @param string $html
	 * @param int|null $truncateLength Maximum length in characters (including ellipses) or null for whole string.
	 * @param Language|null $lang Language to use for truncation.  Defaults to $wgLang
	 * @return string plaintext
	 */
	public static function htmlToPlaintext( $html, ?int $truncateLength = null, Language $lang = null ) {
		/** @var Language $wgLang */
		global $wgLang;

		$plain = trim( Sanitizer::stripAllTags( $html ) );

		// Fallback to some large-ish value for truncation.
		if ( $truncateLength === null ) {
			$truncateLength = 10000;
		}

		$lang = $lang ?: $wgLang;
		return $lang->truncateForVisual( $plain, $truncateLength );
	}

	/**
	 * Convert from/to topic-title-wikitext/topic-title-html using
	 * MediaWiki\CommentFormatter\CommentFormatter::formatLinks
	 *
	 * @param string $from Format of content to convert: topic-title-wikitext
	 * @param string $to Format of content to convert to: topic-title-html
	 * @param string $content Content to convert, in topic-title-wikitext format.
	 * @return string $content in HTML
	 * @throws WikitextException
	 */
	protected static function commentParser( $from, $to, $content ) {
		if (
			$from !== 'topic-title-wikitext' ||
			( $to !== 'topic-title-html' && $to !== 'topic-title-plaintext' )
		) {
			throw new WikitextException( "Conversion from '$from' to '$to' was requested, " .
				"but this is not supported." );
		}

		$html = MediaWikiServices::getInstance()->getCommentFormatter()
			->formatLinks( Sanitizer::escapeHtmlAllowEntities( $content ) );
		if ( $to === 'topic-title-plaintext' ) {
			return self::htmlToPlaintext( $html );
		} else {
			return $html;
		}
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
	 * @param bool $utf8Fragment If true, prefix $content with <?xml encoding="utf-8"?>
	 * @param array $ignoreErrorCodes
	 * @return DOMDocument
	 * @throws WikitextException
	 * @see http://www.xmlsoft.org/html/libxml-xmlerror.html
	 */
	public static function createDOM(
		$content,
		$utf8Fragment = true,
		array $ignoreErrorCodes = [ 9, 76, 513, 801 ]
	) {
		$dom = new DOMDocument();

		$loadEntities = false;
		if ( LIBXML_VERSION < 20900 ) {
			// Otherwise the parser may attempt to load the dtd from an external source.
			// See: https://www.mediawiki.org/wiki/XML_External_Entity_Processing
			$loadEntities = libxml_disable_entity_loader( true );
		}

		// don't output warnings
		$useErrors = libxml_use_internal_errors( true );

		// Work around DOMDocument's morbid insistence on using iso-8859-1
		// Even $dom = new DOMDocument( '1.0', 'utf-8' ); doesn't work, you have to specify
		// encoding ="utf-8" in the string fed to loadHTML()
		$html = ( $utf8Fragment ? '<?xml encoding="utf-8"?>' : '' ) . $content;
		$dom->loadHTML( $html, LIBXML_PARSEHUGE );

		if ( LIBXML_VERSION < 20900 ) {
			libxml_disable_entity_loader( $loadEntities );
		}

		// check error codes; if not in the supplied list of ignorable errors,
		// throw an exception
		$errors = array_filter(
			libxml_get_errors(),
			static function ( $error ) use( $ignoreErrorCodes ) {
				return !in_array( $error->code, $ignoreErrorCodes );
			}
		);

		// restore libxml state before anything else
		libxml_clear_errors();
		libxml_use_internal_errors( $useErrors );

		if ( $errors ) {
			throw new WikitextException(
				implode(
					"\n",
					array_map(
						static function ( $error ) {
							return $error->message;
						},
						$errors
					)
				) . "\n\nFrom source content:\n" . $content,
				'process-wikitext'
			);
		}

		return $dom;
	}

	/**
	 * Handler for FlowAddModules, avoids rest of Flow having to be aware if
	 * Parsoid is in use.
	 *
	 * @param OutputPage $out
	 * @return bool
	 */
	public static function onFlowAddModules( OutputPage $out ) {
		// The module is only necessary when we are using parsoid.
		// XXX We only need the Parsoid CSS if some content being
		// rendered has getContentFormat() === 'html'.
		$out->addModuleStyles( [
			'mediawiki.skinning.content.parsoid',
			'ext.cite.style',
		] );

		return true;
	}

	/**
	 * Saves a document using saveXML, but avoid escaping style blocks with CDATA.
	 * This is not needed in HTML and breaks the CSS.
	 *
	 * @param DOMDocument $doc
	 * @param DOMNode|null $node the specific node to save
	 * @return string HTML
	 */
	public static function saferSaveXML( DOMDocument $doc, DOMNode $node = null ) {
		$html = $doc->saveXML( $node );
		// This regex is only safe as long as attribute values get escaped > chars
		// This is checked by the testcases
		$html = preg_replace( '/<style([^>]*)><!\[CDATA\[/i', '<style\1>', $html );
		return preg_replace( '/\]\]><\/style>/i', '</style>', $html );
	}

	/**
	 * Retrieves the html of the node's children.
	 *
	 * @param DOMNode|null $node
	 * @return string html of the nodes children
	 */
	public static function getInnerHtml( DOMNode $node = null ) {
		$html = '';
		if ( $node ) {
			$dom = $node instanceof DOMDocument ? $node : $node->ownerDocument;
			// Don't use saveHTML(), it has bugs (T217766); instead use XML serialization
			// with a workaround for empty non-void nodes
			$fixer = new ContentFixer( new EmptyNodeFixer );
			$fixer->applyToDom( $dom, Title::newMainPage() );

			foreach ( $node->childNodes as $child ) {
				$html .= self::saferSaveXML( $dom, $child );
			}
		}
		return $html;
	}

	/**
	 * Gets the HTML of a node. This is like getInnterHtml(), but includes the node's tag itself too.
	 * @param DOMNode $node
	 * @return string HTML
	 */
	public static function getOuterHtml( DOMNode $node ) {
		$dom = $node instanceof DOMDocument ? $node : $node->ownerDocument;
		// Don't use saveHTML(), it has bugs (T217766); instead use XML serialization
		// with a workaround for empty non-void nodes
		$fixer = new ContentFixer( new EmptyNodeFixer );
		$fixer->applyToDom( $dom, Title::newMainPage() );
		return self::saferSaveXML( $dom, $node );
	}

	/**
	 * Encode information from the <head> tag as attributes on the <body> tag, then
	 * drop the <head>.
	 *
	 * Specifically, add the Parsoid version number in the parsoid-version attribute;
	 * put the href of the <base> tag in the base-url attribute;
	 * and remove the class attribute from the <body>.
	 *
	 * @param string $html
	 * @return string HTML with <head> information encoded as attributes on the <body>
	 * @throws WikitextException
	 * @suppress PhanUndeclaredMethod,PhanTypeMismatchArgumentNullable Apparently a phan bug / wrong built-in PHP stubs
	 */
	public static function encodeHeadInfo( $html ) {
		$dom = ContentFixer::createDOM( $html );
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		$base = $head ? $head->getElementsByTagName( 'base' )->item( 0 ) : null;
		$body->setAttribute( 'parsoid-version', self::PARSOID_VERSION );
		if ( $base instanceof DOMElement && $base->getAttribute( 'href' ) ) {
			$body->setAttribute( 'base-url', $base->getAttribute( 'href' ) );
		}
		// The class attribute is not used by us and is wastefully long, remove it
		$body->removeAttribute( 'class' );
		return self::getOuterHtml( $body );
	}

	/**
	 * Put the base URI from the <body>'s base-url attribute back in the <head> as a <base> tag.
	 * This reverses (part of) the transformation done by encodeHeadInfo().
	 *
	 * @param string $html HTML (may be a full document, <body> tag  or unwrapped <body> contents)
	 * @return string HTML (<html> tag with <head> and <body>) with the <base> tag restored
	 * @throws WikitextException
	 * @suppress PhanUndeclaredMethod,PhanTypeMismatchArgumentNullable Apparently a phan bug / wrong built-in PHP stubs
	 */
	public static function decodeHeadInfo( $html ) {
		$dom = ContentFixer::createDOM( $html );
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$baseUrl = $body->getAttribute( 'base-url' );
		return Html::rawElement( 'html', [],
			Html::rawElement( 'head', [],
				// Only set base href if there's a value to set.
				$baseUrl ? Html::element( 'base', [ 'href' => $baseUrl ] ) : ''
			) .
			self::getOuterHtml( $body )
		);
	}

	/**
	 * Get the Parsoid version from HTML content stored in the database.
	 * This interprets the transformation done by encodeHeadInfo().
	 *
	 * @param string $html
	 * @return string|null Parsoid version number, or null if none found
	 * @suppress PhanUndeclaredMethod Apparently a phan bug / wrong built-in PHP stubs
	 */
	public static function getParsoidVersion( $html ) {
		$dom = ContentFixer::createDOM( $html );
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$version = $body->getAttribute( 'parsoid-version' );
		return $version !== '' ? $version : null;
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

	/**
	 * @since 1.35
	 * @return ILanguageConverter
	 */
	private static function getLanguageConverter(): ILanguageConverter {
		$services = MediaWikiServices::getInstance();
		return $services
			->getLanguageConverterFactory()
			->getLanguageConverter( $services->getContentLanguage() );
	}

	/**
	 * @since 1.35
	 * @param Title $title Title to convert to language variant
	 * @return string Converted title
	 */
	public static function getConvertedTitle( Title $title ) {
		$ns = $title->getNamespace();
		$titleText = $title->getText();
		$langConv = self::getLanguageConverter();
		$variant = $langConv->getPreferredVariant();
		$convertedNamespace = $langConv->convertNamespace( $ns, $variant );
		if ( $convertedNamespace ) {
			return $convertedNamespace . ':' . $langConv->translate( $titleText, $variant );
		} else {
			return $langConv->translate( $titleText, $variant );
		}
	}
}
