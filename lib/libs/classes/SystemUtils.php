<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SystemUtils
 *
 * @author snick
 */
class SystemUtils {

    public static function trimToNumeric($string, $mode = 'int') {
        if ($string == null || !isset($string) || strlen($string) == 0) {
            return 0;
        }
        if ('int' == $mode) {
            return preg_replace('/[^0-9]/', '', $string);
        } else {
            return preg_replace('/[^0-9]./', '', $string);
        }
    }

    public static function trimToDate($string) {
        try {
            if (!isset($string)) {
                return '';
            }
            if (strpos($string, ':') === FALSE) {
                $string.=' 00:00:00';
                $date = DateTime::createFromFormat('m/d/Y H:i:s', trim($string));
            } else {
                $date = DateTime::createFromFormat('m/d/Y H:i:s', trim($string));
            }
            if (is_bool($date)) {
                return $string;
            }
            return date_format($date, 'Y-m-d H:i:s');
        } catch (Exception $e) {
            SystemUtils::logActivity('system', 'app', 'unable to convert date ' . $e->getMessage() . ' trace .. ' . $e->getTraceAsString());
            return $string;
        }
    }

    public static function formatPhoneNumber($phoneNumber) {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (strlen($phoneNumber) > 10) {
            $countryCode = substr($phoneNumber, 0, strlen($phoneNumber) - 10);
            $areaCode = substr($phoneNumber, -10, 3);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+' . $countryCode . ' (' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
        } else if (strlen($phoneNumber) == 10) {
            $areaCode = substr($phoneNumber, 0, 3);
            $nextThree = substr($phoneNumber, 3, 3);
            $lastFour = substr($phoneNumber, 6, 4);

            $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
        } else if (strlen($phoneNumber) == 7) {
            $nextThree = substr($phoneNumber, 0, 3);
            $lastFour = substr($phoneNumber, 3, 4);

            $phoneNumber = $nextThree . '-' . $lastFour;
        }

        return $phoneNumber;
    }

    public static function cleanSQLQuoteArray($array) {
        if (sizeof($array) < 1) {
            return "0,0";
        }
        $quoted = array();
        foreach ($array AS $v) {
            $quoted[] = "'{$v}'";
        }
        return implode(',', $quoted);
    }

    public static function generateKey($len, $readable = false, $hash = false) {
        $key = '';

        if ($hash) {
            $key = substr(sha1(uniqid(rand(), true)), 0, $len);
        } else if ($readable) {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            for ($i = 0; $i < $len; ++$i) {
                $key .= substr($chars, (mt_rand() % strlen($chars)), 1);
            }
        } else {
            for ($i = 0; $i < $len; ++$i) {
                $key .= chr(mt_rand(33, 126));
            }
        }
        if (strlen($key) < $len) {
            $key.= SystemUtils::generateRandomString($len - strlen($key));
        }
        return $key;
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generateUsername($email) {
        try {
            $Email = explode('@', $email);
            $email = $Email[0] . substr(uniqid(rand(), true), 0, 5);
            unset($Email);
            return $email;
        } catch (Exception $e) {
            logActivity('user', 'app', 'An exception occurred while generating username' . $e->getMessage());
            return $email;
        }
    }

    public static function format_percentage($value, $max) {
        if ($max == 0 || $value == 0) {
            return 0;
        }
        return round(($value / $max) * 100, 0);
    }

    public static function commandExist($cmd) {
        try {
            $val = shell_exec("which $cmd");
            echo $val;
            return (empty($val) ? FALSE : TRUE);
        } catch (Exception $e) {
            return FALSE;
        }
    }

    //this is a simple function to log txt msg in file
    public static function logActivity($type, $usertype, $logMsg) {
        try {
            $file = date('Y-m-d') . '_misapp.log';
            $dir .= '/logs/' . $usertype . '/' . $type . '/';
            $dir.=date('Y') . '/';
            if (!file_exists($dir) && !mkdir($dir, 0755, true)) {
                return false;
            }
            $logMsg = PHP_EOL . date('Y-m-d H:i:s') . '-------------------------START LOG-----------------------------------------' . PHP_EOL . $logMsg . PHP_EOL;
            $logMsg .= '--------------------------------------------END LOG-------------------------------------------' . PHP_EOL;
            return file_put_contents($dir . $file, $logMsg, FILE_APPEND);
        } catch (Exception $e) {
            
        }
        return false;
    }

    public static function nicetime($date) {
        if (empty($date)) {
            return "a while ago";
        }

        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();
        $unix_date = strtotime($date);

        // check validity of date
        if (empty($unix_date)) {
            return "";
        }

        // is it future date or past date
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = "ago";
        } else {
            $difference = $unix_date - $now;
            $tense = "from now";
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j].= "s";
        }

        return "$difference $periods[$j] {$tense}";
    }

