<?php

namespace Flow\Parsoid\Converter;

use Flow\Exception\NoParsoidException;
use Flow\Exception\WikitextException;
use Flow\Parsoid\ContentConverter;
use Flow\Parsoid\Utils;
use RequestContext;
use Title;
use User;

class ParsoidConverter implements ContentConverter {

	public function __construct( $url, $prefix, $timeout, $forwardCookies ) {
		$this->url = $url;
		$this->prefix = $prefix;
		$this->timeout = $timeout;
		$this->forwardCookies = $forwardCookies;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequiredModules() {
		return array( 'mediawiki.skinning.content.parsoid' );
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
	public function convert( $from, $to, $content, Title $title ) {
		if ( $from == 'html' ) {
			$from = 'html';
		} elseif ( in_array( $from, array( 'wt', 'wikitext' ) ) ) {
			$from = 'wt';
		} else {
			throw new WikitextException( 'Unknown source format: ' . $from, 'process-wikitext' );
		}

		$request = \MWHttpRequest::factory(
			$this->url . '/' . $this->prefix . '/' . $title->getPrefixedDBkey(),
			array(
				'method' => 'POST',
				'postData' => wfArrayToCgi( array( $from => $content ) ),
				'body' => true,
				'timeout' => $this->timeout,
				'connectTimeout' => 'default',
			)
		);

		if ( $this->forwardCookies && !User::isEveryoneAllowed( 'read' ) ) {
			if ( PHP_SAPI === 'cli' ) {
				// From the command line we need to generate a cookie
				$cookies = Utils::generateForwardedCookieForCli();
			} else {
				$cookies = RequestContext::getMain()->getRequest()->getHeader( 'Cookie' );
			}
			$request->setHeader( 'Cookie', $cookies );
		}

		$status = $request->execute();
		if ( !$status->isOK() ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failed contacting parsoid: ' . $status->getMessage()->text() );
			throw new NoParsoidException( 'Failed contacting Parsoid', 'process-wikitext' );
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

			$dom = Utils::createDOM( $response );
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
}
