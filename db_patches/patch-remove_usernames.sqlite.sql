-- Rename current table to temporary name
ALTER TABLE /*_*/flow_workflow RENAME TO /*_*/temp_flow_workflow_remove_usernames;
ALTER TABLE /*_*/flow_tree_revision RENAME TO /*_*/temp_flow_tree_revision_remove_usernames;
ALTER TABLE /*_*/flow_revision RENAME TO /*_*/temp_flow_revision_remove_usernames;

-- RECREATE tables with user_id renamed to user_ip
CREATE TABLE /*_*/flow_workflow (
    workflow_id binary(16) not null,
    workflow_wiki varchar(16) binary not null,
    workflow_namespace int not null,
    workflow_page_id int unsigned not null,
    workflow_title_text varchar(255) binary not null,
    workflow_name varchar(255) binary not null,
    workflow_last_update_timestamp binary(14) not null,
    workflow_user_id bigint unsigned not null,
    workflow_user_ip varchar(40) binary default null,
    workflow_lock_state int unsigned not null,
    workflow_definition_id binary(16) not null,
    PRIMARY KEY (workflow_id)
) /*$wgDBTableOptions*/;

CREATE TABLE /*_*/flow_tree_revision (
    tree_rev_descendant_id binary(16) not null,
    tree_rev_id binary(16) not null,
    tree_orig_create_time varchar(12) binary not null,
    tree_orig_user_id bigint unsigned not null,
    tree_orig_user_ip varchar(40) binary default null,
    tree_parent_id binary(16),
    PRIMARY KEY( tree_rev_id )
) /*$wgDBTableOptions*/;

CREATE TABLE /*_*/flow_revision (
    rev_id binary(16) not null,
    rev_type varchar(16) binary not null,
    rev_user_id bigint unsigned not null,
    rev_user_ip varchar(40) binary default null,
    rev_parent_id binary(16),
    rev_flags tinyblob not null,
    rev_content mediumblob not null,
    rev_change_type varbinary(255) null,

    rev_mod_state varchar(32) binary not null,
    rev_mod_user_id bigint unsigned,
    rev_mod_user_ip varchar(40) binary default null,
    rev_mod_timestamp varchar(14) binary,
    rev_mod_reason varchar(255) binary,

    rev_last_edit_id binary(16) null,
    rev_edit_user_id bigint unsigned,
    rev_edit_user_ip varchar(40) default null,

    PRIMARY KEY (rev_id)
) /*$wgDBTableOptions*/;

-- Copy over all the old data into the new tables
INSERT INTO /*_*/flow_workflow
	( workflow_id, workflow_wiki, workflow_namespace, workflow_page_id, workflow_title_text, workflow_name, workflow_last_update_timestamp, workflow_user_id, workflow_lock_state, workflow_definition_id, workflow_user_ip )
SELECT
	workflow_id, workflow_wiki, workflow_namespace, workflow_page_id, workflow_title_text, workflow_name, workflow_last_update_timestamp, workflow_user_id, workflow_lock_state, workflow_definition_id,
	CASE ( workflow_user_id )
		WHEN 0 THEN workflow_user_text
		ELSE NULL
	END
FROM /*_*/temp_flow_workflow_remove_usernames;

INSERT INTO /*_*/flow_tree_revision
	( tree_rev_descendant_id, tree_rev_id, tree_orig_create_time, tree_orig_user_id, tree_parent_id, tree_orig_user_ip )
SELECT
	tree_rev_descendant_id, tree_rev_id, tree_orig_create_time, tree_orig_user_id, tree_parent_id,
	CASE ( tree_orig_user_id )
		WHEN 0 THEN tree_orig_user_text
		ELSE NULL
	END
FROM /*_*/temp_flow_tree_revision_remove_usernames;

INSERT INTO /*_*/flow_revision
	( rev_id, rev_type, rev_user_id, rev_parent_id, rev_flags, rev_content, rev_change_type, rev_mod_state, rev_mod_user_id, rev_mod_timestamp, rev_mod_reason, rev_last_edit_id, rev_edit_user_id, rev_user_ip, rev_mod_user_ip, rev_edit_user_ip )
SELECT
	rev_id, rev_type, rev_user_id, rev_parent_id, rev_flags, rev_content, rev_change_type, rev_mod_state, rev_mod_user_id, rev_mod_timestamp, rev_mod_reason, rev_last_edit_id, rev_edit_user_id,
	CASE ( rev_user_id )
		WHEN 0 THEN rev_user_text
		ELSE NULL
	END,
	CASE ( rev_mod_user_id )
		WHEN 0 THEN rev_mod_user_text
		ELSE NULL
	END,
	CASE ( rev_edit_user_id )
		WHEN 0 THEN rev_edit_user_text
		ELSE NULL
	END
FROM /*_*/temp_flow_revision_remove_usernames;

-- Drop the original tables
DROP TABLE /*_*/temp_flow_workflow_remove_usernames;
DROP TABLE /*_*/temp_flow_tree_revision_remove_usernames;
DROP TABLE /*_*/temp_flow_revision_remove_usernames;

-- recreate indexes

CREATE INDEX /*i*/flow_workflow_lookup ON /*_*/flow_workflow (workflow_wiki, workflow_namespace, workflow_title_text, workflow_definition_id);
CREATE UNIQUE INDEX /*i*/flow_tree_descendant_id_revisions ON /*_*/flow_tree_revision ( tree_rev_descendant_id, tree_rev_id );
CREATE UNIQUE INDEX /*i*/flow_revision_unique_parent ON /*_*/flow_revision (rev_parent_id);
