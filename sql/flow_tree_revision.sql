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
	tree_orig_user_wiki varchar(64) binary not null,
	-- denormalize post parent as well? Prevents an extra query when building
	-- tree from closure table.  unnecessary?
	tree_parent_id binary(11),
	PRIMARY KEY(tree_rev_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/flow_tree_descendant_rev_id ON /*_*/flow_tree_revision (tree_rev_descendant_id, tree_rev_id);
