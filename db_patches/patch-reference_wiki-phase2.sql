-- Phase 2 for adding ref_src_wiki field
-- Back-fills the field from other data available in the database
-- and marks the fields as NOT NULL

-- Populate header references with the appropriate wiki
UPDATE /*_*/flow_wiki_ref, /*_*/flow_workflow
	SET flow_wiki_ref.ref_src_wiki = flow_workflow.workflow_wiki
	WHERE
		flow_wiki_ref.ref_src_object_id = flow_workflow.workflow_id AND
		flow_wiki_ref.ref_src_object_type IN ('header');


-- Populate post references with the appropriate wiki.
UPDATE /*_*/flow_wiki_ref, /*_*/flow_tree_node, /*_*/flow_workflow
	SET flow_wiki_ref.ref_src_wiki = flow_workflow.workflow_wiki
	WHERE
		flow_wiki_ref.ref_src_object_id = flow_tree_node.tree_descendant_id AND
		flow_tree_node.tree_ancestor_id = flow_workflow.workflow_id AND
		flow_wiki_ref.ref_src_object_type IN ('post');


-- Populate header references
UPDATE /*_*/flow_ext_ref, /*_*/flow_workflow
	SET flow_ext_ref.ref_src_wiki = flow_workflow.workflow_wiki
	WHERE
		flow_ext_ref.ref_src_object_id = flow_workflow.workflow_id AND
		flow_ext_ref.ref_src_object_type IN ('header');


-- Populate post references
UPDATE /*_*/flow_ext_ref, /*_*/flow_tree_node, /*_*/flow_workflow
	SET flow_ext_ref.ref_src_wiki = flow_workflow.workflow_wiki
	WHERE
		flow_ext_ref.ref_src_object_id = flow_tree_node.tree_descendant_id AND
		flow_tree_node.tree_ancestor_id = flow_workflow.workflow_id AND
		flow_ext_ref.ref_src_object_type IN ('post');


-- Mark field as not null
ALTER TABLE /*_*/flow_wiki_ref MODIFY ref_src_wiki varchar(16) binary not null;
ALTER TABLE /*_*/flow_ext_ref MODIFY ref_src_wiki varchar(16) binary not null;

-- Drop old indexes
DROP INDEX /*i*/flow_wiki_ref_pk ON /*_*/flow_wiki_ref;
DROP INDEX /*i*/flow_wiki_ref_revision ON /*_*/flow_wiki_ref;
DROP INDEX /*i*/flow_ext_ref_pk ON /*_*/flow_ext_ref;
DROP INDEX /*i*/flow_ext_ref_revision ON /*_*/flow_ext_ref;