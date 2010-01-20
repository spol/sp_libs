<?php

/**
 * AutoLinker class automatically converts inline urls and email address in strings into 
 * clickable links.
 *
 * @author Seb Pollard <seb@spolster.co.uk>
 * @version 1
 */
class AutoLinker {

	/**
	 * Converts all urls and emails to links.
	 * @param 	string 	$string		The text (possibly) containing emails/urls.
	 * @return 	string				The processed text.
	 */
	static public function linkAll($string) {
		$string = self::linkEmails($string);
		return self::linkURLS($string);
	}

	/**
	 * Wraps all urls in passed text in a tags.
	 * @param 	string 	$string		The text (possibly) containing urls.
	 * @return 	string				The processed text.
	 */
	static public function linkURLs($string) {
		return preg_replace_callback(
			"/
				([\s]|^)										# word boundary or start of string.
				(\()?											# (optional) opening parenthesis.
				(https?:\/\/)?									# (optional) protocol
				(												# begin domain name
					([A-Z0-9]([A-Z0-9\-]{0,61}[A-Z0-9])?\.)+	# One or more subdomains 
																# (1-63 chars per rfc1035, plus trailing dot)
					[A-Z]{2,6}									# top-level domain (2-6 chars)
				)												# end domain name
				(\/[A-Z0-9\/\.\-\_\?\=\&\+\%\#]+)?				# path
				(\))?											# (optional) closing parenthesis.
				(\s|$)											# word boundary or end of string
			/ix",
			array('AutoLinker', "_linkUrlsCallback"), $string);
	}
	
	static private function _linkUrlsCallback($matches) {
		if (empty($matches[3])) {
			$matches[3] = "http://";
		}
		if (empty($matches[7])) {
			$matches[7] = "";
		}
		$url = $matches[3] . $matches[4] . $matches[7];

		if ($matches[2] == "(" && $matches[8] == ")") { // link in parentheses.
			$pre = $matches[1] . $matches[2];
			$post = $matches[8] . $matches[9];
		}
		else {
			$pre = $matches[1];
			$post = $matches[9];
			$url .= $matches[8];
		}

		return $pre . '<a href="' . $url . '">' . $url . '</a>' . $post;
	}

	/**
	 * Wraps all email address in passed text in a tags (mailto:).
	 * @param 	string 	$string		The text (possibly) containing email addresses.
	 * @return 	string				The processed text.
	 */
	static public function linkEmails($string) {
		return preg_replace("/\b([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\b/i", "<a href='mailto:\\1'>\\1</a>", $string);
	}
}