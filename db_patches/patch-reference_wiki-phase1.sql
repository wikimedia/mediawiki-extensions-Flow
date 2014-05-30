-- Phase 1 for adding ref_src_wiki field
-- Adds the new fields, nullable, and updates indexes

-- Add field to wiki_ref table
ALTER TABLE /*_*/flow_wiki_ref ADD COLUMN ref_src_wiki varchar(16) binary null;

-- Create new indexes
CREATE UNIQUE INDEX /*i*/flow_wiki_ref_pk_v2 ON /*_*/flow_wiki_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_type, ref_target_namespace, ref_target_title, ref_src_object_type, ref_src_object_id);
CREATE UNIQUE INDEX /*i*/flow_wiki_ref_revision_v2 ON /*_*/flow_wiki_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_src_object_type, ref_src_object_id, ref_type, ref_target_namespace, ref_target_title);


-- Add field to ext_ref table
ALTER TABLE /*_*/flow_ext_ref ADD COLUMN ref_src_wiki varchar(16) binary not null;

-- Create new indexes
CREATE UNIQUE INDEX /*i*/flow_ext_ref_pk_v2 ON /*_*/flow_ext_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_type, ref_target, ref_src_object_type, ref_src_object_id);

CREATE UNIQUE INDEX /*i*/flow_ext_ref_revision_v2 ON /*_*/flow_ext_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_src_object_type, ref_src_object_id, ref_type, ref_target);
