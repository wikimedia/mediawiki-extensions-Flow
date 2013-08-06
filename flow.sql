-- Database schema for Flow
-- This file contains only the unsharded global data

CREATE TABLE /*_*/flow_definition (
	definition_id decimal(39) unsigned NOT NULL,
	definition_wiki varchar(32) binary NOT NULL,
	definition_name varchar(32) binary NOT NULL,
	definition_type varchar(32) binary NOT NULL,
	definition_options BLOB NULL, -- should instead be revisioned blob		
	PRIMARY KEY (definition_id)
) /*$wgDBTableOptions*/;
CREATE UNIQUE INDEX /*i*/flow_definition_unique_name ON flow_definition (definition_wiki, definition_name);

CREATE TABLE /*_*/flow_workflow (
	workflow_id decimal(39) unsigned not null,
	workflow_wiki varchar(16) binary not null,
	workflow_namespace int not null,
	workflow_page_id int unsigned not null,
	workflow_title_text varchar(255) binary not null,
    workflow_name varchar(255) binary not null,
	workflow_last_update_timestamp binary(14) not null,
	-- TODO: check what the new global user ids need for storage
	workflow_user_id bigint unsigned not null,
	-- TODO: James F said the global ids wont require us to store user_text,
	-- because anon users will get a global id as well.
	workflow_user_text varchar(255) binary not null,
	-- TODO: is this usefull as a bitfield?  may be premature optimization, a string
	-- or list of strings may be simpler and use only a little more space.
	workflow_lock_state int unsigned not null,
	workflow_definition_id decimal(39) unsigned not null,
	PRIMARY KEY (workflow_id)
) /*$wgDBTableOptions*/;

CREATE TABLE /*_*/flow_subscription (
  subscription_workflow_id int unsigned not null,
  subscription_user_id int unsigned not null,
  subscription_create_timestamp varchar(14) binary not null,
  subscription_last_updated varchar(14) binary not null
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_subscription_unique_user_workflow ON /*_*/flow_subscription (subscription_workflow_id, subscription_user_id);
CREATE INDEX /*i*/flow_subscription_lookup ON /*_*/flow_subscription (subscription_user_id, subscription_last_updated, subscription_workflow_id);

-- TopicList Tables
CREATE TABLE /*_*/flow_topic_list (
	topic_list_id decimal(39) unsigned not null,
	topic_id decimal(39) unsigned
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_topic_list_pk ON /*_*/flow_topic_list( topic_list_id, topic_id);

-- Post Content Revisions.  Connects 1 Post to Many revisions.
-- also denormalizes information commonly needed with a revision
CREATE TABLE /*_*/flow_tree_revision (
	-- the id of the post in the post tree
	tree_rev_descendant decimal(39) unsigned not null,
	-- fk to flow_revision
	tree_rev_id decimal(39) unsigned not null,
	-- denormalized so we dont need to keep finding the first revision of a post
	tree_orig_create_time varchar(12) binary not null,
	tree_orig_user_id bigint unsigned not null,
	tree_orig_user_text varchar(255) binary not null,
	-- denormalize post parent as well? Prevents an extra query when building
	-- tree from closure table.  unnecessary?
	tree_parent_id decimal(39) unsigned,
	PRIMARY KEY( tree_rev_id )
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_tree_descendant_revisions 
	ON /*_*/flow_tree_revision ( tree_rev_descendant, tree_rev_id );

-- Summary Content
-- Instead of summary, should this be more generic 'revisioned scratchpad' 
-- or something?  Main limit in current setup can only associate one summary per
-- workflow 
CREATE TABLE /*_*/flow_summary_revision (
	summary_workflow_id decimal(39) unsigned not null,
	summary_rev_id decimal(39) unsigned not null,
	PRIMARY KEY ( summary_workflow_id, summary_rev_id )
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
	rev_id decimal(39) unsigned not null,
	-- What kind of revision is this: tree/summary/etc.
	rev_type varchar(16) binary not null,
	-- user id creating the revision
	rev_user_id int unsigned not null,
	-- name of user creating the revision, or ip address if anon
	-- TODO: global user logins will obviate the need for this, but a round trip
	--       will be needed to map from rev_user_id -> user name
	rev_user_text varchar(255) binary not null default '',
	-- revision suppression
	rev_deleted tinyint unsigned not null default 0,
	-- rev_id of parent or null if no previous revision
	rev_parent_id decimal(39) unsigned,

	-- content of the revision
	rev_text_id int unsigned not null,

	PRIMARY KEY (rev_id)
) /*$wgDBTableOptions*/;

-- Prevents inconsistency, but perhaps will hurt inserts?
CREATE UNIQUE INDEX /*i*/flow_revision_unique_parent ON
	/*_*/flow_revision (rev_parent_id);

CREATE TABLE /*_*/flow_text (
	-- undecided on uuid, or if table is even neccessary
	-- large wiki should just use external store to distribute
	-- content
	text_id int(10) unsigned not null auto_increment, 
	text_content mediumblob not null,
	text_flags tinyblob not null,
	PRIMARY KEY (text_id)
) /*$wgDBTableOptions*/;

-- Closure table implementation of tree storage in sql
-- We may be able to go simpler than this
CREATE TABLE /*_*/flow_tree_node (
	tree_ancestor decimal(39) unsigned not null,
	tree_descendant decimal(39) unsigned not null,
	tree_depth smallint not null
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_tree_node_pk ON /*_*/flow_tree_node (tree_ancestor, tree_descendant);
CREATE UNIQUE INDEX /*i*/flow_tree_constraint ON /*_*/flow_tree_node (tree_descendant, tree_depth);

-- These dont belong here
INSERT INTO flow_definition
	( definition_id, definition_wiki, definition_name, definition_type, definition_options )
	VALUES
	( 6645733872243863389540699858102420002, 'wiki', 'topic', 'topic', NULL ),
	( 6645733872272877609211450958295368226, 'wiki', 'discussion', 'discussion', 'a:2:{s:19:"topic_definition_id";s:37:"6645733872243863389540699858102420002";s:6:"unique";b:1;}' );
