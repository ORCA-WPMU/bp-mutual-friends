<?php
/*
 * Plugin Name: SuitePlugins - Mutual Friends
 * Plugin URI: 	http://suiteplugins.com
 * Description: Create a new tab on a user's profile with Mutual Friend
 * Author:      SuitePlugins
 * Author URI:  http://suiteplugins.com
 * Version:     1.0.0
 * Text Domain: bp-mutual-friends
 * Domain Path: /languages/
 * License:     GPLv2 or later (license.txt)
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if(!class_exists('BP_Mutual_Friends')):

class BP_Mutual_Friends{
	public function BP_Mutual_Friends(){
		add_action( 'plugins_loaded', array($this, 'plugin_load_textdomain'));
		add_action( 'bp_setup_nav', array($this, 'add_subnav_items'), 10 ); 
		add_action( 'bp_mutual_friends_members', array($this, 'mutual_friends_template'));
		add_action( 'bp_before_member_body', array($this, 'mutual_friends_nav'));
	}
	
	public function plugin_load_textdomain(){
		load_plugin_textdomain( 'bp-mutual-friends', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
	
	public function add_subnav_items(){
		global $bp;
		if ( bp_is_my_profile()){return;} 
		
		// Determine user to use
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			//return;
		}
		if(empty($user_domain)){
			return;
		}
		$friends_link = trailingslashit( $user_domain . bp_get_friends_slug() );
		
		bp_core_new_subnav_item( array( 
			'name' => __('Mutual Friends','bp-mutual-friends'),
			'slug' => 'mutual-friends',
			'parent_url' => $friends_link,
			'parent_slug' => 'friends',
			'screen_function' => array($this, 'mutual_friends_screen'),
			'user_has_access' => true,
			'position' => 112
		) );
	}
	
	public function mutual_friends_screen(){
	   add_action( 'bp_template_title', array($this, 'mutual_friends_screen_title') );
	   add_action( 'bp_template_content', array($this, 'mutual_friends_content') );
	   bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}
	
	public function mutual_friends_nav(){
		if ( !bp_is_my_profile() && bp_is_user_friends() ){
			global $bp;
			$bp = buddypress();


	// If we are looking at a member profile, then the we can use the current
	$component_index = !empty( $bp->displayed_user ) ? bp_current_component() : bp_get_root_slug( bp_current_component() );
	$selected_item   = bp_current_action();

	if ( ! bp_is_single_item() ) {
		if ( !isset( $bp->bp_options_nav[$component_index] ) || count( $bp->bp_options_nav[$component_index] ) < 1 ) {
			return false;
		} else {
			$the_index = $component_index;
		}
	} else {
		$current_item = bp_current_item();

		if ( ! empty( $parent_slug ) ) {
			$current_item  = $parent_slug;
			$selected_item = bp_action_variable( 0 );
		}

		if ( !isset( $bp->bp_options_nav[$current_item] ) || count( $bp->bp_options_nav[$current_item] ) < 1 ) {
			return false;
		} else {
			$the_index = $current_item;
		}
	}

		$subnav_item = (array) $bp->bp_options_nav['friends'][112];
		// If the current action or an action variable matches the nav item id, then add a highlight CSS class.
		if ( $subnav_item['slug'] == $selected_item ) {
			$selected = ' class="current selected"';
		} else {
			$selected = '';
		}

		// List type depends on our current component
		$list_type = bp_is_group() ? 'groups' : 'personal';

		?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
  <ul>
    <?php echo apply_filters( 'bp_get_options_nav_' . $subnav_item['css_id'], '<li id="' . $subnav_item['css_id'] . '-' . $list_type . '-li" ' . $selected . '><a id="' . $subnav_item['css_id'] . '" href="' . $subnav_item['link'] . '">' . $subnav_item['name'] . '</a></li>', $subnav_item, $selected_item ); ?>
  </ul>
</div>
<!-- .item-list-tabs -->
<?php
		}
	}
	public function mutual_friends_screen_title(){
		_e('Mutual Friends', 'bp-mutual-friends');
	}
	
	public function mutual_friends_content(){
		do_action('bp_mutual_friends_members');
	}
	
	public function mutual_friends_template(){
		global $bp;
		$include = $this->get_mutual_firends($bp->loggedin_user->id, $bp->displayed_user->id);
		if($include){
			$include = 'include='.$include;
		}else{
			$include = 'include=false';	
		}
		?>
<?php if ( bp_has_members( bp_ajax_querystring( 'members' ).'&exclude='.$bp->loggedin_user->id.'&'.$include ) ) : ?>
<div id="pag-top" class="pagination">
  <div class="pag-count" id="member-dir-count-top">
    <?php bp_members_pagination_count(); ?>
  </div>
  <div class="pagination-links" id="member-dir-pag-top">
    <?php bp_members_pagination_links(); ?>
  </div>
</div>
<?php

	/**
	 * Fires before the display of the members list.
	 *
	 * @since BuddyPress (1.1.0)
	 */
	do_action( 'bp_before_directory_members_list' ); ?>
