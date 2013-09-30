<?php
/*
.---------------------------------------------------------------------------.
|                                                                           |
|   Author: Hany alsamman (project administrator && original founder)       |
|   Copyright (c) 2013, CODEXC.COM. All Rights Reserved.                    |
'---------------------------------------------------------------------------'
*/

/**
 * CONTROLLERS.php
 *
 * @package CONTROLLERS
 * @author Hany alsamman < hany.alsamman@gmail.com >
 * @copyright CODEXC.COM
 */

class CONTROLLERS
{

    /**
     * CONTROLLERS::__construct()
     *
     * @return
     */
    public function __construct()
    {

        /**
         * initialization the security system
         * parsing all incoming data [get,post,cookie,request]
         * cleaning  all values and keys by wonderful ways
         */
        $secure = new SECURITY();
        $secure->parse_incoming();
    }

    /**
     * CONTROLLERS::__destruct()
     *
     * This will be called automatically at the end of scope
     *
     * @return
     */
    public function __destruct()
    {

    }

}

?>