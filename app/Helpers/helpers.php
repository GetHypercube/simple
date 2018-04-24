<?php

if (!function_exists('prep_url')) {
    /**
     * Prep URL
     *
     * Simply adds the http:// part if no scheme is included
     *
     * https://github.com/bcit-ci/CodeIgniter/blob/master/system/helpers/url_helper.php
     *
     * @param   string  the URL
     * @return  string
     */
    function prep_url($str = '')
    {
        if ($str === 'http://' OR $str === '') {
            return '';
        }
        $url = parse_url($str);
        if (!$url OR !isset($url['scheme'])) {
            return 'http://' . $str;
        }
        return $str;
    }
}


/**
 * Converts a MySQL Timestamp to Unix
 *
 * @access    public
 * @param    integer Unix timestamp
 * @return    integer
 */
if (!function_exists('mysql_to_unix')) {
    /**
     * Converts a MySQL Timestamp to Unix
     *
     * @param    int    MySQL timestamp YYYY-MM-DD HH:MM:SS
     * @return    int    Unix timstamp
     */
    function mysql_to_unix($time = '')
    {
        // We'll remove certain characters for backward compatibility
        // since the formatting changed with MySQL 4.1
        // YYYY-MM-DD HH:MM:SS
        $time = str_replace(array('-', ':', ' '), '', $time);
        // YYYYMMDDHHMMSS
        $hour = substr($time, 8, 2);

        return mktime(
            $hour == '' ? 0 : $hour,
            substr($time, 10, 2),
            substr($time, 12, 2),
            substr($time, 4, 2),
            substr($time, 6, 2),
            substr($time, 0, 4)
        );
    }
}


/**
 * Get Mime by Extension
 *
 * Translates a file extension into a mime type based on config/mimes.php.
 * Returns FALSE if it can't determine the type, or open the mime config file
 *
 * Note: this is NOT an accurate way of determining file mime types, and is here strictly as a convenience
 * It should NOT be trusted, and should certainly NOT be used for security
 *
 * @access    public
 * @param    string    path to file
 * @return    mixed
 */
if (!function_exists('get_mime_by_extension')) {
    function get_mime_by_extension($file)
    {
        $extension = strtolower(substr(strrchr($file, '.'), 1));

        global $mimes;

        if (!is_array($mimes)) {
            if (defined('ENVIRONMENT') AND is_file(APPPATH . 'config/' . ENVIRONMENT . '/mimes.php')) {
                include(APPPATH . 'config/' . ENVIRONMENT . '/mimes.php');
            } elseif (is_file(APPPATH . 'config/mimes.php')) {
                include(APPPATH . 'config/mimes.php');
            }

            if (!is_array($mimes)) {
                return FALSE;
            }
        }

        if (array_key_exists($extension, $mimes)) {
            if (is_array($mimes[$extension])) {
                // Multiple mime types, just give the first one
                return current($mimes[$extension]);
            } else {
                return $mimes[$extension];
            }
        } else {
            return FALSE;
        }
    }
}

/**
 * Create a Random String
 *
 * Useful for generating passwords or hashes.
 *
 * @access	public
 * @param	string	type of random string.  basic, alpha, alunum, numeric, nozero, unique, md5, encrypt and sha1
 * @param	integer	number of characters
 * @return	string
 */
if ( ! function_exists('random_string'))
{
    function random_string($type = 'alnum', $len = 8)
    {
        switch($type)
        {
            case 'basic'	: return mt_rand();
                break;
            case 'alnum'	:
            case 'numeric'	:
            case 'nozero'	:
            case 'alpha'	:

                switch ($type)
                {
                    case 'alpha'	:	$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum'	:	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric'	:	$pool = '0123456789';
                        break;
                    case 'nozero'	:	$pool = '123456789';
                        break;
                }

                $str = '';
                for ($i=0; $i < $len; $i++)
                {
                    $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
                }
                return $str;
                break;
            case 'unique'	:
            case 'md5'		:

                return md5(uniqid(mt_rand()));
                break;
            case 'encrypt'	:
            case 'sha1'	:

                $CI =& get_instance();
                $CI->load->helper('security');

                return do_hash(uniqid(mt_rand(), TRUE), 'sha1');
                break;
        }
    }
}