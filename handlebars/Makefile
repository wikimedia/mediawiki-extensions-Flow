#
# @todo it should be possible to run the templates through lightncandy and auto-generate
# this Makefile with the metadata it calculates.
#

COMPILE=php ../maintenance/compileLightncandy.php

TOPIC_SUBTEMPLATES= flow_topic.html.handlebars \
		flow_reply_form.html.handlebars

all: compiled/flow_block_header.html.handlebars.php \
		compiled/flow_block_header_edit.html.handlebars.php \
		compiled/flow_block_header_single_view.html.handlebars.php \
		compiled/flow_block_header_diff_view.html.handlebars.php \
		compiled/flow_board_collapsers_subcomponent.html.handlebars.php \
		compiled/flow_block_topiclist.html.handlebars.php \
		compiled/flow_block_topic.html.handlebars.php \
		compiled/flow_block_topic_history.html.handlebars.php \
		compiled/flow_block_topic_single_view.html.handlebars.php \
		compiled/flow_block_topic_diff_view.html.handlebars.php \
		compiled/flow_block_topic_reply.html.handlebars.php \
		compiled/flow_block_topic_edit_post.html.handlebars.php \
		compiled/flow_block_topic_edit_title.html.handlebars.php \
		compiled/flow_block_topic_moderate_topic.html.handlebars.php \
		compiled/flow_block_topic_moderate_post.html.handlebars.php \
		compiled/flow_block_board-history.html.handlebars.php \
		compiled/flow_block_topicsummary_edit.html.handlebars.php \
		compiled/flow_block_topicsummary_single_view.html.handlebars.php \
		compiled/flow_block_topicsummary_diff_view.html.handlebars.php \
		compiled/flow_board.html.handlebars.php \
		compiled/flow_post.html.handlebars.php \
		compiled/timestamp.html.handlebars.php

compiled/flow_block_header.html.handlebars.php: \
		flow_block_header.html.handlebars
	$(COMPILE) flow_block_header

compiled/flow_block_header_edit.html.handlebars.php: \
		flow_block_header_edit.html.handlebars
	$(COMPILE) flow_block_header_edit

compiled/flow_block_header_single_view.html.handlebars.php: \
		flow_block_header_single_view.html.handlebars
	$(COMPILE) flow_block_header_single_view

compiled/flow_block_header_diff_view.html.handlebars.php: \
		flow_block_header_diff_view.html.handlebars
	$(COMPILE) flow_block_header_diff_view

compiled/flow_block_topicsummary_edit.html.handlebars.php: \
		flow_block_topicsummary_edit.html.handlebars
	$(COMPILE) flow_block_topicsummary_edit

compiled/flow_block_topicsummary_single_view.html.handlebars.php: \
		flow_block_topicsummary_single_view.html.handlebars
	$(COMPILE) flow_block_topicsummary_single_view

compiled/flow_block_topicsummary_diff_view.html.handlebars.php: \
		flow_block_topicsummary_diff_view.html.handlebars
	$(COMPILE) flow_block_topicsummary_diff_view

compiled/flow_board_collapsers_subcomponent.html.handlebars.php: \
		flow_board_collapsers_subcomponent.html.handlebars
	$(COMPILE) flow_board_collapsers_subcomponent

compiled/flow_block_topiclist.html.handlebars.php: \
		flow_board_navigation.html.handlebars \
		flow_topic_navigation.html.handlebars \
		flow_newtopic_form.html.handlebars \
		$(TOPIC_SUBTEMPLATES)
	$(COMPILE) flow_block_topiclist

compiled/flow_block_topic.html.handlebars.php: \
		flow_block_topic.html.handlebars \
		$(TOPIC_SUBTEMPLATES)
	$(COMPILE) flow_block_topic

compiled/flow_block_topic_history.html.handlebars.php: \
		flow_block_topic_history.html.handlebars
	$(COMPILE) flow_block_topic_history

compiled/flow_block_topic_single_view.html.handlebars.php: \
		flow_block_topic_single_view.html.handlebars \
		$(TOPIC_SUBTEMPLATES)
	$(COMPILE) flow_block_topic_single_view

compiled/flow_block_topic_diff_view.html.handlebars.php: \
		flow_block_topic_diff_view.html.handlebars \
		$(TOPIC_SUBTEMPLATES)
	$(COMPILE) flow_block_topic_diff_view

compiled/flow_block_topic_reply.html.handlebars.php: \
		flow_block_topic_reply.html.handlebars \
		flow_reply_form.html.handlebars
	$(COMPILE) flow_block_topic_reply

compiled/flow_block_topic_edit_post.html.handlebars.php: \
		flow_block_topic_edit_post.html.handlebars \
		flow_edit_post.html.handlebars
	$(COMPILE) flow_block_topic_edit_post

compiled/flow_block_topic_edit_title.html.handlebars.php: \
		flow_block_topic_edit_title.html.handlebars \
		flow_edit_post.html.handlebars
	$(COMPILE) flow_block_topic_edit_title

compiled/flow_block_topic_moderate_post.html.handlebars.php: \
		flow_block_topic_moderate_post.html.handlebars \
		flow_errors.html.handlebars \
		flow_moderate_post.html.handlebars \
		flow_post.html.handlebars
	$(COMPILE) flow_block_topic_moderate_post

compiled/flow_block_topic_moderate_topic.html.handlebars.php: \
		flow_block_topic_moderate_topic.html.handlebars \
		flow_errors.html.handlebars \
		flow_moderate_topic.html.handlebars \
		$(TOPIC_SUBTEMPLATES)
	$(COMPILE) flow_block_topic_moderate_topic

compiled/flow_block_board-history.html.handlebars.php: \
		flow_block_board-history.html.handlebars
	$(COMPILE) flow_block_board-history

compiled/flow_board.html.handlebars.php: \
		flow_board.html.handlebars
	$(COMPILE) flow_board

compiled/flow_post.html.handlebars.php: \
		flow_post.html.handlebars
	$(COMPILE) flow_post

compiled/timestamp.html.handlebars.php: \
		timestamp.html.handlebars
	$(COMPILE) timestamp
