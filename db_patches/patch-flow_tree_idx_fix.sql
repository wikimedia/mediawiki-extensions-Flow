
DROP INDEX /*i*/flow_tree_descendant_id_revisions ON /*_*/flow_tree_revision;
CREATE INDEX /*i*/flow_tree_descendant_rev_id ON /*_*/flow_tree_revision ( tree_rev_descendant_id, tree_rev_id );

