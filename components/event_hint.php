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
 * @package hint.components
 */
class HINT_CMP_EventHint extends HINT_CMP_HintBase
{
    public function __construct($eventId)
    {
        parent::__construct( HINT_BOL_Service::ENTITY_TYPE_EVENT, $eventId );
    }
    
    public function getCover()
    {
        $bridge = HINT_CLASS_EheaderBridge::getInstance();
        
        if ( !$bridge->isActive() || !$bridge->isEnabled() )
        {
            return null;
        }
        
        $cover = $bridge->getCover($this->entityId);
        
        if ( $cover === null )
        {
            return null;
        }
        
        return $this->prepareCover($cover["src"], $cover["data"]);
    }
    
    public function getEventInfo( $eventId )
    {
        return HINT_CLASS_EventsBridge::getInstance()->getEventById($eventId);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign("event", $this->getEventInfo($this->entityId));
    }
}
