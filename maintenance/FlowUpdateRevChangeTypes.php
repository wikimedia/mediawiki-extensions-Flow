
/* We changed the name of the board header area */
update flow_revision SET rev_change_type = 'flow-create-header' WHERE rev_change_type IS NULL;
update flow_revision SET rev_change_type = 'flow-create-header' WHERE rev_change_type = 'flow-create-summary';
update flow_revision SET rev_change_type = 'flow-edit-header' WHERE rev_change_type = 'flow-edit-summary';
update flow_revision SET rev_change_type = 'flow-rev-message-edit-title' WHERE rev_change_type = 'flow-edit-title';
