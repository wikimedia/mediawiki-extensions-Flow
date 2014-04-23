
COMPILE=php ../maintenance/compileLightncandy.php

all: flow_block_header.html.handlebars.php \
		flow_block_topiclist.html.handlebars.php \
		flow_block_board-history.html.handlebars.php \
		flow_board.html.handlebars.php \
		flow_post.html.handlebars.php \
		timestamp.html.handlebars.php

flow_block_header.html.handlebars.php: flow_block_header.html.handlebars
	$(COMPILE) flow_block_header

flow_block_topiclist.html.handlebars.php: flow_board_navigation.html.handlebars \
		flow_board_collapsers_subcomponent.html.handlebars \
		flow_topic_navigation.html.handlebars \
		flow_newtopic_form.html.handlebars \
		flow_topic.html.handlebars \
		flow_reply_form.html.handlebars
	$(COMPILE) flow_block_topiclist

flow_block_board-history.html.handlebars.php: flow_block_board-history.html.handlebars
	$(COMPILE) flow_block_board-history

flow_board.html.handlebars.php: flow_board.html.handlebars
	$(COMPILE) flow_board

flow_post.html.handlebars.php: flow_post.html.handlebars
	$(COMPILE) flow_post

timestamp.html.handlebars.php: timestamp.html.handlebars
	$(COMPILE) timestamp
