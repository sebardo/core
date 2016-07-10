<?php
namespace CoreBundle\Service;

class ShortCut
{

    public static function readDir($dir)
    {
        $retunrValues = array();
        if ($handle = opendir($dir)) {

            /* This is the correct way to loop over the directory. */
            while (false !== ($entry = readdir($handle))) {
                $retunrValues[] = $entry;
            }

            closedir($handle);
        }

        return $retunrValues;
    }

    public static function getOpenGraphData($url)
    {

        $returnValues = array();
        //obtengo los datos del enlace con su imagen titulo y descripcion haaa y la url
        $graph = OpenGraph::fetch($url);

        foreach ($graph as $key => $value) {
            switch ($key) {
                case 'title':
                    $returnValues['title'] = $value;
                    break;
                case 'description':
                    $returnValues['description'] = $value;
                    break;
                case 'site_name':
                    $returnValues['siteName'] = $value;
                    break;
                case 'image':
                    $returnValues['image'] = $value;
                    break;
            }
        }

        //Checking video link if youtube or vimeo
        $host = parse_url($url, PHP_URL_HOST);
        if ($host == 'vimeo.com') {
            $path = parse_url($url, PHP_URL_PATH);
            $clip_id = substr($path, 1);
            $returnValues['videoUrl'] = 'http://vimeo.com/moogaloop.swf?clip_id='.$clip_id;
        }
        if ($host == 'www.youtube.com') {
            $query = parse_url($url, PHP_URL_QUERY);
            $v = explode("=", $query);
            $returnValues['videoUrl']  = 'http://www.youtube.com/v/'. $v[1] .'?version=3&autohide=1';
        }
        if ($host == 'youtu.be') {
           $path = parse_url($url, PHP_URL_PATH);
           $returnValues['videoUrl']  = 'http://www.youtube.com/v'. $path .'?version=3&autohide=1';
        }

        //veo si alguno de los campos esta vacio y mando la funcion con curl
        if (isset($returnValues['title']) && isset($returnValues['description']) && isset($returnValues['image']) && isset($returnValues['siteName'])) {
            return $returnValues;
        }

        $arr = OpenGraph::wget($url);
        if(!isset($returnValues['title'])) $returnValues['title'] = $arr['title'];
        if(!isset($returnValues['description'])) $returnValues['description'] = $arr['description'];

        if (!isset($returnValues['image'])) {
            $returnValues['image'] = $arr['og:image'];
        }

        if(!isset($returnValues['images'])) $returnValues['images'] = $arr['images'][0];

        return $returnValues;
    }

    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public static function createPath($path)
    {
        if (is_dir($path)) return true;
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
        $return = self::createPath($prev_path);

        return ($return && is_writable($prev_path)) ? mkdir($path) : false;
    }

    public static function recurseCopy($src,$dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            ShortCut::createPath($dst);
        }

