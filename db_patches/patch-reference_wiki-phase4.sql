-- This is the final schema change required for
-- 'Segregate Reference objects by source wiki.'
--
-- After phase 2 is complete (re-running does not affect any more rows),
-- this should be run.

-- Mark field as not null
ALTER TABLE /*_*/flow_wiki_ref MODIFY ref_src_wiki varchar(16) binary not null;
ALTER TABLE /*_*/flow_ext_ref MODIFY ref_src_wiki varchar(16) binary not null;

-- Drop old indexes
DROP INDEX /*i*/flow_wiki_ref_idx ON /*_*/flow_wiki_ref;
DROP INDEX /*i*/flow_wiki_ref_revision ON /*_*/flow_wiki_ref;
DROP INDEX /*i*/flow_wiki_ref_workflow_id_idx_tmp ON /*_*/flow_wiki_ref;

DROP INDEX /*i*/flow_ext_ref_idx ON /*_*/flow_ext_ref;
DROP INDEX /*i*/flow_ext_ref_revision ON /*_*/flow_ext_ref;
DROP INDEX /*i*/flow_ext_ref_workflow_id_idx_tmp ON /*_*/flow_ext_ref;
