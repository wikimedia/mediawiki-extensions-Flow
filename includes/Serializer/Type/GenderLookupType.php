<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;
use GenderCache;

/**
 *
 */
class GenderLookupType extends AbstractSerializerType {
	/**
	 * @var Closure
	 */
	protected $callback;

	/**
	 * @param GenderCache $genderCache
	 */
	public function __construct( GenderCache $genderCache ) {
		$wikiId = wfWikiId();
		$this->callback = function( $data ) use ( $genderCache, $wikiId ) {
			return ( isset( $data['wiki'], $data['name'] ) && $data['wiki'] === $wikiId )
				? $genderCache->getGenderOf( $data['name'], __METHOD__ )
				: 'unknown';
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		$builder->addTransformer( $this->callback );
	}
}
