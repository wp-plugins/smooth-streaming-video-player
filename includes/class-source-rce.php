<?php
/**
 * IIS Smooth Streaming videos source management.
 * 
 * @author Adenova <agence@adenova.fr>
 * @since 1.5.0
 */
if ( ! class_exists ( 'SVP_Source_RCE' ) )
{
	require_once( 'class-source-base.php' );
	
	// It's a source for RCE videos : include the adaptive video type class
	require_once( 'class-video-rce.php' );
	
	class SVP_Source_RCE extends SVP_Source_Base
	{
		
		function SVP_Source_RCE()
		{
			$this->__construct();
		}
		
		function __construct()
		{
			$svp_video_rce = new SVP_Video_RCE();
			$this->set_videos_type( $svp_video_rce->get_type() );
		}
		
		function configure()
		{
			$name = '';
			$options = array();
			$options['svp_source_url'] = '';
			$options['svp_source_dirname'] = '';
			$options['svp_source_host'] = '';
			$options['svp_source_user'] = '';
			$options['svp_source_pass'] = '';
			$id = $this->get_ID();
			if ( ! empty( $id ) )
			{
				$name = $this->get_name();
				$options = $this->get_options();
			}
			return '
				<div class="stuffbox">
					<h3>' . __( 'Main configuration' , 'svp-translate' ) . '</h3>
					<div class="inside">
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_name"><abbr class="required">' . __( 'Source name', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_name" type="text" id="svp_source_name" value="' . $name . '" class="regular-text code" />
									<br />' . __( 'Here, you have to place your source type name.', 'svp-translate' ) . '
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_url"><abbr class="required">' . __( 'Videos server web URL', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_url" type="text" id="svp_source_url" value="' . $options['svp_source_url'] . '" class="regular-text code" />
									<br />' . __( 'Example&nbsp;: <code>http://wordpress.org/</code> &#8212; don&#8217;t forget the <code>http://</code> prefix.', 'svp-translate' ) . '
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_dirname">' . __( 'Videos directory name', 'svp-translate' ) . '</label>
								</th>
								<td>
									<input name="svp_source_dirname" type="text" id="svp_source_dirname" value="' . $options['svp_source_dirname'] . '" class="regular-text code" />
									<br />' . __( 'This is the directory name that contains your videos on the server.', 'svp-translate' ) . '
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="stuffbox">
					<h3>' . __( 'Videos access configuration' , 'svp-translate' ) . '</h3>
					<div class="inside">
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_host"><abbr class="required">' . __( 'Videos FTP host', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_host" type="text" id="svp_source_host" value="' . $options['svp_source_host'] . '" class="regular-text code" />
									<br />' . __( "The FTP host can be an IP or domain name &#8212; don&#8217;t add <code>ftp://</code> prefix.", 'svp-translate' ). '
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_user"><abbr class="required">' . __( 'Videos FTP user', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_user" type="text" id="svp_source_user" value="' . $options['svp_source_user'] . '" class="regular-text code" />
									<br />' . __( 'This is the FTP user to connect to FTP server.', 'svp-translate' ) . '
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_pass"><abbr class="required">' . __( 'Videos FTP password', 'svp-translate' ) . '</abbr></label>
								</th>
								<td>
									<input name="svp_source_pass" type="password" id="svp_source_pass" value="' . $options['svp_source_pass'] . '" class="regular-text code" />
									<br />' . __( 'This is the FTP password to connect to FTP server.', 'svp-translate' ) . '
								</td>
							</tr>
						</table>
					</div>
				</div>';
		}
		
		function scan( $id = null )
		{
			if ( empty( $id ) )
			{
				wp_die( __( 'Parameter ID is undefined.', 'svp-translate' ) );
				exit();
			}
			
			global $wpdb;
			
			$sql = 'SELECT options FROM ' . $wpdb->prefix . 'svp_sources WHERE ID = %d';
			$source = $wpdb->get_row( $wpdb->prepare( $sql, (int) $id ) );
			
			$options = null;
			$result = array();
			if ( ! empty( $source ) )
			{
				if ( ! empty( $source->options ) )
					$options = unserialize( $source->options );
				if ( ! empty( $options ) )
				{
					$cid = ftp_connect( $options['svp_source_host'] );
					$login = ftp_login( $cid, $options['svp_source_user'], $options['svp_source_pass'] );
					if ( ! $cid || ! $login )
					{
						wp_die( __( 'Error connecting to source. Check your source videos access settings.', 'svp-translate' ) );
						exit();
					}
					else
					{
						( isset( $options['svp_source_dirname'] ) && ! empty( $options['svp_source_dirname'] ) ) ? $directory = $options['svp_source_dirname'] : $directory = '.';
						$result = ftp_nlist( $cid, $directory );
						if ( $result === false )
						{
							wp_die( __( 'Error listing your videos source. Try later or check your videos directory name setting.', 'svp-translate' ) );
							exit();
						}
					}
					ftp_close( $cid );
				}
			}
			
			// Create instance of RCE video class
			$svp_video_rce = new SVP_Video_RCE();
			
			// Filter by extension
			$videos = new SVP_Videos();
			$result = $videos->filter_by_extensions( $result, $svp_video_rce->get_extensions() );
			
			// Do the synchronization
			$synchro = $this->synchro( $id, $result );
			
			// Returns an array of videos
			return $synchro;
		}
		
		function check( $data = array() )
		{
			// Instanciation de la classe des utilitaires
			$utils = new SVP_Utils();
			
			// Réalise les tests
			if ( empty( $data['svp_source_name'] ) )
				return false;
			foreach ( $data as $key => $value )
			{
				if ( in_array( $key, $this->get_input_list() ) )
				{
					switch ( $key )
					{
						case 'svp_source_url':
							if ( ! $utils->is_url( $data[$key] ) )
								return false;
							break;
						case 'svp_source_dirname':
							break;
						case 'svp_source_host':
							if ( ! $utils->is_domain( $data[$key] ) && ! $utils->is_ip( $data[$key] ) )
								return false;
							break;
						case 'svp_source_user':
							if ( empty( $data[$key] ) )
								return false;
							break;
						case 'svp_source_pass':
							if ( empty( $data[$key] ) )
								return false;
							break;
					}
				}
			}
			return true;
		}
		
		function get_input_list()
		{
			return array( 
				'svp_source_url', 
				'svp_source_dirname',
				'svp_source_host',
				'svp_source_user',
				'svp_source_pass' );
		}
		
		function make_video_url( $filename = '' )
		{
			// Call parent class method
			parent::make_video_url( $filename );
			
			// Get utils class instance
			$svp_utils = new SVP_Utils();
			
			// Get current User Agent
			$user_agent = $svp_utils->get_user_agent();
			
			// Get source options
			$options = $this->get_options();
			
			// Initialize URL
			$url = '';
			
			// Constructs URL
			$url .= $svp_utils->add_endurl_slash( $options['svp_source_url'] );
			if ( ! empty( $options['svp_source_dirname'] ) )
				$url .= $svp_utils->add_endurl_slash( $options['svp_source_dirname'] );
			switch ( $user_agent )
			{
				case SVP_USER_AGENT_IPHONE:
				case SVP_USER_AGENT_IPAD:
					return '';
					break;
				default:
					$url .= urlencode( $filename );
					break;
			}
			return $url;
		}
	}
}
