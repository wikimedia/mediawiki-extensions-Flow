-- RenameUser will run a couple of queries on WHERE <user_id> = X AND <user_text> = Y
-- to find data that needs to be updated when changing a username on Special:RenameUser

CREATE INDEX /*i*/flow_workflow_user ON /*_*/flow_workflow (workflow_user_id, workflow_user_text);

CREATE INDEX /*i*/flow_rev_user ON /*_*/flow_revision (rev_user_id, rev_user_text);
CREATE INDEX /*i*/flow_rev_nod_user ON /*_*/flow_revision (rev_mod_user_id, rev_mod_user_text);
CREATE INDEX /*i*/flow_rev_edit_user ON /*_*/flow_revision (rev_edit_user_id, rev_edit_user_text);

CREATE INDEX /*i*/flow_tree_orig_user ON /*_*/flow_tree_revision (tree_orig_user_id, tree_orig_user_text);
