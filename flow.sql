-- Database schema for Flow
-- This file contains only the unsharded global data

CREATE TABLE /*_*/flow_workflow (
	workflow_id binary(11) not null,
	workflow_wiki varchar(16) binary not null,
	workflow_namespace int not null,
	workflow_page_id int unsigned not null,
	workflow_title_text varchar(255) binary not null,
    workflow_name varchar(255) binary not null,
	workflow_last_update_timestamp binary(14) not null,
	-- TODO: check what the new global user ids need for storage
	workflow_user_id bigint unsigned not null,
	workflow_user_ip varbinary(39) default null,
	workflow_user_wiki varchar(32) binary not null,
	-- TODO: is this usefull as a bitfield?  may be premature optimization, a string
	-- or list of strings may be simpler and use only a little more space.
	workflow_lock_state int unsigned not null,
	workflow_type varbinary(16) not null,
	PRIMARY KEY (workflow_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/flow_workflow_lookup ON /*_*/flow_workflow (workflow_wiki, workflow_namespace, workflow_title_text);

CREATE TABLE /*_*/flow_subscription (
  subscription_workflow_id int unsigned not null,
  subscription_user_id bigint unsigned not null,
  subscription_user_wiki varchar(32) binary not null,
  subscription_create_timestamp varchar(14) binary not null,
  subscription_last_updated varchar(14) binary not null
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_subscription_unique_user_workflow ON /*_*/flow_subscription (subscription_workflow_id, subscription_user_id, subscription_user_wiki );
CREATE INDEX /*i*/flow_subscription_lookup ON /*_*/flow_subscription (subscription_user_id, subscription_user_wiki, subscription_last_updated, subscription_workflow_id);

-- TopicList Tables
CREATE TABLE /*_*/flow_topic_list (
	topic_list_id binary(11) not null,
	topic_id binary(11)
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_topic_list_pk ON /*_*/flow_topic_list( topic_list_id, topic_id);
CREATE INDEX /*i*/flow_topic_list_topic_id ON /*_*/flow_topic_list (topic_id);

-- Post Content Revisions.  Connects 1 Post to Many revisions.
-- also denormalizes information commonly needed with a revision
CREATE TABLE /*_*/flow_tree_revision (
	-- the id of the post in the post tree
	tree_rev_descendant_id binary(11) not null,
	-- fk to flow_revision
	tree_rev_id binary(11) not null,
	-- denormalized so we don't need to keep finding the first revision of a post
	tree_orig_user_id bigint unsigned not null,
	tree_orig_user_ip varbinary(39) default null,
	tree_orig_user_wiki varchar(32) binary not null,
	-- denormalize post parent as well? Prevents an extra query when building
	-- tree from closure table.  unnecessary?
	tree_parent_id binary(11),
	PRIMARY KEY( tree_rev_id )
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/flow_tree_descendant_rev_id
	ON /*_*/flow_tree_revision ( tree_rev_descendant_id, tree_rev_id );

-- Header Content
-- Instead of header, should this be more generic 'revisioned scratchpad'
-- or something?  Main limit in current setup can only associate one header per
-- workflow
CREATE TABLE /*_*/flow_header_revision (
	header_workflow_id binary(11) not null,
	header_rev_id binary(11) not null,
	PRIMARY KEY ( header_workflow_id, header_rev_id )
) /*$wgDBTableOptions*/;

-- Content
-- This is completely unoptimized right now, just a quick get-it-done for
-- the prototype
--
-- NOTE: This doesn't directly link to whatever the revision is for. The rev_type field should
-- be unique enough to know what to look in though.  For example when rev_type === 'tree' then
-- look in flow_tree_revision.  Typical use case should not be to use this field, but to join
-- from an id in the other direction.
--
-- Each revision has a timestamped id, and explicitly states who its parent is.
-- Comparing to the ids in the matching flow_tree_revision table should allow for
-- detecting edit conflicts, so they can be resolved? Idealy they are resolved before
-- this point, but as a backup plan?
--
CREATE TABLE /*_*/flow_revision (
	-- UID::newTimestampedUID128()
	rev_id binary(11) not null,
	-- What kind of revision is this: tree/header/etc.
	rev_type varchar(16) binary not null,
	-- The id of the revision_type, post_id/workflow_id/etc
	rev_type_id binary(11) not null default '',
	-- user id creating the revision
	rev_user_id bigint unsigned not null,
	rev_user_ip varbinary(39) default null,
	rev_user_wiki varchar(32) binary not null,
	-- rev_id of parent or null if no previous revision
	rev_parent_id binary(11) null,
	-- comma separated set of ascii flags.
	rev_flags tinyblob not null,
	-- content of the revision
	rev_content mediumblob not null,
	-- the type of change that was made. MW message key.
	-- formerly rev_comment
	rev_change_type varbinary(255) null,
	-- current moderation state
	rev_mod_state varchar(32) binary not null,
	-- moderated by who?
	rev_mod_user_id bigint unsigned,
	rev_mod_user_ip varbinary(39) default null,
	rev_mod_user_wiki varchar(32) binary default null,
	rev_mod_timestamp varchar(14) binary,
	-- moderated why? (coming soon: how?, where? and what?)
	rev_mod_reason varchar(255) binary,

	-- track who made the most recent content edit
	rev_last_edit_id binary(11) null,
	rev_edit_user_id bigint unsigned,
	rev_edit_user_ip varbinary(39) default null,
	rev_edit_user_wiki varchar(32) binary default null,

	PRIMARY KEY (rev_id)
) /*$wgDBTableOptions*/;

-- Prevents inconsistency, but perhaps will hurt inserts?
CREATE UNIQUE INDEX /*i*/flow_revision_unique_parent ON
	/*_*/flow_revision (rev_parent_id);
-- Primary key is automatically appended to all secondary index in InnoDB
CREATE INDEX /*i*/flow_revision_type_id ON /*_*/flow_revision (rev_type, rev_type_id);

-- Special:Contributions can do queries based on user id/ip
CREATE INDEX /*i*/flow_revision_user ON
	/*_*/flow_revision (rev_user_id, rev_user_ip, rev_user_wiki);

-- Closure table implementation of tree storage in sql
-- We may be able to go simpler than this
CREATE TABLE /*_*/flow_tree_node (
	tree_ancestor_id binary(11) not null,
	tree_descendant_id binary(11) not null,
	tree_depth smallint not null
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_tree_node_pk ON /*_*/flow_tree_node (tree_ancestor_id, tree_descendant_id);
CREATE UNIQUE INDEX /*i*/flow_tree_constraint ON /*_*/flow_tree_node (tree_descendant_id, tree_depth);
