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
class HINT_CMP_EventHintPreview extends HINT_CMP_HintPreviewBase
{
    public function getCoverPreview()
    {
        $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";

        return array(
            'url' => $staticUrl . "event_cover.jpg",
            'height' => 122,
            'imageCss' => "width: 100%; height: auto; top: -7px"
        );
    }
    
    protected function getEventInfo()
    {
        $eventInfo = array();

        $eventInfo["title"] = "New Year";
        $eventInfo["url"] = "javascript://";
        
        $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";
        $eventInfo['avatar'] =  $staticUrl . 'event_avatar.png';
        
        $eventInfo["date"] = array(
            "month" => OW::getLanguage()->text("base", "date_time_month_short_1"),
            "day" => 1
        );

        return $eventInfo;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $type = null;
        
        if ( empty($this->params["settings"]) )
        {
            $type = HINT_BOL_Service::getInstance()->getConfig("ehintType");
        }
        else
        {
            $type = $this->params["settings"]["ehintType"];
        }
        
        $hasLines = false;
        
        foreach ( $this->params["info"] as $line )
        {
            if ( !empty($line["key"]) )
            {
                $hasLines = true;
            }
        }
        
        $this->assign("hasLines", $hasLines);
        
        $type = empty($type) ? "date" : $type;
        $this->assign("type", $type);     
        $this->assign('event', $this->getEventInfo());
    }
}