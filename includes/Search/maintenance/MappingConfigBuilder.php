<?php

namespace Flow\Search\Maintenance;

class MappingConfigBuilder extends \CirrusSearch\Maintenance\MappingConfigBuilder {
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
				'update_timestamp' => array(
					'type' => 'date',
					'format' => 'dateOptionalTime',
				),

				'revisions.id' => $this->buildKeywordField(),
				// @todo: Cirrus' config for 'text' had some more - see if we need those?
				'revisions.text' => $this->buildStringField( static::ENABLE_NORMS | static::SPEED_UP_HIGHLIGHTING ),
				'revisions.source_text' => $this->buildStringField( static::MINIMAL ),
				'revisions.moderation_state' => $this->buildKeywordField(),
				'revisions.timestamp' => array(
					'type' => 'date',
					'format' => 'dateOptionalTime',
				),
				'revisions.update_timestamp' => array(
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
