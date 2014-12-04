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
        
        $out["accessibility"] = $eventDto->whoCanView;
        
        $out["userId"] = $eventDto->userId;
        $out["location"] = $eventDto->location;
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
    
    public function getUserIds( $eventId, $status, $count )
    {
        $users = EVENT_BOL_EventService::getInstance()->findEventUsers($eventId, $status, null, $count);
        
        $out = array();
        foreach ( $users as $user )
        {
            $out[] = $user->userId;
        }
        
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

        // Event Invite
        
        $event->add(array(
            "key" => "event-invite",
            "label" => $language->text("hint", "button_invite_event_label"),
            "attrs" => array("href" => "javascript://")
        ));
        
        // Event Attend
        
        $contextBtn = new HINT_CMP_ContextButton($language->text("hint", "button_attend_event_label"));
        
        $event->add(array(
            "key" => "event-attend",
            "html" => '<li id="event-attend" class="h-preview">' . $contextBtn->render() . '</li>'
        ));
        
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
        
        // Attend Event
        
        $attendEvent = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-attend");
        $event->add(array(
            "key" => "event-attend",
            "active" => $attendEvent === null ? true : $attendEvent,
            "label" => $language->text("hint", "button_attend_event_config")
        ));
        
        // Invite Event
        
        $inviteEvent = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-invite");
        $event->add(array(
            "key" => "event-invite",
            "active" => $inviteEvent === null ? true : $inviteEvent,
            "label" => $language->text("hint", "button_invite_event_config")
        ));
        
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
            "active" => $flagEvent === null ? true : $flagEvent,
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
            "key" => "event-date",
            "label" => $language->text("hint", "info_event_date_label")
        ));
        
        $event->add(array(
            "key" => "event-start-date",
            "label" => $language->text("hint", "info_event_start_date_label")
        ));
        
        $event->add(array(
            "key" => "event-end-date",
            "label" => $language->text("hint", "info_event_end_date_label")
        ));
        
        $event->add(array(
            "key" => "event-created-by",
            "label" => $language->text("hint", "info_event_created_by_label")
        ));
        
        $event->add(array(
            "key" => "event-location",
            "label" => $language->text("hint", "info_event_location_label")
        ));
        
        $event->add(array(
            "key" => "event-access",
            "label" => $language->text("hint", "info_event_access_label")
        ));
        
        $event->add(array(
            "key" => "event-access-creator",
            "label" => $language->text("hint", "info_event_access_and_creator_label")
        ));
        
        if ( $params["line"] != HINT_BOL_Service::INFO_LINE0 )
        {
            $event->add(array(
                "key" => "event-desc",
                "label" => $language->text("hint", "info_event_desc_label")
            ));
            
            $event->add(array(
                "key" => "event-users",
                "label" => $language->text("hint", "info_event_users_label")
            ));
        }
    }
    
    public function onInfoPreview( OW_Event $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }
        
        $startDate = UTIL_DateTime::formatSimpleDate(strtotime("1/1/15"));
        $endDate = UTIL_DateTime::formatSimpleDate(strtotime("12/31/15"));
        
        $userEmbed = '<a href="javascript://">Angela Smith</a>';
        
        switch ( $params["key"] )
        {
            case "event-access-creator":
                
                $event->setData($language->text("hint", "event_info_access_and_creator", array(
                    "user" => $userEmbed,
                    "accessibility" => $language->text("hint", "event_info_access_by_invitation")
                )));
                break;
            
            case "event-access":
                
                $event->setData($language->text("hint", "event_info_access_by_invitation"));
                break;
            
            case "event-created-by":
                
                $event->setData($language->text("hint", "event_info_created_by", array(
                    "user" => $userEmbed
                )));
                break;
            
            case "event-date":
                
                $event->setData($language->text("hint", "event_info_date", array(
                    "startDate" => $startDate,
                    "endDate" => $endDate
                )));
                break;
            
            case "event-start-date":
                
                $event->setData($language->text("hint", "event_info_start_date", array(
                    "startDate" => $startDate
                )));
                break;
            
            case "event-end-date":
                
                $event->setData($language->text("hint", "event_info_end_date", array(
                    "endDate" => $endDate
                )));
                break;
            
            case "event-users":
                $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";
        
                $data = array();

                for ( $i = 0; $i < 9; $i++ )
                {
                    $data[] = array(
                        "src" => $staticUrl . "user_" . $i . ".jpg",
                        "url" => "javascript://"
                    );
                }

                $users = new HINT_CMP_UserList($data, array(), null, 9);

                $event->setData($users->render());
                break;
            
            case "event-desc":
                $description = UTIL_String::truncate($language->text("hint", "info_event_desc_preview"), 150, "...");
                $event->setData('<span class="ow_remark ow_small">' . $description . '</span>');
                break;
            
            case "event-location":
                $event->setData($language->text("hint", "info_event_location_preview"));
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
        
        $language = OW::getLanguage();
        
        $type = HINT_BOL_Service::getInstance()->getConfig("ehintType");
        
        $userName = BOL_UserService::getInstance()->getDisplayName($eventInfo["userId"]);
        $userUrl = BOL_UserService::getInstance()->getDisplayName($eventInfo["userId"]);
        
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';
        
        $access = $eventInfo["accessibility"] == 1
                    ? $language->text("hint", "event_info_access_public")
                    : $language->text("hint", "event_info_access_by_invitation");
        
        $startDate = UTIL_DateTime::formatSimpleDate($eventInfo["startTimeStamp"]);
        $endDate = UTIL_DateTime::formatSimpleDate($eventInfo["endTimeStamp"]);
        
        switch ( $params["key"] )
        {
            case "event-access-creator":
                $event->setData($language->text("hint", "event_info_access_and_creator", array(
                    "user" => $userEmbed,
                    "accessibility" => $access
                )));
                break;
            
            case "event-access":
                
                $event->setData($access);
                break;
            
            case "event-created-by":
                
                $event->setData($language->text("hint", "event_info_created_by", array(
                    "user" => $userEmbed
                )));
                break;
            
            case "event-date":
                
                $event->setData($language->text("hint", "event_info_date", array(
                    "startDate" => $startDate,
                    "endDate" => $endDate
                )));
                break;
            
            case "event-start-date":
                
                $event->setData($language->text("hint", "event_info_start_date", array(
                    "startDate" => $startDate
                )));
                break;
            
            case "event-end-date":
                
                $event->setData($language->text("hint", "event_info_end_date", array(
                    "endDate" => $endDate
                )));
                break;
            
            case "event-users":
                $count = $type == "image" ? 6 : 9;
                $userIds = $this->getUserIds($eventId, 1, $count + 1);

                if ( empty($userIds) )
                {
                    return;
                }
                
                $title = OW::getLanguage()->text("hint", "event_users_list_title");
                $data = BOL_AvatarService::getInstance()->getDataForUserAvatars(array_slice($userIds, 0, $count), true, true, false, false);
                $users = new HINT_CMP_UserList($data, $userIds, $title, $count);

                $event->setData($users->render());
                break;
            
            case "event-desc":
                $description = UTIL_String::truncate($eventInfo["description"], 150, "...");
                $event->setData('<span class="ow_remark ow_small">' . $description . '</span>');
                break;
            
            case "event-location":
                $event->setData($eventInfo["location"]);
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