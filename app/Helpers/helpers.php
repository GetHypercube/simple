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