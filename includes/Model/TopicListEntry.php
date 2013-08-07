<?php

namespace Flow\Model;

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

		static public function fromStorageRow( array $row ) {
				$obj = new self;
				$obj->topicListId = new UUID( $row['topic_list_id'] );
				$obj->topicId = new UUID( $row['topic_id'] );
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
				return $this-topicListId;
		}
}

