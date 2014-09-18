<?php

namespace Flow\Search;

class MappingConfigBuilder extends \CirrusSearch\Maintenance\MappingConfigBuilder {
	/**
	 * @param bool $optimizeForExperimentalHighlighter should the index be optimized for the experimental highlighter?
	 */
	public function __construct( $optimizeForExperimentalHighlighter ) {
		$this->optimizeForExperimentalHighlighter = $optimizeForExperimentalHighlighter;
	}

	/**
	 * Build the mapping config.
	 * @return array the mapping config
	 */
	public function buildConfig() {
		// @todo: Cirrus had some $titleExtraAnalyzers stuff here, not sure if useful for Flow

		$config = array(
			'dynamic' => false,
			'_all' => array( 'enabled' => false ),
			'properties' => array(
				'namespace' => $this->buildLongField(),
				'namespace_text' => $this->buildKeywordField(),
				'pageid' => $this->buildLongField(),
				// no need to analyze title, we won't be searching it
				'title' => $this->buildKeywordField(),
				'timestamp' => array(
					'type' => 'date',
					'format' => 'dateOptionalTime',
				),

				'revisions.id' => $this->buildKeywordField(),
				// @todo: Cirrus' config for 'text' had some more - see if we need those?
				'revisions.text' => $this->buildStringField( MappingConfigBuilder::ENABLE_NORMS | MappingConfigBuilder::SPEED_UP_HIGHLIGHTING ),
				'revisions.source_text' => $this->buildStringField( MappingConfigBuilder::MINIMAL ),
				'revisions.moderation_state' => $this->buildKeywordField(),
				'revisions.timestamp' => array(
					'type' => 'date',
					'format' => 'dateOptionalTime',
				),
				'revisions.type' => $this->buildKeywordField(),
			),
		);

		// @todo: Cirrus had some stuff here WRT weights, not sure if useful for Flow

		return $config;
	}
}
