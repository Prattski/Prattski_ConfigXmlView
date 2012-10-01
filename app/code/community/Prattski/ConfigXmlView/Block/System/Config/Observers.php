<?php
/**
 * Prattski - Config XML View
 *
 * @category    Prattski
 * @package     Prattski_ConfigXmlView
 * @copyright   Copyright (c) 2012 Prattski (http://prattski.com)
 * @author      Josh Pratt (josh@prattski.com)
 */

/**
 * System Config Rewrites Block
 *
 * @category    Prattski
 * @package     Prattski_ConfigXmlView
 */
class Prattski_ConfigXmlView_Block_System_Config_Observers extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_observers;
    
    /**
     * Generate html to be output in the system config
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string 
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_init();
        
        $html = '<div style="border:1px solid #CCCCCC;margin-bottom:10px;padding:10px 5px 5px 20px;">';
        $html .= '<h2>Event Observers</h2>';
        $html .= $this->_getObservers();
        $html .= '</div>';

        return $html;
    }
    
    /**
     * Initialize the core config object to populate data 
     */
    protected function _init()
    {
        $config = Mage::getModel('prattski_configxmlview/mage_core_config');
        $config->init();
        $this->_observers = $config->getObservers();
    }
    
    /**
     * Generate html display of all event observers in the local and community
     * code pools.
     * 
     * @return html 
     */
    protected function _getObservers()
    {
        $html = '';
        
        if (empty($this->_observers)) {
            $html .= '<p>None</p>';
        }
        
        foreach ($this->_observers as $event => $observers) {
            
            $html .= '<h3>'.$event.'</h3>';
            
            $html .= '<ul>';
            
            foreach ($observers as $name => $details) {
                $html .= '<li>'.$name;
                $html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;Module: '.$details['module'].'</li>';
                $html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;Class/Method: '.$details['class'].'::'.$details['method'].'</li>';
                $html .= '</li>';
            }
            
            $html .= '</ul>';
        }
        
        return $html;
    }
}   