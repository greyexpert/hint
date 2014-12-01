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
    
    
    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }

        $language = OW::getLanguage();
        
        $eventId = $params["entityId"];
        $eventInfo = $this->getEventById($eventId);
        
        if ( empty($eventInfo) )
        {
            return;
        }
        
        // View Event button
        
        $event->add(array(
            "key" => "event-view",
            "label" => $language->text("hint", "button_view_event_label"),
            "attrs" => array(
                "href" => $eventInfo["url"],
                "target" => "_blank"
            )
        ));
        
        // Flag Event button
        // TODO
        $event->add(array(
            "key" => "event-flag",
            "label" => $language->text("hint", "button_flag_event_label"),
            "attrs" => array(
                "href" => "javascript://"
            )
        ));
    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }

        $language = OW::getLanguage();

        // Event View
        
        $event->add(array(
            "key" => "event-view",
            "label" => $language->text("hint", "button_view_event_label"),
            "attrs" => array("href" => "javascript://")
        ));
        
        // Event Flag
        
        $event->add(array(
            "key" => "event-flag",
            "label" => $language->text("base", "flag"),
            "attrs" => array("href" => "javascript://")
        ));
    }

    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }

        $language = OW::getLanguage();
        $service = HINT_BOL_Service::getInstance();
        
        // View Event
        
        $viewEvent = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-view");
        $event->add(array(
            "key" => "event-view",
            "active" => $viewEvent === null ? false : $viewEvent,
            "label" => $language->text("hint", "button_view_event_config")
        ));
        
        // Flag Event
        
        $flagEvent = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-flag");
        $event->add(array(
            "key" => "event-flag",
            "active" => $flagEvent === null ? false : $flagEvent,
            "label" => $language->text("base", "flag")
        ));
    }
    
    public function onCollectInfoConfigs( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }
        
        $event->add(array(
            "key" => "event-desc",
            "label" => $language->text("hint", "info_event_desc_label")
        ));
        
        $event->add(array(
            "key" => "event-users",
            "label" => $language->text("hint", "info_event_users_label")
        ));
                
        $event->add(array(
            "key" => "event-date",
            "label" => $language->text("hint", "info_event_date_label")
        ));
    }
    
    public function onInfoPreview( OW_Event $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }
        
        switch ( $params["key"] )
        {
            case "event-date":
                $event->setData($language->text("hint", "info_event_date_preview"));
                break;
            
            case "event-users":
                $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";
        
                $data = array();

                for ( $i = 0; $i < 6; $i++ )
                {
                    $data[] = array(
                        "src" => $staticUrl . "user_" . $i . ".jpg",
                        "url" => "javascript://"
                    );
                }

                $users = new HINT_CMP_UserList($data);

                $event->setData($users->render());
                break;
            
            case "event-desc":
                $description = UTIL_String::truncate($language->text("hint", "info_event_desc_preview"), 110, "...");
                $event->setData('<span class="ow_remark ow_small">' . $description . '</span>');
                break;
        }
    }
    
    public function onInfoRender( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }
        
        $eventId = $params["entityId"];
        $eventInfo = $this->getEventById($eventId);
        
        if ( empty($eventInfo) )
        {
            return;
        }
        
        switch ( $params["key"] )
        {
            case "event-date":
                // TODO
                break;
            
            case "event-users":
                // TODO
                break;
            
            case "base-desc":
                // TODO
                break;
        }
    }
    
    

    public function init()
    {
        if ( !OW::getPluginManager()->isPluginActive("event") )
        {
            return;
        }
        
        HINT_CLASS_ParseManager::getInstance()->addParser(new HINT_CLASS_EventParser());
        
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));
        
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_INFO_CONFIG, array($this, 'onCollectInfoConfigs'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_PREVIEW, array($this, 'onInfoPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_RENDER, array($this, 'onInfoRender'));
    }
}