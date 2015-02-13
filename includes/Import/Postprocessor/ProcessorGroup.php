<?php

namespace Flow\Import\Postprocessor;

use Flow\Model\UUID;
use Flow\Import\IImportHeader;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\TopicImportState;
use Flow\Import\PageImportState;

class ProcessorGroup implements Postprocessor {
	/** @var array<Postprocessor> **/
	protected $processors;

	public function __construct( ) {
		$this->processors = array();
	}

	public function add( Postprocessor $proc ) {
		$this->processors[] = $proc;
	}

	public function afterHeaderImported( PageImportState $state, IImportHeader $header ) {
		$this->call( __FUNCTION__, func_get_args() );
	}
	public function afterTopicImported( TopicImportState $state, IImportTopic $topic ) {
		$this->call( __FUNCTION__, func_get_args() );
	}

	public function afterPostImported( TopicImportState $state, IImportPost $post, UUID $newPostId ) {
		$this->call( __FUNCTION__, func_get_args() );
	}

	public function importAborted() {
		$this->call( __FUNCTION__, func_get_args() );
	}

	protected function call( $name, $args ) {
		foreach( $this->processors as $proc ) {
			call_user_func_array( array( $proc, $name ), $args );
		}
	}
}
