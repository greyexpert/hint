<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.classes
 */
class HINT_CLASS_GroupsBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_GroupsBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_GroupsBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {

    }

    public function init()
    {
        if ( !OW::getPluginManager()->isPluginActive("groups") )
        {
            return;
        }
        
        HINT_CLASS_ParseManager::getInstance()->addParser(new HINT_CLASS_GroupParser());
    }
}