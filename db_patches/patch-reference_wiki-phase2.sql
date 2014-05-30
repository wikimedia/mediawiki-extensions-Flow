-- Phase 2 for adding ref_src_wiki field
-- Back-fills the field from other data available in the database.
--
-- These updates are idempotent, but must be re-run until 0 rows are
-- affected for all.

-- Populate header and summary wiki references with the appropriate wiki
UPDATE
	/*_*/flow_wiki_ref, (
		SELECT ref_src_object_id, workflow_wiki
			FROM /*_*/flow_wiki_ref, /*_*/flow_workflow
		WHERE
			flow_wiki_ref.ref_src_object_id = flow_workflow.workflow_id AND
			flow_wiki_ref.ref_src_object_type IN ('header', 'post-summary') AND
			( flow_wiki_ref.ref_src_wiki = '' OR flow_wiki_ref.ref_src_wiki IS NULL )
		LIMIT 1000
	) tmp
	SET flow_wiki_ref.ref_src_wiki = tmp.workflow_wiki
	WHERE flow_wiki_ref.ref_src_object_id = tmp.ref_src_object_id;


-- Populate post wiki references with the appropriate wiki.
UPDATE
	/*_*/flow_wiki_ref, (
		SELECT ref_src_object_id, workflow_wiki
		FROM /*_*/flow_wiki_ref, /*_*/flow_tree_node, /*_*/flow_workflow
		WHERE
			flow_wiki_ref.ref_src_object_id = flow_tree_node.tree_descendant_id AND
			flow_tree_node.tree_ancestor_id = flow_workflow.workflow_id AND
			flow_wiki_ref.ref_src_object_type IN ('post') AND
			( flow_wiki_ref.ref_src_wiki = '' OR flow_wiki_ref.ref_src_wiki IS NULL )
		LIMIT 1000
	) tmp
	SET flow_wiki_ref.ref_src_wiki = tmp.workflow_wiki
	WHERE flow_wiki_ref.ref_src_object_id = tmp.ref_src_object_id;


UPDATE
	/*_*/flow_ext_ref, (
		SELECT ref_src_object_id, workflow_wiki
			FROM /*_*/flow_ext_ref, /*_*/flow_workflow
		WHERE
			flow_ext_ref.ref_src_object_id = flow_workflow.workflow_id AND
			flow_ext_ref.ref_src_object_type IN ('header', 'post-summary') AND
			( flow_ext_ref.ref_src_wiki = '' OR flow_ext_ref.ref_src_wiki IS NULL )
		LIMIT 1000
	) tmp
	SET flow_ext_ref.ref_src_wiki = tmp.workflow_wiki
	WHERE flow_ext_ref.ref_src_object_id = tmp.ref_src_object_id;


-- Populate post wiki references with the appropriate wiki.
UPDATE
	/*_*/flow_ext_ref, (
		SELECT ref_src_object_id, workflow_wiki
		FROM /*_*/flow_ext_ref, /*_*/flow_tree_node, /*_*/flow_workflow
		WHERE
			flow_ext_ref.ref_src_object_id = flow_tree_node.tree_descendant_id AND
			flow_tree_node.tree_ancestor_id = flow_workflow.workflow_id AND
			flow_ext_ref.ref_src_object_type IN ('post') AND
			( flow_ext_ref.ref_src_wiki = '' OR flow_ext_ref.ref_src_wiki IS NULL )
		LIMIT 1000
	) tmp
	SET flow_ext_ref.ref_src_wiki = tmp.workflow_wiki
	WHERE flow_ext_ref.ref_src_object_id = tmp.ref_src_object_id;
