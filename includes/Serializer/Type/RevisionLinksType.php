<?php

namespace Flow\Serializer\Type;

use Flow\Formatter\FormatterRow;
use Flow\Serializer\SerializerBuilder;

/**
 *
 */
class RevisionLinksType extends AbstractSerializerType {
	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options = array() ) {
		$self = $this;
		$builder->addTransformer( function( $data ) use ( $self ) {
			return $data instanceof FormatterRow
				? $self->buildLinks( $data )
				: null;
		} );
	}

	/**
	 * @param FormatterRow $row
	 * @return Anchor[]
	 */
	public function buildLinks( FormatterRow $row ) {
		// @todo copy from RevisionFormatter
	}
}
