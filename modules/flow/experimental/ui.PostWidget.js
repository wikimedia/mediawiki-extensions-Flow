mw.flowExperimental.ui.PostWidget = function MwFlowPostWidget( id, config ) {
	var $author, $group,
		cssIdentifier = '.mw-flow-identifier-post-' + id;

	config = config || {};

	// Parent constructor
	// (send config.$element to replace this.$element)
	mw.flowExperimental.ui.PostWidget.parent.call( this, config );
	// Mixin constructors
	OO.ui.mixin.GroupElement.call( this );

	this.id = id;
	this.topicID = config.topicID;

	this.menu = new mw.flowExperimental.ui.SimpleMenuWidget( {
		menuItems: [
			{
				data: 'edit',
				label: 'Edit',
				icon: 'edit'
			},
			{
				data: 'permalink',
				label: 'Permalink',
				icon: 'link'
			},
			'separator',
			{
				data: 'hide',
				label: 'Hide'
			},
			{
				data: 'delete',
				label: 'Delete'
			}
		],
		classes: [ 'mw-flow-ui-postWidget-actionMenu' ]
	} );

	// If this widget was created without an established DOM, then we
	// need to create the DOM first and then attach events to it
	if ( !config.$element ) {
		config.user = config.user || {};

		// Build the widget from scratch
		$author = $( '<div>' )
			.addClass( 'mw-flow-ui-postWidget-user' )
			.append(
				new OO.ui.LabelWidget( {
					label: config.user.name || 'username',
					classes: [ 'mw-flow-ui-postWidget-user-name' ]
				} ).$element,
				new OO.ui.LabelWidget( {
					label: config.user.links || '(user) | (links)',
					classes: [ 'mw-flow-ui-postWidget-user-links' ]
				} ).$element
			);

		this.$element
			.addClass( 'mw-flow-ui-postWidget' )
			.append(
				// postHeader
				$( '<div>' )
					.addClass( 'mw-flow-ui-table' )
					.append(
						$( '<div>' )
							.addClass( 'mw-flow-ui-row' )
							.append(
								$( '<div>' )
									.addClass( 'mw-flow-ui-cell' )
									.append( $author ),
								$( '<div>' )
									.addClass( 'mw-flow-ui-cell' )
									.append(
										// Placeholder for the actionMenu
										// which we will replace below
										$( '<div>' )
											.addClass( 'mw-flow-ui-postWidget-actionMenu' )
									)
							)
					),
				// Content
				$( '<div>' )
					.addClass( 'mw-flow-ui-postWidget-content' )
					.append( config.content || 'Testing content' ),
				// Bottom menu
				$( '<div>' )
					.addClass( 'mw-flow-ui-table' )
					.append(
						$( '<div>' )
							.addClass( 'mw-flow-ui-row' )
							.addClass( 'mw-flow-ui-postWidget-bottomMenu-actions' )
							.append(
								$( '<div>' )
									.addClass( 'mw-flow-ui-cell' )
									.append(
										// Placeholders for the bottom menu which we
										// will replace below
										$( '<div>' ).addClass( 'mw-flow-ui-postWidget-actions-reply' ),
										$( '<div>' ).addClass( 'mw-flow-ui-postWidget-actions-thank' )
									),
								$( '<div>' )
									.addClass( 'mw-flow-ui-cell' )
									.append(
										// These should not change unless
										// an edit was made to the actual post
										new OO.ui.LabelWidget( {
											classes: [ 'mw-flow-ui-postWidget-bottomMenu-timestamp-ago' ],
											label: 'XXX ago'
										} ),
										new OO.ui.LabelWidget( {
											classes: [ 'mw-flow-ui-postWidget-bottomMenu-timestamp-full' ],
											label: 'XX of December, 2017'
										} )
									)
							)
					),
				// Replies
				$( '<div>' )
					.addClass( 'mw-flow-ui-postWidget-replies' )
			);
	}

	this.topicTitle = config.$element ?
		this.$element.closest( '.mw-flow-ui-topicWidget-header-title-content' ).text() :
		'';
	this.$actions = this.$element.find( cssIdentifier + '.mw-flow-ui-postWidget-bottomMenu-actions' );

	// Take over elements
	this.setGroupElement( this.$element.find( '.mw-flow-ui-postWidget-replies' ) );
	this.replyButton = new OO.ui.ButtonWidget( {
		framed: false,
		label: 'Reply',
		classes: [ 'mw-flow-ui-postWidget-actions-reply' ]
	} );
	this.thankButton = new OO.ui.ButtonWidget( {
		framed: false,
		label: 'Thank',
		classes: [ 'mw-flow-ui-postWidget-actions-thank' ]
	} );
	this.$element.find( cssIdentifier + ' > .mw-flow-ui-postWidget-actions-reply' )
		.replaceWith( this.replyButton.$element );
	this.$element.find( cssIdentifier + ' > .mw-flow-ui-postWidget-actions-thank' )
		.replaceWith( this.thankButton.$element );

	// Events
	this.replyButton.connect( this, { click: [ 'handleAction', 'reply' ] } );
	this.thankButton.connect( this, { click: [ 'handleAction', 'thank' ] } );

	this.$element
		.addClass( 'mw-flow-ui-postWidget' )
		.addClass( 'mw-flow-ui-postWidget-enhanced' );
};

/* Initialization */

OO.inheritClass( mw.flowExperimental.ui.PostWidget, OO.ui.Widget );
OO.mixinClass( mw.flowExperimental.ui.PostWidget, OO.ui.mixin.GroupElement );

mw.flowExperimental.ui.PostWidget.prototype.triggerAction = function ( action ) {
	console.log( 'action: ' + action, this.id );

	switch ( action ) {
		case 'reply':
			this.reply();
			break;
		case 'thank':
			this.thank();
			break
	}
};
mw.flowExperimental.ui.PostWidget.prototype.reply = function () {
	if ( !this.replyWidget ) {
		this.replyWidget = new mw.flowExperimental.ui.ReplyWidget(
			this.id,
			{
				postTitle: this.topicTitle
			}
		);

		this.replyWidget.connect( this, {
			submit: 'onSubmitReply',
			cancel: 'removeReplyWidget'
		} );

		this.$actions.after( this.replyWidget.$element );
		this.replyButton.setDisabled( true );
	}
};

mw.flowExperimental.ui.PostWidget.prototype.thank = function () {
	console.log( 'thank', this.id );
};

mw.flowExperimental.ui.PostWidget.prototype.onSubmitReply = function () {
	var replyText = this.replyWidget.getContent();

	if ( !replyText ) {
		return;
	}

	// TODO: This should be submit to the API in the controller.
	// For the experiment, I'm going to pretend it was, and display
	// the new post with the content given
	/* new mw.Api().postWithToken( 'csrf', {
		action: 'flow',
		submodule: 'reply',
		page: 'Topic:' + this.topicID,
		repreplyTo: this.id,
		repcontent: replyText,
		repformat: 'html'
	} )
		.then( function ( result ) {
			console.log( result );
		} )
		.then( function () {
			this.replyWidget.destroy();
			this.replyButton.setDisabled( false );
		} );
		*/
	this.addItem(
		new mw.flowExperimental.ui.PostWidget( 'fakeID', {
			user: {
				name: 'fooser'
			},
			content: replyText
		} )
	);

	this.removeReplyWidget();
};

mw.flowExperimental.ui.PostWidget.prototype.removeReplyWidget = function () {
	this.replyWidget.destroy();
	this.replyButton.setDisabled( false );
};
