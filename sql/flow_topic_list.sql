-- TopicList Tables
CREATE TABLE /*_*/flow_topic_list (
	topic_list_id binary(11) not null,
	topic_id binary(11) not null,
	PRIMARY KEY (topic_list_id, topic_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/flow_topic_list_topic_id ON /*_*/flow_topic_list (topic_id);
