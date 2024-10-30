<?php


use Pheal\Pheal;
use Pheal\Core\Config;
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'MOOAuthServerPointersManager.php';

class Mo_Rocketchat_Oauth_Server_Widget extends WP_Widget {
	
	public function __construct() {
		update_option( 'host_name', 'https://login.xecurify.com' );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		
		add_action( 'init', array( $this, 'mo_oauth_start_session' ) );
		add_action( 'wp_logout', array( $this, 'mo_oauth_end_session' ) );
		parent::__construct( 'Mo_Rocketchat_Oauth_Server_Widget', 'miniOrange OAuth', array( 'description' => __( 'Login to Apps with OAuth', 'flw' ), ) );
	 }
	 
	function mo_oauth_start_session() {
		if( ! session_id() ) {
			session_start(['read_and_close' => true,]);
		}
		
		if(isset($_REQUEST['option']) and $_REQUEST['option'] == 'testattrmappingconfig'){
			$mo_oauth_app_name = $_REQUEST['app'];
			wp_redirect(site_url().'?option=oauthredirect&app_name='. urlencode($mo_oauth_app_name)."&test=true");
			exit();
		} 
		
	}

	function mo_oauth_end_session() {
		if( ! session_id() ) 
		{ 	session_start(['read_and_close' => true,]);
        }
		session_destroy();
	}
	 
	public function widget( $args, $instance ) {
		return;
		extract( $args );
		
		echo $args['before_widget'];
		if ( ! empty( $wid_title ) ) {
			echo $args['before_title'] . $wid_title . $args['after_title'];
		}
		// $this->mo_oauth_login_form();
		echo $args['after_widget'];
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
		return $instance;
	}
	
	public function mo_oauth_login_form() {
		global $post;
		$this->error_message();
		$appsConfigured = get_option('mo_oauth_google_enable') | get_option('mo_oauth_eveonline_enable') | get_option('mo_oauth_facebook_enable');
		
		$appslist = get_option('mo_oauth_apps_list');
		if($appslist && sizeof($appslist)>0)
			$appsConfigured = true;
		
		if( ! is_user_logged_in() ) {
			
			if( $appsConfigured ) {
				
				$this->mo_oauth_load_login_script();
				
				$style = get_option('mo_oauth_icon_width') ? "width:".get_option('mo_oauth_icon_width').";" : "";
				$style .= get_option('mo_oauth_icon_height') ? "height:".get_option('mo_oauth_icon_height').";" : "";
				$style .= get_option('mo_oauth_icon_margin') ? "margin:".get_option('mo_oauth_icon_margin').";" : "";
				
				if( get_option('mo_oauth_google_enable') ) {
				?>
				
				<a href="javascript:void(0)" onClick="moOAuthLogin('google');"><img src="<?php echo plugins_url( 'images/icons/google.jpg', __FILE__ )?>"></a>
					
				<?php
				}
				if( get_option('mo_oauth_eveonline_enable') ) { ?>
					<a href="javascript:void(0)" onClick="moOAuthLogin('eveonline');"><img style="<?php echo $style;?>" src="<?php echo plugins_url( 'images/icons/eveonline.png', __FILE__ )?>"></a>
				<?php }
				if( get_option('mo_oauth_facebook_enable') ) { ?>
					<a href="javascript:void(0)" onClick="moOAuthLogin('facebook');"><img src="<?php echo plugins_url( 'images/icons/facebook.png', __FILE__ )?>"></a> <?php
				}
				if (is_array($appslist)) {
					foreach($appslist as $key=>$app){
						if($key=="eveonline")
							continue;
							$imageurl = "";
						if($key=='facebook')
							$imageurl = plugins_url( 'images/fblogin.png', __FILE__ );
						else if($key=='google')
							$imageurl = plugins_url( 'images/googlelogin.png', __FILE__ );
						else if($key=='windows')
							$imageurl = plugins_url( 'images/windowslogin.png', __FILE__ );
					
						if(!empty($imageurl)){
						?><div><a href="javascript:void(0)" onClick="moOAuthLoginNew('<?php echo $key;?>');"><img style="<?php echo $style;?>" src="<?php echo $imageurl; ?>"></a></div><?php
						} else { ?><a href="javascript:void(0)" onClick="moOAuthLoginNew('<?php echo $key;?>');" style="color:#fff"><div style="background: #7272dc;height:40px;padding:8px;text-align:center;<?php echo $style;?>">Login with <?php echo ucwords($key);?></div></a><?php
						}
				
					}
				}
				
			} else {
				?>
				<div>No apps configured.</div>
				<?php
			}
			?>
			
			<?php 
		} else {
			$current_user = wp_get_current_user();
			$link_with_username = __('Howdy, ', 'flw') . $current_user->display_name;
			?>
			<div id="logged_in_user" class="login_wid">
				<li><?php echo $link_with_username;?> | <a href="<?php echo wp_logout_url( site_url() ); ?>" title="<?php _e('Logout','flw');?>"><?php _e('Logout','flw');?></a></li>
			</div>
			<?php
		}
	}
	