    public static function formatTextUTF8($text) {
        return mb_detect_encoding($text . " ", "UTF-8,CP1252") == "UTF-8" ? iconv("UTF-8", "CP1252", $text) : $text;
    }

    public static function getLoremText($len) {
        $lorem = array('lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'curabitur', 'vel', 'hendrerit', 'libero', 'eleifend', 'blandit', 'nunc', 'ornare', 'odio', 'ut', 'orci', 'gravida', 'imperdiet', 'nullam', 'purus', 'lacinia', 'a', 'pretium', 'quis', 'congue', 'praesent', 'sagittis', 'laoreet', 'auctor', 'mauris', 'non', 'velit', 'eros', 'dictum', 'proin', 'accumsan', 'sapien', 'nec', 'massa', 'volutpat', 'venenatis', 'sed', 'eu', 'molestie', 'lacus', 'quisque', 'porttitor', 'ligula', 'dui', 'mollis', 'tempus', 'at', 'magna', 'vestibulum', 'turpis', 'ac', 'diam', 'tincidunt', 'id', 'condimentum', 'enim', 'sodales', 'in', 'hac', 'habitasse', 'platea', 'dictumst', 'aenean', 'neque', 'fusce', 'augue', 'leo', 'eget', 'semper', 'mattis', 'tortor', 'scelerisque', 'nulla', 'interdum', 'tellus', 'malesuada', 'rhoncus', 'porta', 'sem', 'aliquet', 'et', 'nam', 'suspendisse', 'potenti', 'vivamus', 'luctus', 'fringilla', 'erat', 'donec', 'justo', 'vehicula', 'ultricies', 'varius', 'ante', 'primis', 'faucibus', 'ultrices', 'posuere', 'cubilia', 'curae', 'etiam', 'cursus', 'aliquam', 'quam', 'dapibus', 'nisl', 'feugiat', 'egestas', 'class', 'aptent', 'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'per', 'conubia', 'nostra', 'inceptos', 'himenaeos', 'phasellus', 'nibh', 'pulvinar', 'vitae', 'urna', 'iaculis', 'lobortis', 'nisi', 'viverra', 'arcu', 'morbi', 'pellentesque', 'metus', 'commodo', 'ut', 'facilisis', 'felis', 'tristique', 'ullamcorper', 'placerat', 'aenean', 'convallis', 'sollicitudin', 'integer', 'rutrum', 'duis', 'est', 'etiam', 'bibendum', 'donec', 'pharetra', 'vulputate', 'maecenas', 'mi', 'fermentum', 'consequat', 'suscipit', 'aliquam', 'habitant', 'senectus', 'netus', 'fames', 'quisque', 'euismod', 'curabitur', 'lectus', 'elementum', 'tempor', 'risus', 'cras');
        $i = 0;
        $lorem_txt = '';
        while ($i < $len) {
            $lorem_txt.=$lorem[rand(0, sizeof($lorem) - 1)] . ' ';
            $i++;
        }
        return $lorem_txt;
    }

