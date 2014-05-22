CREATE TABLE /*_*/flow_revision_state (
	frs_rev_id binary(11) not null,
	frs_state varchar(32) binary not null,
	frs_user_id bigint unsigned not null default 0,
	frs_user_ip varbinary(39) default null,
	frs_user_wiki varchar(32) binary not null default '',
	frs_comment varchar(255) binary
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/flow_revision_state_rev_id_state ON /*_*/flow_revision_state (frs_rev_id,frs_state);
