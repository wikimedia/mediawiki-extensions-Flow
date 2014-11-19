<?php

namespace Flow\Import\Postprocessing;

use Flow\Model\UUID;
use Flow\Import\IImportPost;
use Flow\Import\IImportSource;
use Flow\Import\IImportTopic;
use MWException;

interface Postprocessor {
	function afterTopicImported( IImportTopic $topic, UUID $newTopicId );
	function afterPostImported( IImportPost $post, UUID $topicId, UUID $newPostId );
	function afterTalkpageImported();
}


class ProcessorGroup implements Postprocessor {
	/** @var array<Postprocessor> **/
	protected $processors;

	public function __construct( ) {
		$this->processors = array();
	}

	public function add( Postprocessor $proc ) {
		$this->processors[] = $proc;
	}

	public function afterTopicImported( IImportTopic $topic, UUID $newTopicId ) {
		$this->call( 'afterTopicImported', func_get_args() );
	}

	public function afterPostImported( IImportPost $post, UUID $topicId, UUID $postId ) {
		$this->call( 'afterPostImported', func_get_args() );
	}

	public function afterTalkpageImported() {
		$this->call( 'afterTalkpageImported', func_get_args() );
	}

	protected function call( $name, $args ) {
		foreach( $this->processors as $proc ) {
			call_user_func_array( array( $proc, $name ), $args );
		}
	}
}

class PostprocessingException extends MWException {

}
