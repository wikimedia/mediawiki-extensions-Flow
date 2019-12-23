-- Increase varchar size to 255
ALTER TABLE /*_*/flow_wiki_ref MODIFY ref_src_wiki varchar(255) binary not null;
