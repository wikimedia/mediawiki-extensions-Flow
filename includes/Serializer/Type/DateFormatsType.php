<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;
use Language;
use User;

/**
 *
 */
class DateFormatsType extends AbstractSerializerType {
	/**
	 * @var Closure
	 */
	protected $callback;

	/**
	 * @param User $user
	 * @param Language $lang
	 */
	public function __construct( User $user, Language $lang ) {
		$this->callback = function( $data ) use ( $user, $lang ) {
			return $data === null ? null : array(
				'timeAndDate' => $lang->userTimeAndDate( $data, $user ),
				'date' => $lang->userDate( $data, $user ),
				'time' => $lang->userTime( $data, $user ),
			);
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParentType( array $options ) {
		return 'uuid';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultOptions() {
		return array( 'timestamp' => TS_MW );
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		$builder->addTransformer( $this->callback );
	}
}
