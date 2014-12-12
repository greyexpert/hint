<?php

class HINT_CMP_PrivateHint extends HINT_CMP_HintBase
{
    public function __construct($entityType, $entityId, array $avatar, $content )
    {
        parent::__construct($entityType, $entityId);
        
        $this->assign("avatar", $avatar);
        $this->assign("content", $content);
    }
}