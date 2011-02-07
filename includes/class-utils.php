<?php

if ( ! class_exists( 'SVP_Utils' ) )
{
	class SVP_Utils
	{	
		/**
		 * Retourne la valeur par défaut en cas de valeur postée vide.
		 * @param $default mixed Valeur par défaut ou précédente
		 * @param $value mixed Valeur postée par le formulaire
		 * @param $type string Type de la valeur postée
		 */
		function form_post_check( $default, $value, $type = 'string' )
		{
			$value = strip_tags( trim( $value) );
			switch ( $type )
			{
				case 'integer':
					if (trim( $value ) == '' )
						return $default;
					return (int) $value;
					break;
				case 'url':
					if ( ! $this->is_url( $value ) )
						return $this->add_endurl_slash( $default );
					return (string) $this->add_endurl_slash( $value );
					break;
				case 'positive':
					if ( ! $this->is_positive( $value ) )
						return $default;
					return $value;
					break;
				case 'hexcolor':
					if ( ! $this->is_hexcolor( $value ) )
						return $default;
					return $value;
					break;
				default: // String
					if ( empty( $value ) )
						return $default;
					return (string) $value;
					break;
			}
		}
		
		/**
		 * Return checked string for radio or checkbox input.
		 *
		 * @since 1.1.0
		 * @param string $value Value to check
		 * @return string
		 */
		function checked( $value )
		{
			if ( trim( $value ) == 'on' )
				print 'checked="checked"';
		}
		
		/**
		 * Check if is an URL.
		 *
		 * @since 1.1.0
		 * @param string $value Value to check
		 * @return bool
		 */
		function is_url( $value )
		{
			$value = (string) trim( $value );
			if ( empty( $value ) )
				return false;
			$pattern = "@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@";
			if ( preg_match( $pattern, $value ) == 0 || preg_match( $pattern, $value ) == false)
				return false;
			return true;
		}
		
		/**
		 * Check if is a domain name.
		 *
		 * @since 1.5.0
		 * @param string $value Value to check
		 * @return bool
		 */
		function is_domain( $value )
		{
			$value = (string) trim( $value );
			if ( empty( $value ) )
				return false;
			$pattern = "@([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?@";
			if ( preg_match( $pattern, $value ) == 0 || preg_match( $pattern, $value ) == false)
				return false;
			return true;
		}
		
		/**
		 * Add an end slash to an URL.
		 * 
		 * @since 1.1.0
		 * @param string $value Value to check
		 * @return string URL with end slash
		 */
		function add_endurl_slash( $value )
		{
			$value = (string) trim( $value );
			if ( substr( $value, strlen( $value ) - 1, 1 ) != '/' )
				$value .= '/';
			return $value;
		}
		
		/**
		 * Delete an end slash from an URL.
		 * 
		 * @since 1.5.0
		 * @param string $value Value to check
		 * @return string URL without end slash
		 */
		function delete_endurl_slash( $value )
		{
			$value = (string) trim( $value );
			if ( substr( $value, strlen( $value ) - 1, 1 ) == '/' )
				$value = substr( $value, 0, strlen( $value ) - 1);
			return $value;
		}
		
		/**
		 * Check if is a positive integer.
		 *
		 * @since 1.1.0
		 * @param string $value Value to check
		 * @return bool
		 */
		function is_positive( $value )
		{
			$value = (int) trim( $value );
			if ( empty( $value ) )
				return false;
			if ( (int) $value <= 0 )
				return false;
			return true;
		}
		
		/**
		 * Check if is an hexadecimal color.
		 *
		 * @since 1.1.0
		 * @param string $value Value to check
		 * @return bool
		 */
		function is_hexcolor( $value )
		{
			$value = (string) trim( strtolower( $value ) );
			if ( empty( $value ) )
				return false;
			$pattern = "/^#[0-9a-f]{6}$/";
			if ( preg_match( $pattern, $value ) == 0 || preg_match( $pattern, $value ) == false )
				return false;
			return true;
		}
		
		/**
		 * Check if is an IP address.
		 *
		 * @since 1.5.0
		 * @param string $value Value to check
		 * @return bool
		 */
		function is_ip( $value )
		{
			$value = (string) trim( strtolower( $value ) );
			if ( empty( $value ) )
				return false;
			$pattern = "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/";
			if ( preg_match( $pattern, $value ) == 0 || preg_match( $pattern, $value ) == false )
				return false;
			return true;
		}
		
		/**
		 * Returns the current User Agent.
		 * 
		 * @since 1.0.0
		 * @return string User Agent code
		 */
		function get_user_agent()
		{
			if ( ! defined( 'SVP_USER_AGENT' ) )
				define( 'SVP_USER_AGENT', SVP_USER_AGENT_OTHER );
			return SVP_USER_AGENT;
		}
	}
}