	private function mo_oauth_load_login_script() {
	?>
	<script type="text/javascript">
		function moOAuthLogin(app_name) {
			window.location.href = '<?php echo site_url() ?>' + '/?option=generateDynmicUrl&app_name=' + app_name;
		}
		function moOAuthLoginNew(app_name) {
			window.location.href = '<?php echo site_url() ?>' + '/?option=oauthredirect&app_name=' + app_name;
		}
	</script>
	<?php
	}
	
	
	
	public function error_message() {
		if( isset( $_SESSION['msg'] ) and $_SESSION['msg'] ) {
			echo '<div class="' . $_SESSION['msg_class'] . '">' . $_SESSION['msg'] . '</div>';
			unset( $_SESSION['msg'] );
			unset( $_SESSION['msg_class'] );
		}
	}
	
	public function register_plugin_styles() {
		wp_enqueue_style( 'style_login_widget', plugins_url( 'style_login_widget.css', __FILE__ ) );
	}
	
	
}
	function mo_oauth_server_login_validate(){
		
		
	}
	
	//here entity is corporation, alliance or character name. The administrator compares these when user logs in
	function mo_oauth_server_check_validity_of_entity($entityValue, $entitySessionValue, $entityName) {
		
		$entityString = $entityValue ? $entityValue : false;
		$valid_entity = false;
		if( $entityString ) {			//checks if entityString is defined
			if ( strpos( $entityString, ',' ) !== false ) {			//checks if there are more than 1 entity defined
				$entity_list = array_map( 'trim', explode( ",", $entityString ) );
				foreach( $entity_list as $entity ) {			//checks for each entity to exist
					if( $entity == $entitySessionValue ) {
						$valid_entity = true;
						break;
					}
				}
			} else {		//only one entity is defined
				if( $entityString == $entitySessionValue ) {
					$valid_entity = true;
				}
			}
		} else {			//entity is not defined
			$valid_entity = false;
		}
		return $valid_entity;
	}

	function mo_oauth_server_testattrmappingconfig($nestedprefix, $resourceOwnerDetails){
		foreach($resourceOwnerDetails as $key => $resource){
			if(is_array($resource) || is_object($resource)){
				if(!empty($nestedprefix))
					$nestedprefix .= ".";
				mo_oauth_server_testattrmappingconfig($nestedprefix.$key,$resource);
			} else {
				echo "<tr><td>";
				if(!empty($nestedprefix))
					echo $nestedprefix.".";
				echo $key."</td><td>".$resource."</td></tr>";
			}
		}
	}
	
	function mo_oauth_server_getnestedattribute($resource, $key){
		if(empty($key))
			return "";
		
		$keys = explode(".",$key);
		if(sizeof($keys)>1){
			$current_key = $keys[0];
			if(isset($resource[$current_key]))
				return mo_oauth_server_getnestedattribute($resource[$current_key], str_replace($current_key.".","",$key));
		} else {
			$current_key = $keys[0];
			if(isset($resource[$current_key]))
				return $resource[$current_key];
		}
	}
	
	function mo_oauth_server_register_mo_oauth_widget() {
		register_widget('Mo_Rocketchat_Oauth_Server_Widget');
	}
	
	add_action( 'init', 'mo_oauth_server_login_validate' );
?>