    public static function mime_file_content_type($filename) {
        try {
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME);
                return $finfo->file($filename);
            }
        } catch (Exception $e) {
            SystemUtils::logActivity('system', $_SESSION['app'], 'An exc caught in  mime_file_content_type() ' . $e->getMessage() . ' ' . $e->getTraceAsString());
        }
        if (function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }

        SystemUtils::detectFileMimeType($filename);
    }

    public static function getExtension($file) {
        $file_args = explode('.', $file);
        return end($file_args);
    }

    public static function detectFileMimeType($filename) {
        if (function_exists('escapeshellcmd') && function_exists('shell_exec')) {
            $file = escapeshellcmd($filename);
            return shell_exec("file -b --mime-type -m /usr/share/misc/magic {$file}");
        } else {
            return $filename;
        }
    }

    public static function guessMIME($file) {

        $ext = SystemUtils::getExtension($file);
        if ('jpeg' == $ext || 'jpg' == $ext || 'png' == $ext) {
            return 'image/' . $ext;
        } else if ('pdf' == $ext || 'doc' == $ext || 'docx' == $ext || 'odt' == $ext) {
            return 'application/' . $ext;
        } else {
            return 'application/octet-stream';
        }
    }

    public static function formatOrdinalNumber($num) {
        try {
            if (!in_array(($num % 100), array(11, 12, 13))) {
                switch ($num % 10) {
                    // Handle 1st, 2nd, 3rd
                    case 1: return $num . 'st';
                    case 2: return $num . 'nd';
                    case 3: return $num . 'rd';
                }
            }
        } catch (Exception $e) {
            SystemUtils::logActivity('mis.init', 'system', 'error in formatOrdinalNumber() ' . $e->getMessage());
        }
        return $num . 'th';
    }

    public static function getDayFromDate($start_date) {
        $start = new DateTime($start_date);
        $day_start = $start->format('d');
        return intval($day_start);
    }

    public static function getMonthFromDate($start_date) {
        $start = new DateTime($start_date);
        $month_start = $start->format('m');
        return intval($month_start);
    }

    public static function getYearFromDate($start_date) {
        $start = new DateTime($start_date);
        $month_start = $start->format('Y');
        return intval($month_start);
    }

    public static function get_day_name($timestamp) {
        $date = 'some time';
        try {
            $date = date('d/m/Y', $timestamp);
            switch ($date) {
                case date('d/m/Y'):
                    $date = 'Today';
                    break;
                case date('d/m/Y', time() - (24 * 60 * 60)):
                    $date = 'Yesterday';
                    break;
                case date('d/m/Y', time() + (24 * 60 * 60)):
                    $date = 'Tomorrow';
                    break;
                default :
                    break;
            }
            return $date . '  ' . date('h:i A', $timestamp);
        } catch (Exception $e) {
            SystemUtils::logActivity('mis.init', 'system', 'error in get_day_name() ' . $e->getMessage());
        }
        return $date;
    }

    public static function convertSpeed($speed, $to, $postfix_unit = true) {
        $unit = 'm/s';
        if ($to == 'kph') {
            $unit = 'km/h';
            $speed = round(($speed * 3.6), 2); // kph
        } else if ($to == 'mph') {
            $unit = 'mi/h';
            $speed = round(($speed * 2.23694), 2); // mph
        }
        if ($postfix_unit) {
            $speed.=' ' . $unit;
        }
        return $speed;
    }

    public static function fitSubEventIntoEvent($_start, $_end, $start, $end) {
        try {
            return strtotime($_start) >= strtotime($start) && (strtotime($end) >= strtotime($_end));
        } catch (Exception $e) {
            SystemUtils::logActivity('mis.init', 'system', 'error in fitSubEventIntoEvent() ' . $e->getMessage());
        }
        return false;
    }

    public static function distance($lat1, $lon1, $lat2, $lon2, $_unit) {
        try {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.15150000000000000;
            $unit = strtoupper($_unit);

            if ($unit == "M") {
                return ($miles * 1609.344000000000000);
            } else if ($unit == "K") {
                return ($miles * 1.609344000000000000);
            } else if ($unit == "N") {
                return ($miles * 0.868400000000000000);
            } else {
                return $miles;
            }
        } catch (Exception $e) {
            SystemUtils::logActivity('mis.init', 'system', 'error in distance() ' . $e->getMessage());
        }
        return -1;
    }

}