<ul id="members-list" class="item-list">
  <?php while ( bp_members() ) : bp_the_member(); ?>
  <li <?php bp_member_class(); ?>>
    <div class="item-avatar"> <a href="<?php bp_member_permalink(); ?>">
      <?php bp_member_avatar(); ?>
      </a> </div>
    <div class="item">
      <div class="item-title"> <a href="<?php bp_member_permalink(); ?>">
        <?php bp_member_name(); ?>
        </a>
        <?php if ( bp_get_member_latest_update() ) : ?>
        <span class="update">
        <?php bp_member_latest_update(); ?>
        </span>
        <?php endif; ?>
      </div>
      <div class="item-meta"><span class="activity">
        <?php bp_member_last_active(); ?>
        </span></div>
      <?php

				/**
				 * Fires inside the display of a directory member item.
				 *
				 * @since BuddyPress (1.1.0)
				 */
				do_action( 'bp_directory_members_item' ); ?>
      <?php
				 /***
				  * If you want to show specific profile fields here you can,
				  * but it'll add an extra query for each member in the loop
				  * (only one regardless of the number of fields you show):
				  *
				  * bp_member_profile_data( 'field=the field name' );
				  */
				?>
    </div>
    <div class="action">
      <?php

				/**
				 * Fires inside the members action HTML markup to display actions.
				 *
				 * @since BuddyPress (1.1.0)
				 */
				do_action( 'bp_directory_members_actions' ); ?>
    </div>
    <div class="clear"></div>
  </li>
  <?php endwhile; ?>
</ul>
<?php

	/**
	 * Fires after the display of the members list.
	 *
	 * @since BuddyPress (1.1.0)
	 */
	do_action( 'bp_after_directory_members_list' ); ?>
<?php bp_member_hidden_fields(); ?>
<div id="pag-bottom" class="pagination">
  <div class="pag-count" id="member-dir-count-bottom">
    <?php bp_members_pagination_count(); ?>
  </div>
  <div class="pagination-links" id="member-dir-pag-bottom">
    <?php bp_members_pagination_links(); ?>
  </div>
</div>
<?php else: ?>
<div id="message" class="info">
  <p>
    <?php _e( "Sorry, you have no mutual friends.", 'bp-mutual-friends' ); ?>
  </p>
</div>
<?php endif; ?>
<?php	
	}
	
public function get_mutual_firends($wp_logged_user_id, $wp_profile_user_id)
{
	global $wpdb, $bp; 
	if($wp_logged_user_id==$wp_profile_user_id) return array(); 
	$qry = sprintf('SELECT DISTINCT u1.id
	FROM '.$wpdb->users.' u1,
	(SELECT f1.initiator_user_id,f1.friend_user_id
	FROM '.$bp->friends->table_name.' f1
	WHERE (f1.initiator_user_id = %1$d
	 OR f1.friend_user_id = %1$d)
	AND f1.is_confirmed = 1) f1
	WHERE (u1.id = f1.initiator_user_id
	OR u1.id = f1.friend_user_id)
	 AND u1.id <> %1$d
	AND EXISTS(SELECT 1
	FROM '.$wpdb->users.' u2,
	 (SELECT f2.initiator_user_id,f2.friend_user_id
	FROM '.$bp->friends->table_name.' f2
	WHERE (f2.initiator_user_id = %2$d
	OR f2.friend_user_id = %2$d)
	AND f2.is_confirmed = 1) f2
	WHERE (u2.id = f2.initiator_user_id
	OR u2.id = f2.friend_user_id)
	AND u2.id <> %2$d
	AND u1.id = u2.id)
	',$wp_profile_user_id,$wp_logged_user_id);
	 $results = $wpdb->get_col($qry);
	 return (!empty($results) ? implode(",", $results) : false);
}
	
}
endif;
function sp_run_mutual_friends(){
	$sp_mutual_friends = new BP_Mutual_Friends();
}
add_action('bp_include', 'sp_run_mutual_friends');

