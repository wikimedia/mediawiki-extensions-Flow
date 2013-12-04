
UPDATE flow_definition
   SET definition_id = substr( definition_id, 1, 11 );

UPDATE flow_workflow
   SET workflow_id = substr( workflow_id, 1, 11 ),
       workflow_definition_id = substr( workflow_definition_id, 1, 11 );

UPDATE flow_topic_list
   SET topic_list_id = substr( topic_list_id, 1, 11 ),
       topic_id = substr( topic_id, 1, 11 );

UPDATE flow_tree_revision
   SET tree_rev_descendant_id = substr( tree_rev_descendant_id, 1, 11 ),
       tree_rev_id = substr( tree_rev_id, 1, 11 ),
	   tree_parent_id = substr( tree_parent_id, 1, 11 );

UPDATE flow_header_revision
   SET header_workflow_id = substr( header_workflow_id, 1, 11 ),
       header_rev_id = substr( header_rev_id, 1, 11 );

UPDATE flow_revision
   SET rev_id = substr( rev_id, 1, 11 ),
       rev_parent_id = substr( rev_parent_id, 1, 11 ),
	   rev_last_edit_id = substr( rev_last_edit_id, 1, 11 );

UPDATE flow_tree_node
   SET tree_ancestor_id = substr( tree_ancestor_id, 1, 11 ),
       tree_descendant_id = substr( tree_descendant_id, 1, 11 );
