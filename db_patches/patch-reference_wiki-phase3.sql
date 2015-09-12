-- Phase 3 for adding ref_src_wiki field
-- Back-fills the field from other data available in the database.
--
-- These updates are idempotent, but must be re-run until 0 rows are
-- affected for all.

-- Populate wiki references with the appropriate wiki
UPDATE
	/*_*/flow_wiki_ref, (
		SELECT ref_src_workflow_id, workflow_wiki
			FROM /*_*/flow_wiki_ref, /*_*/flow_workflow
		WHERE
			flow_wiki_ref.ref_src_workflow_id = flow_workflow.workflow_id AND
			( flow_wiki_ref.ref_src_wiki = '' OR flow_wiki_ref.ref_src_wiki IS NULL )
		LIMIT 1000
	) tmp
	SET flow_wiki_ref.ref_src_wiki = tmp.workflow_wiki
	WHERE flow_wiki_ref.ref_src_workflow_id = tmp.ref_src_workflow_id;


-- Populate external references with the appropriate wiki.
UPDATE
	/*_*/flow_ext_ref, (
		SELECT ref_src_workflow_id, workflow_wiki
			FROM /*_*/flow_ext_ref, /*_*/flow_workflow
		WHERE
			flow_ext_ref.ref_src_workflow_id = flow_workflow.workflow_id AND
			( flow_ext_ref.ref_src_wiki = '' OR flow_ext_ref.ref_src_wiki IS NULL )
		LIMIT 1000
	) tmp
	SET flow_ext_ref.ref_src_wiki = tmp.workflow_wiki
	WHERE flow_ext_ref.ref_src_workflow_id = tmp.ref_src_workflow_id;
