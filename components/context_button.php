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
    
    public function setId( $id )
    {
        $this->assign("uniqId", $id);
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $script = '$(document).off("hover", ".ow_context_action").on("hover", ".ow_context_action",function(e) {
                        if (e.type == "mouseenter") {
                            $(this).find(".ow_tooltip").css({opacity: 0, top: 10}).show().stop(true, true).animate({top: 17, opacity: 1}, "fast"); 
                        }
                        else { // mouseleave
                            $(this).find(".ow_tooltip").hide();
                        }     
                    }
                );';

        OW::getDocument()->addOnloadScript($script);
    }
}