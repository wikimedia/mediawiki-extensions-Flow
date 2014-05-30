-- Adds a ref_src_wiki field to reference tables


-- Add field to wiki_ref table
ALTER TABLE /*_*/flow_wiki_ref ADD COLUMN ref_src_wiki varchar(16) binary not null;

-- Drop indexes for adjustment
DROP INDEX /*i*/flow_wiki_ref_pk ON /*_*/flow_wiki_ref;
DROP INDEX /*i*/flow_wiki_ref_revision ON /*_*/flow_wiki_ref;

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

-- Recreate new indexes
CREATE UNIQUE INDEX /*i*/flow_wiki_ref_pk ON /*_*/flow_wiki_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_type, ref_target_namespace, ref_target_title, ref_src_object_type, ref_src_object_id);
CREATE UNIQUE INDEX /*i*/flow_wiki_ref_revision ON /*_*/flow_wiki_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_src_object_type, ref_src_object_id, ref_type, ref_target_namespace, ref_target_title);


-- Add field to ext_ref table
ALTER TABLE /*_*/flow_ext_ref ADD COLUMN ref_src_wiki varchar(16) binary not null;

-- Drop indexes
DROP INDEX /*i*/flow_ext_ref_pk ON /*_*/flow_ext_ref;
DROP INDEX /*i*/flow_ext_ref_revision ON /*_*/flow_ext_ref;

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

-- Recreate new indexes
CREATE UNIQUE INDEX /*i*/flow_ext_ref_pk ON /*_*/flow_ext_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_type, ref_target, ref_src_object_type, ref_src_object_id);

CREATE UNIQUE INDEX /*i*/flow_ext_ref_revision ON /*_*/flow_ext_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_src_object_type, ref_src_object_id, ref_type, ref_target);
