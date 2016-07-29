<?php

namespace Flow\Model;

use ApiSerializable;

/**
 * Represents an HTML snippet, and associated information needed to render it
 */
class HtmlRenderingInformation implements ApiSerializable {
	/**
	 * Main HTML
	 *
	 * @var string
	 */
	protected $html;

	/**
	 * Array of ResourceLoader module names
	 *
	 * @var array
	 */
	protected $modules;

	/**
	 * Array of ResourceLoader module names to be included as style-only modules.
	 *
	 * @var array
	 */
	protected $moduleStyles;

	/**
	 * Array of head items (see OutputPage::addHeadItems), as an array
	 * of raw HTML strings.
	 *
	 * @var array
	 */
	protected $headItems;

	/**
	 * @param string $html
	 * @param array $modules
	 * @param array $moduleStyles
	 * @param array $headItems
	 */
	public function __construct( $html, array $modules, array $moduleStyles, array $headItems ) {
		$this->html = $html;
		$this->modules = $modules;
		$this->moduleStyles = $moduleStyles;
		$this->headItems = $headItems;
	}

	public function getHtml() {
		return $this->html;
	}

	public function getModules() {
		return $this->modules;
	}

	public function getModuleStyles() {
		return $this->moduleStyles;
	}

	public function getHeadItems() {
		return $this->headItems;
	}

	/**
	 * @return array
	 */
	public function serializeForApiResult() {
		return $this->toArray();
	}

	/**
	 * Constructs the object from an associative array
	 *
	 * @param array $info
	 * @param string $info['html']
	 * @param array (optional) $info['modules']
	 * @param array (optional) $info['modulestyles']
	 * @param array (optional) $info['headitems']
	 */
	public static function fromArray( array $info ) {
		return new HtmlRenderingInformation(
			$info['html'],
			isset( $info['modules'] ) ? $info['modules'] : [],
			isset( $info['modulestyles'] ) ? $info['modulestyles'] : [],
			isset( $info['headitems'] ) ? $info['headitems'] : []
		);
	}

	public function toArray() {
		return [
			'html' => $this->html,
			'modules' => $this->modules,
			'modulestyles' => $this->moduleStyles,
			'headitems' => $this->headItems,
		];
	}
}
