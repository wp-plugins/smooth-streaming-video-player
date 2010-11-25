<?php

if (!class_exists("SVP_Utils"))
{
	
	class SVP_Utils
	{
		
		/**
		 * Retourne la valeur par défaut en cas de valeur postée vide.
		 * @param $default mixed Valeur par défaut ou précédente
		 * @param $value mixed Valeur postée par le formulaire
		 * @param $type string Type de la valeur postée
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
		
		// Retourne un attribut checked pour les cases à cocher
		function checked($value)
		{
			if (trim($value) == "on")
				print 'checked="checked"';
		}
		
		// Vérifie s'il s'agit d'une URL
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
		
		// Ajoute le slash final à une URL
		function addEndUrlSlash($value)
		{
			$value = (string)trim($value);
			if (substr($value, strlen($value) - 1, 1) != "/")
				$value .= "/";
			return $value;
		}
		
		// Vérifie s'il s'agit d'un entier numérique non nul et positif
		function isPositive($value)
		{
			$value = (int)trim($value);
			if (empty($value))
				return false;
			if ((int) $value <= 0)
				return false;
			return true;
		}
		
		// Vérifie s'il s'agit d'une couleur hexadécimale
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