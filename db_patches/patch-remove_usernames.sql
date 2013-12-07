
ALTER TABLE flow_workflow CHANGE workflow_user_text workflow_user_ip varchar(255) binary default null;
UPDATE flow_workflow SET workflow_user_ip = null WHERE workflow_user_id != 0;

ALTER TABLE flow_tree_revision CHANGE tree_orig_user_text tree_orig_user_ip varchar(255) binary default null;
UPDATE flow_tree_revision SET tree_orig_user_ip = null WHERE tree_orig_user_id != 0;

ALTER TABLE flow_revision
	CHANGE rev_user_text rev_user_ip varchar(255) binary default null,
	CHANGE rev_mod_user_text rev_mod_user_ip varchar(255) binary default null,
	CHANGE rev_edit_user_text rev_edit_user_ip varchar(255) binary default null;

UPDATE flow_revision SET rev_user_ip = null WHERE rev_user_id != 0;
UPDATE flow_revision SET rev_mod_user_ip = null WHERE rev_mod_user_id != 0;
UPDATE flow_revision SET rev_edit_user_ip = null WHERE rev_edit_user_id != 0;
