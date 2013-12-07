
ALTER TABLE /*_*/flow_workflow ADD workflow_user_ip varchar(255) binary default null;
UPDATE /*_*/flow_workflow SET workflow_user_ip = null WHERE workflow_user_id != 0;

ALTER TABLE /*_*/flow_tree_revision ADD tree_orig_user_ip varchar(255) binary default null;
UPDATE /*_*/flow_tree_revision SET tree_orig_user_ip = null WHERE tree_orig_user_id != 0;

ALTER TABLE /*_*/flow_revision
	ADD rev_user_ip varchar(255) binary default null,
	ADD rev_mod_user_ip varchar(255) binary default null,
	ADD rev_edit_user_ip varchar(255) binary default null;

UPDATE /*_*/flow_revision SET rev_user_ip = null WHERE rev_user_id != 0;
UPDATE /*_*/flow_revision SET rev_mod_user_ip = null WHERE rev_mod_user_id != 0;
UPDATE /*_*/flow_revision SET rev_edit_user_ip = null WHERE rev_edit_user_id != 0;
