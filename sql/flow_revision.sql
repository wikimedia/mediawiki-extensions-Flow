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
	-- The id of the object this is a revision of
	-- For example, if rev_type is header, rev_type_id is the header's id.
	-- If rev_type is post, it is the post's id, etc.
	rev_type_id binary(11) not null default '',
	-- user id creating the revision
	rev_user_id bigint unsigned not null,
	rev_user_ip varbinary(39) default null,
	rev_user_wiki varchar(64) binary not null,
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
	rev_mod_user_wiki varchar(64) binary default null,
	rev_mod_timestamp varchar(14) binary,
	-- moderated why? (coming soon: how?, where? and what?)
	rev_mod_reason varchar(255) binary,

	-- track who made the most recent content edit
	rev_last_edit_id binary(11) null,
	rev_edit_user_id bigint unsigned,
	rev_edit_user_ip varbinary(39) default null,
	rev_edit_user_wiki varchar(64) binary default null,

	rev_content_length int not null default 0,
	rev_previous_content_length int not null default 0,

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
