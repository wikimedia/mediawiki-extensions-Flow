
ALTER TABLE /*_*/flow_subscription ADD subscription_user_wiki varchar(32) binary not null;

ALTER TABLE /*_*/flow_tree_revision ADD tree_orig_user_wiki varchar(32) binary not null;

ALTER TABLE /*_*/flow_revision ADD rev_user_wiki varchar(32) binary not null;

ALTER TABLE /*_*/flow_revision ADD rev_mod_user_wiki varchar(32) binary default null;

ALTER TABLE /*_*/flow_revision ADD rev_edit_user_wiki varchar(32) binary default null;

DROP INDEX /*i*/flow_subscription_unique_user_workflow ON /*_*/flow_subscription;
CREATE UNIQUE INDEX /*i*/flow_subscription_unique_user_workflow ON /*_*/flow_subscription (subscription_workflow_id, subscription_user_id, subscription_user_wiki );

DROP INDEX /*i*/flow_subscription_lookup ON /*_*/flow_subscription;
CREATE INDEX /*i*/flow_subscription_lookup ON /*_*/flow_subscription (subscription_user_id, subscription_user_wiki, subscription_last_updated, subscription_workflow_id);
