ALTER TABLE /*_*/flow_ext_ref DROP KEY flow_ext_ref_idx_v2;
ALTER TABLE /*_*/flow_ext_ref CHANGE ref_target ref_target BLOB NOT NULL;
CREATE INDEX /*i*/flow_ext_ref_idx_v2 ON /*_*/flow_ext_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_type, ref_target(255), ref_src_object_type, ref_src_object_id);
