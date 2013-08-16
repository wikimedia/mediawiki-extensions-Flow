<?php

// @todo: I think we can remove this file, a replacement lives in modules/editor/ext.flow.parsoid.js - conversion should no longer happen; anything coming in and going out should be HTML; editors have to convert in JS (if necessary - VE doesn't even have to)


namespace Flow;

abstract class ParsoidUtils {
	public static function convertWikitextToHtml5( $wikitext, $title ) {
		global $wgFlowUseParsoid;

		if ( $wgFlowUseParsoid ) {
			global $wgFlowParsoidURL, $wgFlowParsoidPrefix, $wgFlowParsoidTimeout;

			$parsoidOutput = \Http::post(
				$wgFlowParsoidURL . '/_wikitext/' . $title->getPrefixedUrl(),
				array(
					'postData' => array(
						'content' => $wikitext,
						'format' => 'html',
					),
					'timeout' => $wgFlowParsoidTimeout
				)
			);

			// Strip out the Parsoid boilerplate
			$dom = new \DOMDocument();
			$dom->loadHTML( $parsoidOutput );
			$body = $dom->getElementsByTagName( 'body' )->item(0);
			$html = '';

			foreach( $body->childNodes as $child ) {
				$html .= $child->ownerDocument->saveXML( $child );
			}

			return $html;
		} else {
			global $wgParser;

			$title = \Title::newFromText( 'Flow', NS_SPECIAL );

			$options = new \ParserOptions;
			$options->setTidy( true );
			$options->setEditSection( false );

			$output = $wgParser->parse( $wikitext, $title, $options );
			return $output->getText();
		}
	}

	public static function convertHtml5ToWikitext( $html ) {
		global $wgFlowUseParsoid;

		if ( $wgFlowUseParsoid ) {
			global $wgFlowParsoidURL, $wgFlowParsoidPrefix, $wgFlowParsoidTimeout;

			return \Http::post(
				$wgFlowParsoidURL . '/' . $wgFlowParsoidPrefix . '/Flow',
				array(
					'postData' => array(
						'content' => $html,
					),
					'timeout' => $wgFlowParsoidTimeout
				)
			);
		} else {
			throw new \MWException( "Editing posts is not supported without Parsoid" );
		}
	}
}
