<?php
/**
 * Front source management class.
 * 
 * This class include a concrete source management class.
 * 
 * @author Adenova <agence@adenova.fr>
 * @since 1.5.0
 */
if ( ! class_exists ( 'SVP_Source' ) )
{
	class SVP_Source
	{
		var $type = '';
		
		function set_type( $type )
		{
			$this->type = trim( $type );
		}
		
		function get_type()
		{
			return $this->type;
		}
		
		/**
		 * Retourne une instance de la classe de source à configurer.
		 *
		 * @return object Instance de classe
		 * @since 1.5.0
		 */
		function factory( $type )
		{
			$this->set_type( $type );
			$filename = $this->get_filename();
			if ( ! file_exists( dirname( __FILE__ ) . '/' . $filename ) )
			{
				wp_die( sprintf( __( 'The filename &laquo;&nbsp;%s&nbsp;&raquo; does not exist.' , 'svp-translate' ), $filename ) );
				exit();
			}
			require_once( $this->get_filename() );
			$class = $this->get_classname();
			if ( ! class_exists ( $class ) )
			{
				wp_die( sprintf( __( 'The class &laquo;&nbsp;%s&nbsp;&raquo; does not exist.', 'svp-translate' ), $class ) );
				exit();
			}
			return new $class();
		}
		
		/**
		 * Retourne l'ensemble des sources stockées en base de données.
		 *
		 * @return array Liste des sources
		 * @since 1.5.0
		 */
		function get_sources()
		{
			global $wpdb;
			$sql = 'SELECT s.ID, s.name, s.is_configured, s.is_scanned, t.label type, t.code FROM ' . $wpdb->prefix . 'svp_sources s
				LEFT JOIN ' . $wpdb->prefix . 'svp_source_types t
				ON t.code = s.source_type_code';
			return $wpdb->get_results( $sql );
		}
		
		/**
		 * Construit le nom du fichier de la classe de source à inclure.
		 *
		 * @return string Nom du fichier
		 * @since 1.5.0
		 */
		function get_filename()
		{
			return 'class-source-' . strtolower( $this->get_type() ) . '.php';
		}
		
		/**
		 * Construit le nom de la classe de source à instancier.
		 *
		 * @return string Nom de la classe
		 * @since 1.5.0
		 */
		function get_classname()
		{
			return 'SVP_Source_' . $this->camelize( $this->get_type() );
		}
		
		/**
		 * Camelize a string for class name contruction. 
		 *
		 * @param mixed $str String to camelize
		 * @param mixed $separator Separator of class name
		 * @return mixed Class name to instanciate
		 * @since 1.5.0
		 */
		function camelize( $str, $separator = '_' )
		{
			$camelized_words = array();
			$str = str_replace( '-', '_', $str );
			$words = explode( $separator, trim( $str ) );
			foreach ( $words as $word )
			{
				$word = strtolower( $word );
				if ( $word == 'iis' )
					$camelized_words[] = strtoupper( $word );
				else
					$camelized_words[] = ucfirst( $word );
			}
			return implode( $separator, $camelized_words );
		}
		
		/**
		 * Retourne le label d'un type de source.
		 * 
		 * @param string $code Code du type de la source
		 * @return object Source type object
		 * @since 1.5.0
		 */
		function get_name( $code )
		{
			global $wpdb;
			$sql = 'SELECT label FROM ' . $wpdb->prefix . 'svp_source_types WHERE code = %s';
			return $wpdb->get_row( $wpdb->prepare( $sql, trim( $code ) ) );
		}
		
		/**
		 * Retourne la liste des types de sources.
		 * 
		 * @return array Liste des types de sources
		 * @since 1.5.0
		 */
		function get_source_types()
		{
			global $wpdb;
			return $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'svp_source_types ORDER BY label' );
		}
	}
}