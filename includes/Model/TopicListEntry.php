<?php

namespace Flow\Model;

use Flow\Exception\DataModelException;

// TODO: We shouldn't need this class
class TopicListEntry {
	protected $topicListId;
	protected $topicId;

	static public function create( Workflow $topicList, Workflow $topic ) {
		// die( var_dump( array(
		// 	'topicList' => $topicList,
		// 	'topic' => $topic,
		// )));
		$obj = new self;
		$obj->topicListId = $topicList->getId();
		$obj->topicId = $topic->getId();
		return $obj;
	}

	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $obj === null ) {
			$obj = new self;
		} elseif ( !$obj instanceof self ) {
			throw new DataModelException( 'Wrong obj type: ' . get_class( $obj ), 'process-data' );
		}
		$obj->topicListId = UUID::create( $row['topic_list_id'] );
		$obj->topicId = UUID::create( $row['topic_id'] );
		return $obj;
	}

	static public function toStorageRow( TopicListEntry $obj ) {
		return array(
			'topic_list_id' => $obj->topicListId->getBinary(),
			'topic_id' => $obj->topicId->getBinary(),
		);
	}

	public function getId() {
		return $this->topicId;
	}

	public function getListId() {
		return $this->topicListId;
	}
}

