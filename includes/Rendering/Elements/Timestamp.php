<?php

namespace Flow\Rendering;
use Html;
use MWTimestamp;

class Timestamp extends UIElement {
	public function instantiate( array $params ) {
		extract( $params );

		if ( $timestamp instanceof MWTimestamp ) {
			$this->timestamp = $timestamp;
		} else {
			$this->timestamp = new MWTimestamp( $timestamp );
		}

		$this->historicalLink = $historicalLink;
		$this->tag = $tag;
	}

	public function getValidParameters() {
		return array(
			'timestamp' => array(
				'required' => true,
				'description' => 'The timestamp to render',
			),
			'historicalLink' => array(
				'description' => 'A URL to link the timestamp to, if appropriate',
			),
			'tag' => array(
				'default' => 'p',
				'description' => 'The HTML tag to enclose the timestamp in',
			),
		);
	}

	public function render() {
		$agoTime = Html::element(
			'span',
			array(
				'style' => 'display: inline',
				'class' => 'flow-agotime',
			),
			$this->timestamp->getHumanTimestamp()
		);

		$utcTime = Html::element(
			'span',
			array(
				'style' => 'display: none',
				'class' => 'flow-utctime',
			),
			$this->timestamp->getTimestamp( TS_RFC2822 )
		);

		$html = "$agoTime\n$utcTime";

		if ( ! is_null( $this->historicalLink ) ) {
			$html = Html::rawElement(
				'a',
				array(
					'href' => $this->historicalLink
				),
				$html
			);
		}

		$html = Html::rawElement(
			$this->tag,
			array(
				'class' => 'flow-datestamp',
			),
			$html
		);

		return $html;
	}
}