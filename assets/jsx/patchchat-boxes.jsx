var PatchChatBoxes = React.createClass( {
	render: function() {

		var patchchat_boxes = this.props.chats.map( function( chat, i ) {

			var chat_id = 'chat_' + chat.chat_id;
			var classes = 'patchchatbox ' + chat.status;
			if ( i == 0 ) classes += ' active';

			return (
				<PatchChatBox
					id={chat_id}
					key={chat_id}
					chat={chat}
					role="tabpanel"
					submit={this.props.submit}
					classes={classes}
					/>
			);
		}, this );

		var initNeeded = this.props.chats.length == 0 ? true : false;

		return(
			<ul id="patchchatboxes">
				<PatchChatInitBox submit={this.props.submit} needed={initNeeded} />
				{patchchat_boxes}
			</ul>
		)
	}
} );


var PatchChatBox = React.createClass( {

	render: function() {

		var patchchat_comments = typeof this.props.chat.chat_id === 'undefined' ? null : <PatchChatComments chat={this.props.chat} />;

		return(
			<li className={this.props.classes} id={this.props.id}>
				<PatchChatBoxHeader />
				{patchchat_comments}
				<PatchChatForm submit={this.props.submit} chat_id={this.props.chat.chat_id} />
			</li>
		);
	}
} );


var PatchChatBoxHeader = React.createClass( {

	handleClick : function(e) {
		if ( patchchat.admin == 'true' ) return;
		jQuery( e.nativeEvent.target ).closest( '.patchchatbox' ).toggleClass( 'active' );
	},

	minimizeIcon : function() {
		if ( 'minimizeIcon' in patchchat ) {
			return <i className={'minimize fa ' + patchchat.minimizeIcon}></i>;
		}
	},

	spinnerIcon : function() {
		if ( 'spinnerIcon' in patchchat ) {
			return <i className={'patchchat-spinner fa ' + patchchat.spinnerIcon + ' fa-spin'}></i>;
		}
	},

	render : function () {
		return (
			<header onClick={this.handleClick}>
				{patchchat.headerText}
				{this.minimizeIcon()}
				{this.spinnerIcon()}
			</header>
		);
	}
} );


var PatchChatComments = React.createClass( {

	shouldScrollBottom: true,

	componentWillUpdate: function() {
		var node = this.getDOMNode();
		this.shouldScrollBottom = node.scrollTop + node.offsetHeight === node.scrollHeight;
	},

	componentDidUpdate: function() {
		if (this.shouldScrollBottom) {
			var node = this.getDOMNode();
			node.scrollTop = node.scrollHeight
		}

	},

	componentDidMount: function() {
		this.componentDidUpdate();
	},

	icon: function( comment ) {
		if ( patchchat.welcomeIcon && comment.type == 'auto' ) {
			var classes = "fa " + patchchat.welcomeIcon;
			return <i className={classes}></i>;
		}
	},

	render: function() {

		var comments = this.props.chat.comments.map( function( comment ) {
			var classes = 'patchchatcomment';
			var user    = this.props.chat.users[comment.user].name;

			if ( comment.type == 'auto' )
				classes += ' auto';
			else
				classes += ' ' + this.props.chat.users[comment.user].role;

			return (
				<li className={classes} key={'comment' + comment.id} title={user}>
					<img src={'https://gravatar.com/avatar/' + comment.img + '.jpg?s=30'} />
					<span>
						{comment.text}
						{this.icon(comment)}
					</span>
				</li>
			);
		}, this );

		return (
			<ul className="patchchatcomments">
				{comments}
			</ul>
		);
	}
} );


/**
 * This is a normal form, with just the text box for creating commments.
 *
 * Previously, it also had the name and email fields.
 *
 * Name and email are moved to PatchChatInitBox - caseypatrickdriscoll 2015-08-02 18:53:59
 */
var PatchChatForm = React.createClass( {

	validate: function(e) {

		if ( e.which == 13 || e.keyCode == 13 ) {
			e.preventDefault();

			var chat = {};

			// Initialize the method to update.
			// If the initBox is tripped, this will be changed to 'create'
			chat.method  = 'update';

			chat.chat_id = this.props.chat_id;
			chat.text    = e.target.value;

			var valid = false;
			var error = false;

			if ( chat.text == '' )
				error = 'Text is blank';

			if ( error == false ) {
				valid = true;
			}

			if ( patchchat.debug == 'true' ) console.log( 'PatchChatForm', 'text: ' + chat.text, 'error: ' + error );

			if ( valid ) {
				e.target.value = '';
				this.props.submit(chat);
			}

		}

	},
	adjust: function(e) {
		jQuery( e.target ).height( 0 );
		jQuery( e.target ).height( e.target.scrollHeight );
	},
	render: function() {
		return(
			<form>
				{this.props.children}
				<textarea name="patchchat-text" onKeyUp={this.adjust} onKeyDown={this.validate} required></textarea>
			</form>
		);
	}
});
