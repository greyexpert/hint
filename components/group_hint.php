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
class HINT_CMP_GroupHint extends HINT_CMP_HintBase
{
    public function __construct($groupId)
    {
        parent::__construct( HINT_BOL_Service::ENTITY_TYPE_GROUP, $groupId );
    }
    
    public function getCover()
    {
        // TODO return group cover
        
        return null;
    }
    
    public function getGroupInfo( $groupId )
    {
        return HINT_CLASS_GroupsBridge::getInstance()->getGroupById($groupId);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign("group", $this->getGroupInfo($this->entityId));
    }
}
