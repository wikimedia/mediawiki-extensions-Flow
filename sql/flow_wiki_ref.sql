CREATE TABLE /*_*/flow_wiki_ref (
	ref_id binary(11) not null,
	ref_src_wiki varchar(16) binary not null,
	ref_src_object_id binary(11) not null,
	ref_src_object_type varbinary(32) not null,
	ref_src_workflow_id binary(11) not null,
	ref_src_namespace int not null,
	ref_src_title varbinary(255) not null,
	ref_target_namespace int not null,
	ref_target_title varbinary(255) not null,
	ref_type varbinary(16) not null,

	PRIMARY KEY (ref_id)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/flow_wiki_ref_idx_v2 ON /*_*/flow_wiki_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_type, ref_target_namespace, ref_target_title, ref_src_object_type, ref_src_object_id);

CREATE INDEX /*i*/flow_wiki_ref_revision_v2 ON /*_*/flow_wiki_ref
	(ref_src_wiki, ref_src_namespace, ref_src_title, ref_src_object_type, ref_src_object_id, ref_type, ref_target_namespace, ref_target_title);
