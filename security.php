<?php
/*
.---------------------------------------------------------------------------.
|   Version: 4.0 RC1                                                        |
|   Author: Hany alsamman (project administrator && original founder)       |
|   Copyright (c) 2013, CODEX.COM. All Rights Reserved.                     |
| ------------------------------------------------------------------------- |
|   License: DISTRIBUTED UNDER CODEX.COM TEAM (PRIVETE ACCESS ONLY)	    |
'---------------------------------------------------------------------------'
*/

/**
 * PROPERTY_CPANEL.php
 *
 * @author Hany alsamman (Original founder <hany.alsamman@gmail.com>)
 * @copyright CODEXC.COM
 * @version $Id$
 * @pattern private
 * @access private
 */

class SECURITY{

    var $get_magic_quotes   = 0;
    var $allow_unicode      = 1;

    /*-------------------------------------------------------------------------*/
    // txt_stripslashes
    // ------------------
    // Make Big5 safe - only strip if not already...
    /*-------------------------------------------------------------------------*/

    /**
     * SECURITY::txt_stripslashes()
     *
     * @param mixed $t
     * @return
     */
    function txt_stripslashes($t)
    {
        if ( $this->get_magic_quotes )
        {
            $t = stripslashes($t);
            $t = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $t );
        }

        return $t;
    }

    /**
     * SECURITY::parse_clean_value()
     * Performs basic cleaning
     * Null characters, etc
     * @param mixed $val
     * @return
     */
    function parse_clean_value($val)
    {
        if ( $val == "" )
        {
            return "";
        }

        //$sBadChars = array("select", "drop", ";", "--", "insert", "delete", "xp_","#", "%", "&", "'", "(", ")", "/",":", ";", "<", ">", "=", "[", "]", "?", "`", "|");

        $val = preg_replace( "/select/i", "", $val );
        $val = preg_replace( "/insert/i", "", $val );
        $val = preg_replace( "/drop/i", "", $val );
        $val = preg_replace( "/delete/i", "", $val );

        $val = str_replace( ";", "", $val);
        $val = str_replace( "&#032;", " ", $this->txt_stripslashes($val) );

        // As cool as this entity is...
        $val = str_replace( "&#8238;"		, ''			  , $val );
        $val = str_replace( "&"				, "&amp;"         , $val );
        $val = str_replace( '"'				, "&quot;"        , $val );
        $val = str_replace( "\n"			, "<br />"        , $val ); // Convert literal newlines
        $val = str_replace( "$"				, "&#036;"        , $val );
        $val = str_replace( "\r"			, ""              , $val ); // Remove literal carriage returns
        $val = str_replace( "!"				, "&#33;"         , $val );
        $val = str_replace( "'"				, "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.

        // Ensure unicode chars are OK

        if ( $this->allow_unicode )
        {
            $val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val );

            //-----------------------------------------
            // Try and fix up HTML entities with missing ;
            //-----------------------------------------

            $val = preg_replace( "/&#(\d+?)([^\d;])/i", "&#\\1;\\2", $val );
        }

        return $val;
    }

    /*-------------------------------------------------------------------------*/
    // Key Cleaner - ensures no funny business with form elements
    /*-------------------------------------------------------------------------*/

    /**
     * SECURITY::parse_clean_key()
     *
     * @param mixed $key
     * @return
     */
    function parse_clean_key($key)
    {
        if ($key == "")
        {
            return "";
        }

        $key = htmlspecialchars(urldecode($key));
        $key = str_replace( ".."           , ""  , $key );
        $key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
        $key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );

        return $key;
    }


    /**
     * SECURITY::clean_globals()
     *
     * @param mixed $data
     * @param integer $iteration
     * @return
     */
    function clean_globals( &$data, $iteration = 0 )
    {
        // Crafty hacker could send something like &foo[][][][][][]....to kill Apache process
        // We should never have an globals array deeper than 10..

        if( $iteration >= 10 )
        {
            return $data;
        }

        if( count( $data ) )
        {
            foreach( $data as $k => $v )
            {
                if ( is_array( $v ) )
                {
                    $this->clean_globals( $data[ $k ], $iteration+1 );
                }
                else
                {

                    # Null byte characters
                    $v = preg_replace( '/\\\0/' , '&#92;&#48;', $v );
                    $v = preg_replace( '/\\x00/', '&#92;x&#48;&#48;', $v );
                    $v = str_replace( '%00'     , '%&#48;&#48;', $v );

                    # File traversal
                    $v = str_replace( '../'    , '&#46;&#46;/', $v );

                    $k = $this->parse_clean_key( $k );
                    $v = $this->parse_clean_value( $v );

                    $data[ $k ] = $v;
                }
            }
        }
    }


    /**
     * SECURITY::parse_incoming()
     *
     * @return
     */
    function parse_incoming()
    {
        //-----------------------------------------
        // Attempt to switch off magic quotes
        //-----------------------------------------

        //@set_magic_quotes_runtime(0);

        //$this->get_magic_quotes = @get_magic_quotes_gpc();

        //-----------------------------------------
        // Clean globals, first.
        //-----------------------------------------

        $this->clean_globals( $_GET );
        $this->clean_globals( $_POST );
        $this->clean_globals( $_COOKIE );
        $this->clean_globals( $_REQUEST );

    }
}

?>