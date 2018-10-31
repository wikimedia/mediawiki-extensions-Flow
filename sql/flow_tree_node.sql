-- Closure table implementation of tree storage in sql
-- We may be able to go simpler than this
CREATE TABLE /*_*/flow_tree_node (
	tree_ancestor_id binary(11) not null,
	tree_descendant_id binary(11) not null,
	tree_depth smallint not null,
	PRIMARY KEY (tree_ancestor_id, tree_descendant_id)
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_tree_constraint ON /*_*/flow_tree_node (tree_descendant_id, tree_depth);
