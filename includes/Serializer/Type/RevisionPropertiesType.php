<?php

namespace Flow\Serializer\Type;

use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Serializer\SerializerBuilder;

/**
 *
 */
class RevisionPropertiesType extends AbstractSerializerType {
	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options = array() ) {
		$self = $this;
		$builder->addTransformer( function( $data ) use( $self ) {
				return ( is_array( $data ) && isset( $data['workflowId'], $data['revision'] ) )
					? $self->buildProperties( $data['workflowId'], $data['revision'] )
					: null;
			} );
	}

	/**
	 * @param UUID $workflowId
	 * @param AbstractRevision $revision
	 * @return array
	 */
	public function buildProperties( UUID $workflowId, AbstractRevision $revision ) {
		// @todo copy from RevisionFormatter
	}
}
