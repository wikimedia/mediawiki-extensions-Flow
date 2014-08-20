<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;
use Flow\Data\UserNameBatch;
use Flow\Model\UserTuple;

/**
 *
 */
class UserNameLookupType extends AbstractSerializerType {
	/**
	 * @var Closure
	 */
	protected $callback;

	public function __construct( UserNameBatch $usernames ) {
		$this->callback = function( $data ) use ( $usernames ){
			return $data instanceof UserTuple
				? $usernames->get( $data->wiki, $data->id, $data->ip )
				: null;
		};
	}

	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		$builder->addTransformer( $this->callback );
	}
}
