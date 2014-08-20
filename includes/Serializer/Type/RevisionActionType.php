<?php

namespace Flow\Serializer\Type;

use Flow\Formatter\FormatterRow;
use Flow\Serializer\SerializerBuilder;

/**
 *
 */
class RevisionActionsType extends AbstractSerializerType {
	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		$self = $this;
		$builder->addTransformer( function( $data ) use ( $self ) {
			return $data instanceof FormatterRow
				? $self->buildActions( $data )
				: null;
		} );
	}

	/**
	 * @param FormatterRow $row
	 * @return Anchor[]
	 */
	public function buildActions( FormatterRow $row ) {
		// @todo copy from RevisionFormatter
	}
}
