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
    
    /**
     * 
     * @param int $groupId
     * @return GROUPS_BOL_Group
     */
    public function getGroupById( $groupId )
    {
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        
        if ( $group === null )
        {
            return null;
        }
        
        $out = array();
        
        $out["id"] = $group->id;
        $out["timeStamp"] = $group->timeStamp;
        $out["userId"] = $group->userId;
        $out["title"] = $group->title;
        $out["url"] = GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        $out["avatar"] = GROUPS_BOL_Service::getInstance()
                ->getGroupImageUrl($group, GROUPS_BOL_Service::IMAGE_SIZE_SMALL);
        
        return $out;
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