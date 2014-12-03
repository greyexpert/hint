<?php

class HINT_CMP_ContextButton extends OW_Component
{
    public function __construct( $label, $content = null, $iconClass = null ) 
    {
        parent::__construct();
        
        $this->assign("label", $label);
        $this->assign("content", $content);
        
        $this->assign("iconClass", $iconClass);
    }
}