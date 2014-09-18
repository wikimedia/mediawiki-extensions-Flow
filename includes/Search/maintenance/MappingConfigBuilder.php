<?php

namespace Flow\Search\Maintenance;

class MappingConfigBuilder extends \CirrusSearch\Maintenance\MappingConfigBuilder {
	/**
	 * Build the mapping config.
	 *
	 * The 2 arguments are unused in Flow, but are needed for PHP Strict
	 * standards compliance: declaration should be compatible with parent.
	 *
	 * @param null $prefixSearchStartsWithAnyWord Unused
	 * @param null $phraseSuggestUseText Unused
	 * @return array the mapping config
	 */
	public function buildConfig( $prefixSearchStartsWithAnyWord = null, $phraseSuggestUseText = null ) {
		// @todo: Cirrus had some $titleExtraAnalyzers stuff here, not sure if useful for Flow

		$config = array(
			'dynamic' => false,
//			'_all' => array( 'enabled' => false ), // @todo: see comment below, regarding $wgCirrusSearchAllFields[ 'build' ]
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
				'revisions' => array(
					// object can be flattened (probably doesn't have to be
					// "nested", which would allow them to be querried independently)
					'type' => 'object',
					'properties' => array(
						'id' => $this->buildKeywordField(),
						// @todo: Cirrus' config for 'text' had some more - see if we need those?
						'text' => $this->buildStringField( static::ENABLE_NORMS | static::SPEED_UP_HIGHLIGHTING ),
						'source_text' => $this->buildStringField( static::MINIMAL ),
						'moderation_state' => $this->buildKeywordField(),
						'timestamp' => array(
							'type' => 'date',
							'format' => 'dateOptionalTime',
						),
						'update_timestamp' => array(
							'type' => 'date',
							'format' => 'dateOptionalTime',
						),
						'type' => $this->buildKeywordField(),
					)
				)
			),
		);

		// @todo: Cirrus had $wgCirrusSearchAllFields['build'] stuff here, do we want that in FLow too?

		// same config for both types (well, so far...)
		return array(
			'topic' => $config,
			'header' => $config,
		);
	}
}
