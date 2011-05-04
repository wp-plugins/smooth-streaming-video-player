<?php
/**
 * IIS Smooth Streaming live source management.
 * 
 * @author Adenova <agence@adenova.fr>
 * @since 1.5.0
 */
if ( ! class_exists ( 'SVP_Source_IIS_Live' ) )
{
	require_once( 'class-source-base.php' );
	
	// It's a source for live videos : include the live video type class
	require_once( 'class-video-live.php' );
	
	class SVP_Source_IIS_Live extends SVP_Source_Base
	{
		
		function SVP_Source_IIS_Live()
		{
			$this->__construct();
		}
		
		function __construct()
		{
			$svp_video_live = new SVP_Video_Live();
			$this->set_videos_type( $svp_video_live->get_type() );
		}
		
		function configure()
		{
			$name = '';
			$options = array();
			$options['svp_source_url'] = '';
			$options['svp_source_dirname'] = '';
			$options['svp_source_compatibility_iphone'] = 0;
			$options['svp_source_iphone_dirname'] = '';
			$options['svp_source_host'] = '';
			$options['svp_source_user'] = '';
			$options['svp_source_pass'] = '';
			$options['svp_source_webroot'] = '';
			$id = $this->get_ID();
			if ( ! empty( $id ) )
			{
				$name = $this->get_name();
				$options = $this->get_options();
			}
			( $options['svp_source_compatibility_iphone'] == 1 ) ? $iphone_compatibility_checked = ' checked' : $iphone_compatibility_checked = '';
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
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_compatibility_iphone">' . __( 'iPhone compatibility', 'svp-translate' ) . '</label>
								</th>
								<td>
									<label for="svp_source_compatibility_iphone">
										<input name="svp_source_compatibility_iphone" type="checkbox" id="svp_source_compatibility_iphone" value="1"' . $iphone_compatibility_checked . ' />' . 
				__( 'Activate', 'svp-translate' ) . '
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_iphone_dirname">' . __( 'Videos directory name for iPhone', 'svp-translate' ) . '</label>
								</th>
								<td>
									<input name="svp_source_iphone_dirname" type="text" id="svp_source_iphone_dirname" value="' . $options['svp_source_iphone_dirname'] . '" class="regular-text code" />
									<br />' . __( 'This is the directory name that contains your videos for iPhone or iPad on the server. This field must be filled if the iPhone compatibility option has been enabled.', 'svp-translate' ) . '
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
							<tr valign="top">
								<th scope="row">
									<label for="svp_source_webroot">' . __( 'Path to web root', 'svp-translate' ) . '</label>
								</th>
								<td>
									<input name="svp_source_webroot" type="text" id="svp_source_webroot" value="' . $options['svp_source_webroot'] . '" class="regular-text code" />
									<br />' . __( 'If your FTP access not goes directly to web root, you must specify to path to it. Example : <code>webuser/www</code> or <code>www</code> &#8212; don&#8217;t add <code>/</code> as prefix or as suffix.', 'svp-translate' ) . '
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
					$cid = @ftp_connect( $options['svp_source_host'] );
					$login = @ftp_login( $cid, $options['svp_source_user'], $options['svp_source_pass'] );
					if ( ! $cid || ! $login )
					{
						wp_die( __( 'Error connecting to source. Check your source videos access settings.', 'svp-translate' ) );
						exit();
					}
					else
					{
						$directory = '.';
						( isset( $options['svp_source_webroot'] ) && ! empty( $options['svp_source_webroot'] ) ) ? $directory = $options['svp_source_webroot'] : $directory = '.';
						if ( isset( $options['svp_source_dirname'] ) && ! empty( $options['svp_source_dirname'] ) )
						{
							if ( $directory == '.')
								$directory = $options['svp_source_dirname'];
							else
								$directory .= '/' . $options['svp_source_dirname'];
						}
						$result = ftp_nlist( $cid, $directory );
						if ( $result === false )
						{
							wp_die( __( 'Error listing your videos source. Try later or check your videos directory name setting.', 'svp-translate' ) );
							exit();
						}
					}
					@ftp_close( $cid );
				}
			}
			
			// Create instance of adaptive video class
			$svp_video_live = new SVP_Video_Live();
			
			// Filter by extension
			$videos = new SVP_Videos();
			if ( $directory != '.' )
				$path = $directory . '/';
			$result = $videos->filter_by_extensions( $result, $svp_video_live->get_extensions(), $path );
			
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
						case 'svp_source_compatibility_iphone':
							break;
						case 'svp_source_iphone_dirname':
							if ( $data['svp_source_compatibility_iphone'] == 1)
							{
								if ( empty( $data[$key] ) )
									return false;
							}
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
						case 'svp_source_webroot':
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
				'svp_source_compatibility_iphone',
				'svp_source_iphone_dirname',
				'svp_source_host',
				'svp_source_user',
				'svp_source_pass',
				'svp_source_webroot' );
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
					if ( array_key_exists( 'svp_source_compatibility_iphone', $options ) && $options['svp_source_compatibility_iphone'] == 1 )
						$url .= urlencode( $filename ) . '/Manifest(format=' . SVP_VIDEO_SUFFIX_TS . ')';
					else
						return '';
					break;
				default:
					$url .= urlencode( $filename ) . '/Manifest';
					break;
			}
			return $url;
		}
	}
}
