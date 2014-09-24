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
 * @package hint.controllers
 */
class HINT_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    const PLUGIN_URL = "http://www.oxwall.org/store/item/634";
    
    protected function hintSettings( $entityType, $previewCmpClass, $headerBridge, $features = array(), $requirements = array() )
    {
        $this->setPageHeading(OW::getLanguage()->text('hint', 'admin_heading'));
        
        HINT_BOL_Service::getInstance()->saveConfig("admin_notified", 1);
        
        $tpl = OW::getPluginManager()->getPlugin("hint")
                ->getCtrlViewDir() . "admin_hint_settings.html";
        
        $this->setTemplate($tpl);
        
        $sortableStatic = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl() . "jquery-ui.min.js";
        OW::getDocument()->addScript($sortableStatic);
        
        $this->assign("previewCmp", $previewCmpClass);
        
        $buttonConfig = $this->getActionConfigs($entityType);
        $this->assign("buttonConfigs", $buttonConfig);
        
        $info = array();
        $info[HINT_BOL_Service::INFO_LINE0] = HINT_BOL_Service::getInstance()->getInfoConfig($entityType, HINT_BOL_Service::INFO_LINE0);
        $info[HINT_BOL_Service::INFO_LINE1] = HINT_BOL_Service::getInstance()->getInfoConfig($entityType, HINT_BOL_Service::INFO_LINE1);
        $info[HINT_BOL_Service::INFO_LINE2] = HINT_BOL_Service::getInstance()->getInfoConfig($entityType, HINT_BOL_Service::INFO_LINE2);
        
        $form = new HINT_ConfigurationForm($entityType, $buttonConfig, $features, $info, $headerBridge);
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $form->process();

            OW::getFeedback()->info(OW::getLanguage()->text("hint", "admin_configs_saved"));
            $this->redirect();
        }

        $this->addForm($form);
        
        $params = array();
        $params["actions"] = array();
        foreach ( $buttonConfig as $action )
        {
            if ( $action["active"] )
            {
                $params["actions"][] = $action["key"];
            }
            
            if ( !empty($action["requirements"]["long"]) )
            {
                $requirements[] = array(
                    "text"=> $action["requirements"]["long"],
                    "hidden" => !$action["active"],
                    "key" => $action["key"]
                );
            }
        }
        
        $this->assign("requirements", $requirements);
        
        $params["features"] = $features;
        $params["info"] = $info;

        $cmp = new $previewCmpClass(HINT_BOL_Service::ENTITY_TYPE_USER, $params);
        $this->addComponent("preview", $cmp);

        $this->assign("entityType", HINT_BOL_Service::ENTITY_TYPE_USER);
        $this->assign("info", $info);

        $preloaderUrl = OW::getThemeManager()->getCurrentTheme()->getStaticUrl() . 'images/ajax_preloader_button.gif';
        $this->assign("preloaderUrl", $preloaderUrl);
        
        $this->assign("pluginUrl", self::PLUGIN_URL);
    }


    public function index()
    {
        $userUrl = OW::getRouter()->urlForRoute("hint-configuration-user");
        $this->redirect($userUrl);
    }
    
    public function user()
    {
        $headerBridge = HINT_CLASS_UheaderBridge::getInstance();
        
        $features = array();
        $features["cover"] = $headerBridge->isEnabled();
        
        $requirements = array();
                
        if ( !$headerBridge->isActive() )
        {
            $pluginEmbed = '<a href="' . HINT_CLASS_UheaderBridge::PLUGIN_URL . '" target="_blank">' . HINT_CLASS_UheaderBridge::PLUGIN_TITLE . '</a>';
            
            $requirements[] = array(
                "text" => OW::getLanguage()->text("hint", "uheader_required_long", array(
                    "plugin" => $pluginEmbed,
                    "feature" => OW::getLanguage()->text("hint", "admin_profile_cover_option")
                )),
                
                "hidden" => !$features["cover"],
                "key" => "cover"
            );
            
            $this->assign("coverRequired", OW::getLanguage()->text("hint", "uheader_required_short", array(
                "plugin" => $pluginEmbed
            )));
        }
        
        $this->hintSettings(HINT_BOL_Service::ENTITY_TYPE_USER, 'HINT_CMP_UserHintPreview', $headerBridge, $features, $requirements);
    }

     public function group()
    {
        $headerBridge = HINT_CLASS_GheaderBridge::getInstance();
         
        $features = array();
        $features["cover"] = $headerBridge->isEnabled();
        
        $requirements = array();
                
        if ( !$headerBridge->isActive() )
        {
            $pluginEmbed = '<a href="' . HINT_CLASS_GheaderBridge::PLUGIN_URL . '" target="_blank">' . HINT_CLASS_GheaderBridge::PLUGIN_TITLE . '</a>';
            
            $requirements[] = array(
                "text" => OW::getLanguage()->text("hint", "gheader_required_long", array(
                    "plugin" => $pluginEmbed,
                    "feature" => OW::getLanguage()->text("hint", "admin_group_cover_option")
                )),
                
                "hidden" => !$features["cover"],
                "key" => "cover"
            );
            
            $this->assign("coverRequired", OW::getLanguage()->text("hint", "gheader_required_short", array(
                "plugin" => $pluginEmbed
            )));
        }
        
        $this->hintSettings(HINT_BOL_Service::ENTITY_TYPE_GROUP, 'HINT_CMP_GroupHintPreview', $headerBridge, $features, $requirements);
    }
    
    private function getActionConfigs( $feedType )
    {
        return HINT_BOL_Service::getInstance()->getButtonsSettings($feedType);
    }
    
    public function saveOrder()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect403Exception;
        }
        
        $entityType = trim($_GET["entityType"]);
        
        $sort = json_decode($_GET["sort"]);
        HINT_BOL_Service::getInstance()->setButtonsOrder($entityType, $sort);
    }
}