        while (false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurseCopy($src . '/' . $file,$dst . '/' . $file);
                } else {
//                    print_r($src . '/' . $file);echo PHP_EOL;
//                    print_r($dst . '/' . $file);
//                    die();
                    @copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function recurseRemove($directory, $empty=FALSE)
    {
       // if the path has a slash at the end we remove it here
       if (substr($directory,-1) == '/') {
           $directory = substr($directory,0,-1);
       }

      // if the path is not valid or is not a directory ...
       if (!file_exists($directory) || !is_dir($directory)) {
           // ... we return false and exit the function
           return FALSE;

       // ... if the path is not readable
       } elseif (!is_readable($directory)) {
           // ... we return false and exit the function
           return FALSE;

       // ... else if the path is readable
       } else {

           // we open the directory
           $handle = opendir($directory);

           // and scan through the items inside
           while (FALSE !== ($item = readdir($handle))) {
               // if the filepointer is not the current directory
               // or the parent directory
               if ($item != '.' && $item != '..') {
                   // we build the new path to delete
                   $path = $directory.'/'.$item;

                   // if the new path is a directory
                   if (is_dir($path)) {
                       // we call this function with the new path
                       self::recurseRemove($path);

                   // if the new path is a file
                   } else {
                       // we remove the file
                       unlink($path);
                   }
               }
          }
           // close the directory
           closedir($handle);

           // if the option to empty is not set to true
           if ($empty == FALSE) {
               // try to delete the now empty directory
               if (!rmdir($directory)) {
                   // return false if not possible
                   return FALSE;
               }
           }
           // return success
           return TRUE;
       }
    }

    /**
     *
     * Return the class name without the namespace.
     * If $object is not an object, we return the type.
     *
     * if the object is a Doctrine proxy : (implements \Doctrine\ORM\Proxy\Proxy)
     * return the original type
     *
     * @param  vary   $object the object used to find name.
     * @return string the class or type name.
     */
    public static function getClass($object)
    {
        if (is_object($object)) {
            //If proxied by Doctrine, return the real class name (the parent)
            if (array_key_exists('Doctrine\\ORM\\Proxy\\Proxy', class_implements($object))) {
                $class = explode('\\', get_parent_class($object));
            } else $class = explode('\\', get_class($object));

            return end($class);
        } elseif (is_string($object)) {
            $class = explode('\\', $object);

            return end($class);
        }

        return gettype($object);
    }

    public static function isGeoIpInstalled()
    {
        return function_exists('geoip_country_code_by_name');
    }

    public static function getName($var)
    {
        if (is_object($var)) return self::getClass($var);
        if (is_string($var)) return "String:$var";
        return gettype($var);
    }

    public static function getFirstClassInFile($file)
    {
        $tokens = token_get_all( file_get_contents($file) );
        $class_token = false;
        foreach ($tokens as $token) {
            if ( !is_array($token) ) continue;
            if ($token[0] == T_CLASS) {
                $class_token = true;
            } elseif ($class_token && $token[0] == T_STRING) {
                return $token[1];
                $class_token = false;
            }
        }
    }

    public static function isEntity($value)
    {
        if (gettype($value) != 'object') return false;
        $temp = explode('\\', get_class($value));
        if((count($temp) > 1) && ($temp[count($temp) - 2] == 'Entity')
           && method_exists($value, 'getId')) return True;
        if ($temp[0] == 'Proxies' && method_exists($value, 'getId')) return True;
        return false;
    }

    public static function isEntityOrId($value)
    {
        if (self::isInt($value)) return true;
        //for alphanumeric ID
        if (is_string($value)) return true;
        return self::isEntity($value);
    }

    public static function isArrayOfEntityOrId($value)
    {
        if (!is_array($value)) return false;
        foreach ($value as $entity) {
            if (!self::isEntityOrId($entity)) return false;
        }

        return true;
    }

    /**
    * Check if integer or a valid integer string,
    */
    public static function isInt($value)
    {
        if (is_numeric($value)) return is_int(intval($value));
        return False;
    }

    public static function generatePassword($syllables = 3, $use_prefix = false)
    {

        // Define function unless it is already exists
        if (!function_exists('ae_arr')) {
            // This function returns random array element
            function ae_arr(&$arr)
            {
                return $arr[rand(0, sizeof($arr)-1)];
            }
        }

        // 20 prefixes
        $prefix = array('aero', 'anti', 'auto', 'bi', 'bio',
                        'cine', 'deca', 'demo', 'dyna', 'eco',
                        'ergo', 'geo', 'gyno', 'hypo', 'kilo',
                        'mega', 'tera', 'mini', 'nano', 'duo');

        // 10 random suffixes
        $suffix = array('dom', 'ity', 'ment', 'sion', 'ness',
                        'ence', 'er', 'ist', 'tion', 'or');

        // 8 vowel sounds
        $vowels = array('a', 'o', 'e', 'i', 'y', 'u', 'ou', 'oo');

        // 20 random consonants
        $consonants = array('w', 'r', 't', 'p', 's', 'd', 'f', 'g', 'h', 'j',
                            'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'qu');

        $password = $use_prefix?ae_arr($prefix):'';
        $password_suffix = ae_arr($suffix);

        for ($i=0; $i<$syllables; $i++) {
        // selecting random consonant
        $doubles = array('n', 'm', 't', 's');
        $c = ae_arr($consonants);
        if (in_array($c, $doubles)&&($i!=0)) {
        // maybe double it
        if (rand(0, 2) == 1) // 33% probability
            $c .= $c;
        }
        $password .= $c;
        //

        // selecting random vowel
        $password .= ae_arr($vowels);

        if ($i == $syllables - 1) // if suffix begin with vovel
        if (in_array($password_suffix[0], $vowels)) // add one more consonant
        $password .= ae_arr($consonants);

        }

        // selecting random suffix
        $password .= $password_suffix;

        return $password;
    }

    //from http://www.php-security.org/2010/05/09/mops-submission-04-generating-unpredictable-session-ids-and-hashes/index.html
    public static function generateUniqueId($maxLength = null)
    {
        $entropy = '';

        // try ssl first
        if (function_exists('openssl_random_pseudo_bytes')) {
            $entropy = openssl_random_pseudo_bytes(64, $strong);
            // skip ssl since it wasn't using the strong algo
            if ($strong !== true) {
                $entropy = '';
            }
        }

        // add some basic mt_rand/uniqid combo
        $entropy .= uniqid(mt_rand(), true);

        // try to read from the windows RNG
        if (class_exists('COM')) {
            try {
                $com = new COM('CAPICOM.Utilities.1');
                $entropy .= base64_decode($com->GetRandom(64, 0));
            } catch (Exception $ex) {
            }
        }

        // try to read from the unix RNG
        if (is_readable('/dev/urandom')) {
            $h = fopen('/dev/urandom', 'rb');
            $entropy .= fread($h, 64);
            fclose($h);
        }

        $hash = hash('whirlpool', $entropy);
        if ($maxLength) {
            return substr($hash, 0, $maxLength);
        }

        return $hash;
    }


    public static function contains($haystack, $needle, $case = true, $pos = 0)
    {
        if ($case) {
            $result = (strpos($haystack, $needle, 0) === $pos);
        } else {
            $result = (stripos($haystack, $needle, 0) === $pos);
        }

        return $result;
    }

    public static function strBetween($str,$start,$end)
    {
        if (preg_match_all('/' . preg_quote($start) . '(.*?)' . preg_quote($end) . '/',$str,$matches)) {
            return $matches[1];
        }
        // no matches
        return false;
    }

    /**
     *
     * @param string  $needle   string to search in
     * @param string  $haystack string that has to be at starts.
     * @param boolean $case     if true, case sensitive.
     */
    public static function startsWith($haystack, $needle, $case = true)
    {
        return self::contains($haystack, $needle, $case, 0);
    }

    public static function endsWith($haystack, $needle, $case = true)
    {
        return self::contains($haystack, $needle, $case, (strlen($haystack) - strlen($needle)));
    }

    public static function jsonPrettyPrint($json, $html=FALSE)
    {
        $tabcount = 0;
        $result = '';
        $inquote = false;
        $ignorenext = false;

        if ($html) {
            $tab = "&nbsp;&nbsp;&nbsp;";
            $newline = "<br/>";
        } else {
            $tab = "\t";
            $newline = "\n";
        }

        for ($i = 0; $i < strlen($json); $i++) {
            $char = $json[$i];

            if ($ignorenext) {
                $result .= $char;
                $ignorenext = false;
            } else {
                switch ($char) {
                    case '{':
                        $tabcount++;
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '}':
                        $tabcount--;
                        $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                        break;
                    case ',':
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '"':
                        $inquote = !$inquote;
                        $result .= $char;
                        break;
                    case '\\':
                        if ($inquote) $ignorenext = true;
                        $result .= $char;
                        break;
                    default:
                        $result .= $char;
                }
            }
        }

        return $result;
    }

    public static function json_decode($json, $assoc = FALSE)
    {
        // remove UTF8 order mask if here.
        // http://en.wikipedia.org/wiki/Byte_order_mark
        if (urlencode(substr($json,0 ,3)) == '%EF%BB%BF') {
            $json = substr($json, 3);
        }

        $json = str_replace(array("\n","\r"),"",$json);

        return json_decode($json,$assoc);
    }

    public static function getJsonLastError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'No errors';
                break;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return 'nknown error';
                break;
            }

            return 'nknown error';

    }

    public static function printStack()
    {
        $e = new \Exception();
        print "\n=============================START PRINT STACK\n";
        print $e->getTraceAsString();
        print "\n=============================END PRINT STACK\n";
    }
}
