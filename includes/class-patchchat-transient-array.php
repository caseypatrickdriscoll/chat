<?php
/**
 * PatchChat Transient Array
 *
 * A Transient Array is an array of patchchat transients
 *
 *
 * Methods:
 * - build (create new transient from fresh WP_Query)
 * - trim  (remove a specific node from current transient)
 * - add   (Add new chat to front of array)
 * - move  (to move between two transients)
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


// TODO: Create 'move' function to move patchchat between two transients
// TODO: If patchchat doesn't exist in a transient you have to build
//       Like adding a chat to 'open' that was previously 'closed'

class PatchChat_Transient_Array {

	/**
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @edited 2015-08-04 14:43:09 - Refactors to use array instead of set
	 * @param $array_name
	 *
	 * @return array|mixed
	 */
	public static function get( $array_name ) {

		$transient = get_transient( 'patchchat_array_' . $array_name );

		if ( $transient === false ) $transient = PatchChat_Transient_Array::build( $array_name );

		return $transient;

	}


	/**
	 * This builds a Transient Array by getting all the transients of user or type.
	 *
	 * There are two types of Transient Arrays
	 *   - 'new'     is an array of all current chats with a 'new' status.
	 *   - 'user_id' is an array of all current chats belonging to a user.
	 *
	 * It needs to know which transients to grab, which it can't do on name only
	 *
	 * We need a record of which transients are 'new' chats, which chats an agent belongs to, etc
	 *
	 * But absolutely, this function does not build single transients from WP_Querys
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @edited 2015-08-04 13:35:48 - Refactors to query only user_id
	 * @edited 2015-08-04 14:44:06 - Adds building of transient if missing
	 *
	 * @param $set_name
	 *
	 * @return array
	 */
	public static function build( $array_name ) {

		// TODO: Should better design to differentiate between
		//       array_name == user_id and array_name == 'new'
		//       Need to generally design to build all sorts of array types
		$transient_array = array();

		$args = array(
			'post_type' => 'patchchat',
			'nopaging'  => true,
			'fields'    => 'ids',
		);

		// A 'new' array is indifferent to the author, just needs a new status
		if ( $array_name == 'new' ) {
			$args['post_status'] = 'new';
		} else {
			// If it's not 'new' it's a user array
			$args['author'] = $array_name;
			$args['post_status'] = array( 'new', 'open' );
		}

		$query = new WP_Query( $args );

		$list = $query->get_posts();

		foreach ( $list as $id ) {
			$transient = get_transient( 'patchchat_' . $id );

			if ( $transient === false ) $transient = PatchChat_Transient::build( $id );

			array_push( $transient_array, $transient );
		}


		set_transient( 'patchchat_array_' . $array_name, $transient_array );


		return $transient_array;
	}


	/**
	 * Moves a chat from one transient to another
	 * Used in the PatchChatAJAX::change_chat_status function
	 *
	 * For example, moves a chat from the 'new' transient to a transient of its own
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-07-24 19:37:58
	 *
	 * @param $id
	 * @param $from
	 * @param $to The status to move it to
	 */
	public static function move( $id, $from, $to ) {

		if ( $from == 'new' && $to == 'open' ) {

			$transient = get_transient( 'patchchat_new' );

			foreach ( $transient as $key => $newchat ) {

				if ( $newchat['id'] == $id ) {

					PatchChat_Transient_Array::trim( 'patchchat_new', $id );
					PatchChat_Transient_Array::add( 'patchchat_' . $id, $newchat );

					break;
				}
			}

		}

	}



	/**
	 * Trims a patchchat from a transient's array
	 *
	 * For example, when a chat is moved from 'new' to 'open', it needs to be trimmed and reassigned
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-07-19 20:16:57
	 *
	 */
	public static function trim( $transient_name, $id ) {

		$transient = get_transient( $transient_name );

		if ( $transient === false ) return false;

		foreach ( $transient as $index => $chat ) {

			if ( $chat['id'] == $id )
				unset( $transient[ $index ] );

		}

		set_transient( $transient_name, $transient );

		return true;
	}


	/**
	 * Adds a patchchat to a transient's array
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-07-19 20:18:54
	 *
	 * $transient_name string The name of the transient to add to ('patchchat_new', etc)
	 *
	 * $patchchat array The array of information to store in transient (see 'Form' in class comments)
	 *
	 */
	public static function add( $transient_name, $patchchat ) {

		$transient = get_transient( $transient_name );

		if ( $transient === false ) $transient = PatchChat_Transient_Array::build( 'new' );

		array_unshift( $transient, $patchchat );

		set_transient( $transient_name, $transient );

		return true;
	}

}