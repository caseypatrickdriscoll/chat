PWDEBUG = 1;

var ajaxURL = '/wp-admin/admin-ajax.php';
var patchchat = { users: [] };

// TODO: Localize
// TODO: Convert to new ajax controller
// TODO: Make better React
// TODO: Drop jquery if possible
// TODO: Add bootstrap(?) tabs


/**
 * - PatchChat
 *   - Header
 *   - Chats
 */

jQuery( document ).ready( function() {

	React.render(
		<PatchChat />,
		document.getElementById( 'wpbody-content' )
	);

} );


var PatchChat = React.createClass( {
	loadCommentsFromServer: function() {

		ajaxdata = {
			'action'  : 'patchchat_get',
			'method'  : 'get_agent',
			'user_id' : 1
		};

		jQuery.ajax({
			method  : 'POST',
			url     : ajaxURL,
			data    : ajaxdata,
			success : function ( response ) {

				this.setState({ data : { chats: response.data } });

				if ( PWDEBUG ) console.log( 'response 2: ', response );

				setTimeout( this.loadCommentsFromServer, 3000 );

			}.bind(this),
			error   : function ( response ) {
				if ( PWDEBUG ) console.log( 'error response: ', response );
			}.bind(this)
		});
	},
	getInitialState: function() {
		return { data: { chats: [] }  }
	},
	componentDidMount: function() {
		this.loadCommentsFromServer();
	},
	render: function() {
		return (
			<div className="patchchat">
				<Header count={this.state.data.chats.length} />
				<Chats data={this.state.data} />
			</div>
		);
	}
} );


var Header = React.createClass( {
	render: function() {
		return (
			<header>
				<h1>PatchChat ({this.props.count})</h1>
			</header>
		);
	}
} );



// TODO: Make gravatar img size variable
var Chats = React.createClass( {
	render: function() {
		var chats = this.props.data.chats.reverse().map( function( chat, i ) {
			return (
				<Chat data={chat} idx={i}  >
					<img src={'https://gravatar.com/avatar/' + chat.img + '.jpg?s=40'} />
					<h3>{chat.name}</h3>
					{chat.title}
				</Chat>
			);
		} );

		var comments = this.props.data.chats.map( function( chat, i ) {
			var chat_id = 'chat_' + chat.id;
			var classes = 'patchchat-body';
			if ( i == 0 ) classes += ' active';
			return (
				<div className={classes} id={chat_id} role="tabpanel" >
					<PatchComments data={chat} />
					<PatchChatForm submit={this.submit} />
				</div>
			);
		} );

		return (
			<section>
				<ul className="chats" role="tablist">
					{chats}
				</ul>
				<div className="tab-content">
					{comments}
				</div>
			</section>
		);
	}
} );

var Chat = React.createClass( {
	click: function (e) {
		e.preventDefault();
		jQuery( e.nativeEvent.target ).tab('show');
	},
	render: function() {
		var chat_id = 'chat_' + this.props.data.id;
		var classes = 'chat';
		if ( this.props.idx == 0 ) classes += ' active';
		return (
			<li className={classes} role="presentation">
				<a href={'#' + chat_id} aria-controls={chat_id} role="tab" data-toggle="tab" onClick={this.click}>
					{this.props.children}
				</a>
			</li>
		);
	}
} );