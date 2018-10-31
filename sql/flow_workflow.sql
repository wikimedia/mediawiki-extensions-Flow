-- This file contains only the unsharded global data

CREATE TABLE /*_*/flow_workflow (
	workflow_id binary(11) not null,
	workflow_wiki varchar(64) binary not null,
	workflow_namespace int not null,
	workflow_page_id int unsigned not null,
	workflow_title_text varchar(255) binary not null,
	workflow_name varchar(255) binary not null,
	workflow_last_update_timestamp binary(14) not null,
	-- TODO: is this useful as a bitfield?  may be premature optimization, a string
	-- or list of strings may be simpler and use only a little more space.
	workflow_lock_state int unsigned not null,
	workflow_type varbinary(16) not null,
	PRIMARY KEY (workflow_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/flow_workflow_lookup ON /*_*/flow_workflow (workflow_wiki, workflow_namespace, workflow_title_text);
CREATE INDEX /*i*/flow_workflow_update_timestamp ON /*_*/flow_workflow (workflow_last_update_timestamp);
