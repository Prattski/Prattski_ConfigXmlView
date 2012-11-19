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
        
        $html = '<h2>Rewrites</h2>';
        $html .= $this->_getRewrites('models');
        $html .= $this->_getRewrites('blocks');
        $html .= $this->_getRewrites('helpers');

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
        
        // Get the table header html
        $html .= $this->_renderTableHeader();
        
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
                $html .= '<td style="'.$conflict.'">'.$coreClass.'</td>';
                $html .= '<td style="'.$conflict.'">'.$rewriteInfo['rewrite_class'].'</td>';
                $html .= '<td>'.$rewriteInfo['module_name'].'</td>';
                $html .= '</tr>';
            }
        }
        
        // Get the table footer html
        $html .= $this->_renderTableFooter();
        
        return $html;
    }
    
    /**
     * Method to create the table headings for the 3 different tables used to
     * ouput the rewrite lists.  This utilizes Magento's already existing table
     * styles
     * 
     * @return html 
     */
    protected function _renderTableHeader()
    {
        $html = '';
        $html .= '<div class="grid">';
        $html .= '<div class="hor-scroll">';
        $html .= '<table cellspacing="0" class="data">';
        $html .= '<colgroup><col><col><col></colgroup>';
        $html .= '<thead><tr class="headings">';
        $html .= '<th><span class="nobr">Core Class</span></th>';
        $html .= '<th><span class="nobr">Rewrite Class</span></th>';
        $html .= '<th class="last"><span class="nobr">Module</span></th>';
        $html .= '</tr></thead>';
        
        return $html;
    }
    
    /**
     * Method to create the table footer for the 3 different tables used to
     * ouput the rewrite lists
     * 
     * @return string 
     */
    protected function _renderTableFooter()
    {
        $html = '';
        $html .= '</table>';
        $html .= '</div></div>';
        
        return $html;
    }
}   