class HINT_ConfigurationForm extends Form
{
    private $actions, $entityType, $headerBridge;

    public function __construct( $entityType, $actions, $features, $info, $headerBridge )
    {
        parent::__construct("HINT_ConfigurationForm");

        $language = OW::getLanguage();

        $this->headerBridge = $headerBridge;
        $this->actions = $actions;
        $this->entityType = $entityType;

        // Actions
        foreach ( $actions as $action )
        {
            $field = new CheckboxField("action-" . $action["key"]);
            $field->setId("action-" . $action["key"]);
            $field->addAttribute("data-key", $action["key"]);
            $field->setValue($action["active"]);
            $field->setLabel($action["label"]);
            $field->addAttribute("class", "h-refresher");

            $this->addElement($field);
        }
        
        // Additional Features
        $field = new CheckboxField("header_enabled");
        $field->setId("feature_header");
        $field->setValue($features["cover"]);
        $field->addAttribute("class", "h-refresher");
        $field->addAttribute("data-key", "cover");

        $this->addElement($field);
        
        // User Information
        
        $line0Options = HINT_BOL_Service::getInstance()->getInfoLineSettings($entityType, HINT_BOL_Service::INFO_LINE0);
        
        $field = new Selectbox("info_" . HINT_BOL_Service::INFO_LINE0);
        $field->setId("info0");
        
        foreach ( $line0Options as $lineOption )
        {
            $field->addOption($lineOption["key"], $lineOption["label"]);
        }
        
        if ( !empty($info[HINT_BOL_Service::INFO_LINE0]["key"]) )
        {
            $field->setValue($info[HINT_BOL_Service::INFO_LINE0]["key"]);
        }

        $this->addElement($field);
        
        $questions = $this->findQuestions();
        $questionOptions = array();
        foreach ( $questions as $question )
        {
            $questionOptions[$question->name] = BOL_QuestionService::getInstance()->getQuestionLang($question->name);
        }
        
        $field = new Selectbox("info_" . HINT_BOL_Service::INFO_LINE0 . "_question");
        $field->setId("info0_q");
        $field->setOptions($questionOptions);
        
        if ( !empty($info[HINT_BOL_Service::INFO_LINE0]["question"]) )
        {
            $field->setValue($info[HINT_BOL_Service::INFO_LINE0]["question"]);
        }

        $this->addElement($field);
        
        
        $line1Options = HINT_BOL_Service::getInstance()->getInfoLineSettings($entityType, HINT_BOL_Service::INFO_LINE1);

        $field = new Selectbox("info_" . HINT_BOL_Service::INFO_LINE1);
        $field->setId("info1");
        
        foreach ( $line1Options as $lineOption )
        {
            $field->addOption($lineOption["key"], $lineOption["label"]);
        }
        
        if ( !empty($info[HINT_BOL_Service::INFO_LINE1]["key"]) )
        {
            $field->setValue($info[HINT_BOL_Service::INFO_LINE1]["key"]);
        }

        $this->addElement($field);
        
        $field = new Selectbox("info_" . HINT_BOL_Service::INFO_LINE1 . "_question");
        $field->setId("info1_q");
        $field->setOptions($questionOptions);
        
        if ( !empty($info[HINT_BOL_Service::INFO_LINE1]["question"]) )
        {
            $field->setValue($info[HINT_BOL_Service::INFO_LINE1]["question"]);
        }

        $this->addElement($field);
        
        
        $line2Options = HINT_BOL_Service::getInstance()->getInfoLineSettings($entityType, HINT_BOL_Service::INFO_LINE2);

        $field = new Selectbox("info_" . HINT_BOL_Service::INFO_LINE2);
        $field->setId("info2");
        
        foreach ( $line2Options as $lineOption )
        {
            $field->addOption($lineOption["key"], $lineOption["label"]);
        }
        
        if ( !empty($info[HINT_BOL_Service::INFO_LINE2]["key"]) )
        {
            $field->setValue($info[HINT_BOL_Service::INFO_LINE2]["key"]);
        }

        $this->addElement($field);
        
        $field = new Selectbox("info_" . HINT_BOL_Service::INFO_LINE2 . "_question");
        $field->setId("info2_q");
        $field->setOptions($questionOptions);
        
        if ( !empty($info[HINT_BOL_Service::INFO_LINE2]["question"]) )
        {
            $field->setValue($info[HINT_BOL_Service::INFO_LINE2]["question"]);
        }

        $this->addElement($field);
        
        
        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('hint', 'admin_save_btn'));
        $this->addElement($submit);
    }

    private function findQuestions()
    {
        $ignorePresentations = array(BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX);
        
        $questions = BOL_QuestionService::getInstance()->findAllQuestions();
        
        $out = array();
        
        foreach ( $questions as $question )
        {
            /* @var $question BOL_Question */
            
            if ( !$question->onView || in_array($question->presentation, $ignorePresentations) )
            {
                continue;
            }
            
            $out[] = $question;
        }
        
        return $out;
    }
    
    private function saveInfoLine( $line, $values )
    {
        if ( empty($values["info_" . $line]) )
        {
            return;
        }
        
        $key = $values["info_" . $line];
        $question = $key == "base-question" ? $values["info_" . $line . "_question"] : null;
        
        HINT_BOL_Service::getInstance()->saveInfoConfig($this->entityType, $line, $key, $question);
    }
    
    public function process()
    {
        $service = HINT_BOL_Service::getInstance();
        $values = $this->getValues();

        foreach ( $this->actions as $action )
        {
            $service->setActionActive($this->entityType, $action["key"], !empty($values["action-" . $action["key"]]));
        }
        
        $this->headerBridge->setEnabled($values["header_enabled"]);
        
        $this->saveInfoLine(HINT_BOL_Service::INFO_LINE0, $values);
        $this->saveInfoLine(HINT_BOL_Service::INFO_LINE1, $values);
        $this->saveInfoLine(HINT_BOL_Service::INFO_LINE2, $values);
    }
}