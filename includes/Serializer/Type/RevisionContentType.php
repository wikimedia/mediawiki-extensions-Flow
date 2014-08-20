<?php

namespace Flow\Serializer\Type;

use Flow\Serializer\SerializerBuilder;
use Flow\Model\PostRevision;
use Flow\Templating;

/**
 *
 */
class RevisionContentType extends AbstractSerializerType {
	/**
	 * @var Templating
	 */
	protected $templating;

	/**
	 * @param Templating $templating
	 */
	public function __construct( Templating $templating ) {
		$this->templating = $templating;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultOptions() {
		return array(
			'content_format' => 'html',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildSerializer( SerializerBuilder $builder, array $options ) {
		$templating = $this->templating;
		$builder->addTransformer( function( $data ) use ( $options, $templating ) {
			$contentFormat = ( $data instanceof PostRevision && $data->isTopicTitle() )
				? 'plaintext'
				: $options['content_format'];

			return array(
				'content' => $templating->getContent( $data, $contentFormat ),
				'contentFormat' => $contentFormat,
			);
		} );
	}
}
