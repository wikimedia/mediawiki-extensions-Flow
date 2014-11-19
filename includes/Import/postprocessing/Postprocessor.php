<?php

namespace Flow\Import\Postprocessing;

use Flow\Model\UUID;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use MWException;

interface Postprocessor {
	function afterTopicImported( IImportTopic $topic, UUID $newTopicId );
	function afterPostImported( IImportPost $post, UUID $topicId, UUID $newPostId );
}


class ProcessorGroup {
	/** @var array<Postprocessor> **/
	protected $processors;
	/** @var array<string> **/
	protected $methods;

	function __construct( array $methods ) {
		$this->processors = array();
		$this->methods = $methods;
	}

	function add( Postprocessor $proc ) {
		$this->processors[] = $proc;
	}

	function __call( $name, $args ) {
		if ( ! in_array( $name, $this->methods ) ) {
			throw new PostprocessingException( "Unknown postprocessing method $name" );
		}

		foreach( $this->processors as $proc ) {
			call_user_func_array( array( $proc, $name ), $args );
		}
	}
}

class PostprocessingException extends MWException {

}
