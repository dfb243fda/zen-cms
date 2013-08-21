<?php

namespace App\Utility;

class GeneralUtility
{
    public static function isValidUrl($url)
    {
        return (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) !== FALSE);
    }
    
    /**
	 * Check for item in list
	 * Check if an item exists in a comma-separated list of items.
	 *
	 * @param string $list Comma-separated list of items (string)
	 * @param string $item Item to check for
	 * @return boolean TRUE if $item is in $list
	 */
    static public function inList($list, $item) {
		return strpos(',' . $list . ',', ',' . $item . ',') !== FALSE ? TRUE : FALSE;
	}
    
    /**
	 * Removes an item from a comma-separated list of items.
	 *
	 * @param string $element Element to remove
	 * @param string $list Comma-separated list of items (string)
	 * @return string New comma-separated list of items
	 */
	static public function rmFromList($element, $list) {
		$items = explode(',', $list);
		foreach ($items as $k => $v) {
			if ($v == $element) {
				unset($items[$k]);
			}
		}
		return implode(',', $items);
	}
    
    /**
	 * Explodes a string and trims all values for whitespace in the ends.
	 * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
	 *
	 * @param string $delim Delimiter string to explode with
	 * @param string $string The string to explode
	 * @param boolean $removeEmptyValues If set, all empty values will be removed in output
	 * @param integer $limit If positive, the result will contain a maximum of
	 * @return array Exploded values
	 */
	static public function trimExplode($delim, $string, $removeEmptyValues = FALSE, $limit = 0) {
		$explodedValues = explode($delim, $string);
		$result = array_map('trim', $explodedValues);
		if ($removeEmptyValues) {
			$temp = array();
			foreach ($result as $value) {
				if ($value !== '') {
					$temp[] = $value;
				}
			}
			$result = $temp;
		}
		if ($limit != 0) {
			if ($limit < 0) {
				$result = array_slice($result, 0, $limit);
			} elseif (count($result) > $limit) {
				$lastElements = array_slice($result, $limit - 1);
				$result = array_slice($result, 0, $limit - 1);
				$result[] = implode($delim, $lastElements);
			}
		}
		return $result;
	}
}