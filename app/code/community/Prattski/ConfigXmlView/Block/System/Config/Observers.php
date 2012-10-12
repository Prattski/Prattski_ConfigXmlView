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
    /**
     * Observers array
     * 
     * @var array 
     */
    protected $_observers;
    
    /**
     * Observer name conflicts array
     * 
     * @var array 
     */
    protected $_observerNameConflicts;
    
    /**
     * Generate html to be output in the system config
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string 
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_init();
        
        $html = '';
        
        // Display warning if there are observe name conflicts
        if (!empty($this->_observerNameConflicts)) {
            $html .= '<div style="border: 1px solid red; background-color: #ffcccc; font-weight: bold; padding: 10px; margin-bottom: 30px;">';
            $html .= 'There are conflicting observer names. They have been highlighted by red text. Please resolve conflicts.';
            $html .= '</div>';
        }
        
        $html .= '<div style="border:1px solid #CCCCCC;margin-bottom:10px;padding:10px 5px 5px 20px;">';
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
        
        // Sort all the observers by event name
        ksort($this->_observers);
        
        $this->_observerNameConflicts = $config->getObserverNameConflicts();
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
        
        // If there are no observers, return "None"
        if (empty($this->_observers)) {
            $html .= '<p>None</p>';
            return $html;
        }
        
        $html .= '<table>';
        $html .= '<thead style="font-weight: bold"><td>Event</td><td>Module</td><td>Observer Name</td><td>Method</td><td>Scope</td></thead>';
        
        // Loop through each event
        foreach ($this->_observers as $event => $observers) {
            
            // Loop through each observer for the current event
            foreach ($observers as $name => $details) {
                
                // If the current observer has a name conflict, set style
                $conflict = (key_exists($name, $this->_observerNameConflicts)) ? ' color: red; font-weight: bold;' : '';
                
                $html .= "<tr>";
                $html .= '<td style="padding: 5px 20px 5px 5px;">'.$event.'</td>';
                $html .= '<td style="padding: 5px 20px 5px 5px;">'.$details['module'].'</td>';
                $html .= '<td style="padding: 5px 20px 5px 5px;'.$conflict.'">'.$name.'</td>';
                $html .= '<td style="padding: 5px 20px 5px 5px;">'.$details['method'].'</td>';
                $html .= '<td style="padding: 5px 20px 5px 5px;">'.$details['scope'].'</td>';
                $html .= '</tr>';
            }
            
        }
        
        $html .= '</table>';
        
        return $html;
    }
}   