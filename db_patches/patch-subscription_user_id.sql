-- Patch to add infomation about the last content edit to flow revisions
ALTER TABLE /*_*/flow_subscription CHANGE subscription_user_id subscription_user_id bigint unsigned not null;

