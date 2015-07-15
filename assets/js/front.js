jQuery( document ).ready( function() {

	jQuery( 'body' ).append( '<div class="patchchat"></div>' );

	React.render(
		React.createElement("section", null, 
			React.createElement("header", null, "PatchChat"), 
			React.createElement("div", {className: "patchchat-body"}, 
				React.createElement("form", null, 
					React.createElement("label", null, "Name"), React.createElement("input", {name: "patchchat-name", type: "text"}), 
					React.createElement("label", null, "Email"), React.createElement("input", {name: "patchchat-email", type: "email"}), 
					React.createElement("textarea", {name: "patchchat-text"})
				)
			)
		),
		document.getElementsByClassName('patchchat')[0]
	);

	jQuery( '.patchchat header')
		.on( 'click', function() {
			jQuery( '.patchchat-body' ).toggle();
			jQuery( '.patchchat input' )[0].focus();
		} );

	jQuery( '.patchchat form' )
		.delegate( 'textarea', 'keyup', function (e) {

			if ( e.which == 13 || e.keyCode == 13 ) {
//				jQuery( e.target ).val( '' );
			}

			jQuery( this ).height( 0 );
			jQuery( this ).height( this.scrollHeight );

		} ).delegate( 'textarea', 'keydown', function(e) {
			
			if ( e.which == 13 || e.keyCode == 13 ) {
				submitPatchChat( e.target );
			}

		} );

} );



function submitPatchChat() {

	data = {
		'action' : 'submit_patchchat',
		'name'   : jQuery( 'input[name=patchchat-name]' ).val(),
		'email'  : jQuery( 'input[name=patchchat-email]' ).val(),
		'text'   : jQuery( 'textarea[name=patchchat-text]' ).val()
	};

	jQuery.post(
		'/wp-admin/admin-ajax.php',
		data,
		function( response ) {
			
		}
	);

}