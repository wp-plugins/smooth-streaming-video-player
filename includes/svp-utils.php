<?php

if (!class_exists("SVP_Utils"))
{
	
	class SVP_Utils
	{
		
		/**
		 * Retourne la valeur par d�faut en cas de valeur post�e vide.
		 * @param $default mixed Valeur par d�faut ou pr�c�dente
		 * @param $value mixed Valeur post�e par le formulaire
		 * @param $type string Type de la valeur post�e
		 */
		function form_post_check($default, $value, $type = "string")
		{
			$value = strip_tags(trim($value));
			switch ($type)
			{
				case "integer":
					if (trim($value) == "")
						return $default;
					return (int)$value;
					break;
				case "url":
					if (!$this->isUrl($value))
						return $this->addEndUrlSlash($default);
					return (string)$this->addEndUrlSlash($value);
					break;
				case "positive":
					if (!$this->isPositive($value))
						return $default;
					return $value;
					break;
				case "hexcolor":
					if (!$this->isHexColor($value))
						return $default;
					return $value;
					break;
				default: // String
					if (empty($value))
						return $default;
					return (string)$value;
					break;
			}
		}
		
		// Retourne un attribut checked pour les cases � cocher
		function checked($value)
		{
			if (trim($value) == "on")
				print 'checked="checked"';
		}
		
		// V�rifie s'il s'agit d'une URL
		function isUrl($value)
		{
			$value = (string)trim($value);
			if (empty($value))
				return false;
			$pattern = '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@';
			if (preg_match($pattern, $value) == 0
				|| preg_match($pattern, $value) == false)
				return false;
			return true;
		}
		
		// Ajoute le slash final � une URL
		function addEndUrlSlash($value)
		{
			$value = (string)trim($value);
			if (substr($value, strlen($value) - 1, 1) != "/")
				$value .= "/";
			return $value;
		}
		
		// V�rifie s'il s'agit d'un entier num�rique non nul et positif
		function isPositive($value)
		{
			$value = (int)trim($value);
			if (empty($value))
				return false;
			if ((int) $value <= 0)
				return false;
			return true;
		}
		
		// V�rifie s'il s'agit d'une couleur hexad�cimale
		function isHexColor($value)
		{
			$value = (string)trim(strtolower($value));
			if (empty($value))
				return false;
			$pattern = '/^#[0-9a-f]{6}$/';
			if (preg_match($pattern, $value) == 0
				|| preg_match($pattern, $value) == false)
				return false;
			return true;
		}
	}
}