<?php
class HtmlEscape {
    /**
     * Escapes special characters in a string for use in HTML.
     *
     * @param string $string The string to escape.
     * @return string The escaped string.
     */
    public static function escape(string $string) : string {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    }
    
    /**
     * Escapes an array of strings for use in HTML.
     *
     * @param array $array The array to escape.
     * @return array The escaped array.
     */
    public static function escapeArray(array $array) : array {
        $escapedArray = [];
	
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $escapedArray[$key] = self::escapeArray($value);
            } elseif(is_string($value)) {
                $escapedArray[$key] = self::escape($value);
            } else {
                $escapedArray[$key] = $value;
            }
        }

        return $escapedArray;
    }
    
    /**
     * Escapes an object's properties for use in HTML.
     *
     * @param object $iStdClass The object to escape.
     * @return object The escaped object.
     */
    public static function escapeObject(object $iStdClass) : object {
        $escaped = new \stdClass();
	
        foreach($iStdClass as $key => $value) {
            if(is_string($value)) {
                $escaped->$key = self::escape($value);
            } elseif(is_array($value)) {
                $escapedArray[$key] = self::escapeArray($value);
            } else {
                $escaped->$key = $value;
            }
        }
	
        return $escaped;
    }
}