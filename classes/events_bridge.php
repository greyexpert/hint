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
class HINT_CLASS_EventsBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_EventsBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_EventsBridge
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
    public function getEventById( $eventId )
    {
        $eventDto = EVENT_BOL_EventService::getInstance()->findEvent($eventId);
        
        if ( $eventDto === null )
        {
            return null;
        }
        
        $out = array();
        
        $out["id"] = $eventDto->id;
        $out["startTimeStamp"] = $eventDto->startTimeStamp;
        $out["endTimeStamp"] = $eventDto->endTimeStamp;
        $out["timeStamp"] = $eventDto->createTimeStamp;
        
        $out["userId"] = $eventDto->userId;
        $out["title"] = $eventDto->title;
        $out["description"] = $eventDto->description;
        $out["url"] = OW::getRouter()->urlForRoute('event.view', array(
            'eventId' => $eventDto->id
        ));
        
        $out["avatar"] = !empty($eventDto->image)
                ? EVENT_BOL_EventService::getInstance()->generateImageUrl($eventDto->image, true) 
                : EVENT_BOL_EventService::getInstance()->generateDefaultImageUrl();
        
        return $out;
    }

    public function init()
    {
        if ( !OW::getPluginManager()->isPluginActive("event") )
        {
            return;
        }
        
        HINT_CLASS_ParseManager::getInstance()->addParser(new HINT_CLASS_EventParser());
    }
}