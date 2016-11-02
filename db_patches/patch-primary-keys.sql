DROP INDEX /*i*/flow_subscription_unique_user_workflow ON /*_*/flow_subscription;
ALTER TABLE /*_*/flow_subscription_unique_user_workflow ADD PRIMARY KEY (subscription_workflow_id, subscription_user_id, subscription_user_wiki);

DROP INDEX /*i*/flow_topic_list_pk ON /*_*/flow_topic_list;
ALTER TABLE /*_*/flow_topic_list ADD PRIMARY KEY (topic_list_id, topic_id);

DROP INDEX /*i*/flow_tree_node_pk ON /*_*/flow_tree_node;
ALTER TABLE /*_*/flow_tree_node ADD PRIMARY KEY (tree_ancestor_id, tree_descendant_id);
