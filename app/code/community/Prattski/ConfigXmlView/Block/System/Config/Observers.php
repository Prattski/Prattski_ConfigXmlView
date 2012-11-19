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
            $html .= '<div style="border: 1px solid red; background-color: #ffcccc; font-weight: bold; padding: 10px; margin-bottom: 20px;">';
            $html .= 'There are conflicting observer names. They have been highlighted by red text. Please resolve conflicts.';
            $html .= '</div>';
        }
        
        $html .= '<h2>Event Observers</h2>';
        $html .= $this->_getObservers();

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
        
        $html .= '<div class="grid">';
        $html .= '<div class="hor-scroll">';
        $html .= '<table cellspacing="0" class="data" id="observers-grid">';
        $html .= '<colgroup><col><col><col><col><col></colgroup>';
        $html .= '<thead><tr class="headings">';
        $html .= '<th><span class="nobr">Event</span></th>';
        $html .= '<th><span class="nobr">Module</span></th>';
        $html .= '<th><span class="nobr">Observer Name</span></th>';
        $html .= '<th><span class="nobr">Method</span></th>';
        $html .= '<th class="last"><span class="nobr">Scope</span></th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        
        // Loop through each event
        foreach ($this->_observers as $event => $observers) {
            
            // Loop through each observer for the current event
            foreach ($observers as $name => $details) {
                
                // If the current observer has a name conflict, set style
                $conflict = (key_exists($name, $this->_observerNameConflicts)) ? ' color: red; font-weight: bold;' : '';
                
                $html .= "<tr>";
                $html .= '<td>'.$event.'</td>';
                $html .= '<td>'.$details['module'].'</td>';
                $html .= '<td style="'.$conflict.'">'.$name.'</td>';
                $html .= '<td>'.$details['method'].'</td>';
                $html .= '<td>'.$details['scope'].'</td>';
                $html .= '</tr>';
            }
            
        }
        
        $html .= '</table>';
        $html .= '</div></div>';
        
        return $html;
    }
}   