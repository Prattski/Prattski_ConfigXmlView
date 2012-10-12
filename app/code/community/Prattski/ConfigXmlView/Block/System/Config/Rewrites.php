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
class Prattski_ConfigXmlView_Block_System_Config_Rewrites extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Rewrites array
     * 
     * @var array 
     */
    protected $_rewrites;
    
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
        $html .= '<h2>Rewrites</h2>';
        $html .= $this->_getRewrites('models');
        $html .= $this->_getRewrites('blocks');
        $html .= $this->_getRewrites('helpers');
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
        $this->_rewrites = $config->getRewrites();
    }
    
    /**
     * Get specific type of rewrite for display
     * 
     * @param string $type
     * @return html 
     */
    protected function _getRewrites($type)
    {
        switch ($type) {
            case "models":
                $html = '<h3>Models</h3>';
                $rewrites = (isset($this->_rewrites['models'])) ? $this->_rewrites['models'] : array();
                break;
            case "blocks":
                $html = '<h3 style="margin-top: 25px">Blocks</h3>';
                $rewrites = (isset($this->_rewrites['blocks'])) ? $this->_rewrites['blocks'] : array();
                break;
            case "helpers":
                $html = '<h3 style="margin-top: 25px">Helpers</h3>';
                $rewrites = (isset($this->_rewrites['helpers'])) ? $this->_rewrites['helpers'] : array();
                break;
            default:
                return '';
        }
        
        // If there are no rewrites, just display "None"
        if (empty($rewrites)) {
            $html .= '<p>None</p>';
            return $html;
        }
        
        // Sort by core class
        ksort($rewrites);
        
        $html .= '<table>';
        $html .= '<thead style="font-weight: bold"><td>Core Class</td><td>Rewrite Class</td><td>Module</td></thead>';
        
        // Loop through each rewrite for display
        foreach ($rewrites as $coreClass => $rewrite) {
                
            /**
             * If there are multiple of the same rewrite, then there is a
             * conflict, and it will be displayed in red.  So, set the style
             * attribute to red. 
             */
            $conflict = (count($rewrite) > 1) ? ' color: red;' : '';
            
            /**
             * We need to loop through the rewrites again because there could be
             * multiple (conflicting).  There should only be one here if there
             * is no conflict.
             */
            foreach ($rewrite as $rewriteInfo) {
                $html .= "<tr>";
                $html .= '<td style="padding: 5px 20px 5px 5px;'.$conflict.'">'.$coreClass.'</td>';
                $html .= '<td style="padding: 5px 20px 5px 5px;'.$conflict.'">'.$rewriteInfo['rewrite_class'].'</td>';
                $html .= '<td style="padding: 5px 20px 5px 5px;">'.$rewriteInfo['module_name'].'</td>';
                $html .= '</tr>';
            }
        }
        
        $html .= '</table>';
        
        return $html;
    }
}   