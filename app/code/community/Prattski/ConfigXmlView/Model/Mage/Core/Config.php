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
 * System Config Model Extension
 *
 * @category    Prattski
 * @package     Prattski_ConfigXmlView
 */
class Prattski_ConfigXmlView_Model_Mage_Core_Config extends Mage_Core_Model_Config
{
    /**
     * Rewrites array - Used to store all of the rewrites for processing and
     * display.
     * 
     * @var array 
     */
    protected $_rewrites = array();
    
    /**
     * Observers array - Used to store all of the observers for processing and
     * display.
     * 
     * @var array 
     */
    protected $_observers = array();
    
    /**
     * Observer Names array - Used to store all of the observer names as they
     * are being processed to help detect for observer name conflicts.
     * 
     * @var array 
     */
    protected $_observerNames = array();
    
    /**
     * Observer Name Conflicts array - Used to store all the found observer name
     * conflicts for use when displaying the observers to point out the specific
     * conflicts.
     * 
     * @var array 
     */
    protected $_observerNameConflicts = array();
    
    /**
     * Get all the needed rewrites from all the config.xml files
     * 
     * @return array 
     */
    public function getRewrites()
    {
        $disableLocalModules = !$this->_canUseLocalModules();

        $mergeModel = clone $this->_prototype;
        
        $rewritesArray = array();
        
        $modules = $this->getNode('modules')->children();
        foreach ($modules as $modName=>$module) {
            if ($module->is('active')) {
                if ($disableLocalModules && ('local' === (string)$module->codePool)) {
                    continue;
                }

                $configFile = $this->getModuleDir('etc', $modName).DS.'config.xml';
                if ($mergeModel->loadFile($configFile)) {
                    
                    // Get all model rewrites
                    $rewrites = $mergeModel->getNode('global/models');
                    if ($rewrites) {
                        $this->_populateRewriteArray($rewrites, $modName, 'models');
                    }
                    
                    // Get all block rewrites
                    $rewrites = $mergeModel->getNode('global/blocks');
                    if ($rewrites) {
                        $this->_populateRewriteArray($rewrites, $modName, 'blocks');
                    }
                    
                    // Get all helper rewrites
                    $rewrites = $mergeModel->getNode('global/helpers');
                    if ($rewrites) {
                        $this->_populateRewriteArray($rewrites, $modName, 'helpers');
                    }
                }
            }
        }
        
        return $this->_rewrites;
    }
    
    /**
     * Abstracted method to process and populate the rewrites array for the
     * different types of rewrites.
     * 
     * @param Mage_Core_Model_Config_Element $rewrites
     * @param string $type 
     */
    protected function _populateRewriteArray(Mage_Core_Model_Config_Element $rewrites, $modName, $type)
    {
        // Convert the rewrites object to an array for easier processing
        $rewrites = $rewrites->asArray();
        
        // Loop through each rewrite to get the original and new classes
        foreach ($rewrites as $module => $nodes) {
            
            // Check to see if there are any rewrites for the current core module
            if (isset($nodes['rewrite'])) {
                
                // Loop through each rewrite and store it
                foreach ($nodes['rewrite'] as $classSuffix => $rewrite) {
                    $rewriteInfo = array(
                        'module_name' => $modName,
                        'rewrite_class' => $rewrite
                    );
                    $this->_rewrites[$type][$module.'_'.$classSuffix][] = $rewriteInfo;
                }
            }
        }
    }
    
    /**
     * Get all the needed event observers from all the config.xml files
     * 
     * @return array 
     */
    public function getObservers()
    {
        $disableLocalModules = !$this->_canUseLocalModules();

        $mergeModel = clone $this->_prototype;
        
        $modules = $this->getNode('modules')->children();
        foreach ($modules as $modName=>$module) {
            
            // Skip over all Magento Core observers
            if ((string)$module->codePool == 'core') {
                continue;
            }
            
            if ($module->is('active')) {
                if ($disableLocalModules && ('local' === (string)$module->codePool)) {
                    continue;
                }

                $configFile = $this->getModuleDir('etc', $modName).DS.'config.xml';
                if ($mergeModel->loadFile($configFile)) {
                    
                    // Get all global scope events
                    $events = $mergeModel->getNode('global/events');
                    if ($events) {
                        $this->_populateObserverArrays($events, $modName, 'global');
                    }
                    
                    // Get all frontend scope events
                    $events = $mergeModel->getNode('frontend/events');
                    if ($events) {
                        $this->_populateObserverArrays($events, $modName, 'frontend');
                    }
                    
                    // Get all admin scope events
                    $events = $mergeModel->getNode('admin/events');
                    if ($events) {
                        $this->_populateObserverArrays($events, $modName, 'admin');
                    }
                }
            }
        }
        
        return $this->_observers;
    }
    
    /**
     * Abstracted method to populate the different observer arrays used in
     * getting all of the observers, and finding any conflicts since there are
     * multiple scopes that need to be processed.
     * 
     * @param Mage_Core_Model_Config_Element $events
     * @param string $modName
     * @param string $scope 
     */
    protected function _populateObserverArrays(Mage_Core_Model_Config_Element $events, $modName, $scope)
    {
        // Get all of the events
        $events = $events->children();
        
        // Loop through each event to get all the observers
        foreach ($events as $event => $observers) {

            // Convert the observers object to an array for easier processing
            $observers = $observers->asArray();

            if (!empty($observers) && is_array($observers)) {
                
                // Loop through each specific observer and populate needed data
                foreach ($observers['observers'] as $name => $details) {
                    $this->_observers[$event][$name] = $details;
                    $this->_observers[$event][$name]['scope'] = $scope;
                    $this->_observers[$event][$name]['module'] = $modName;

                    /**
                        * The $eventNames array is used to keep
                        * track of all of the event names to be
                        * able to catch event name conflicts and
                        * display them.
                        * 
                        * If the name has not yet been set, store
                        * it, otherwise there is a conflict.
                        */
                    if (!isset($this->_observerNames[$name])) {
                        $this->_observerNames[$name] = $modName;
                    } else {

                        /**
                            * If this is the first time a conflict
                            * has been identified, make sure to
                            * store the first one in the array too
                            * so both are in the list. 
                            */
                        if (!isset($this->_observerNameConflicts[$name])) {
                            $this->_observerNameConflicts[$name][] = $this->_observerNames[$name];
                        }

                        // Set the current event in the conflict list
                        $this->_observerNameConflicts[$name][] = $modName;
                    }
                }
            }
        }
    }
    
    /**
     * Return the observer name conflicts array
     * 
     * @return array 
     */
    public function getObserverNameConflicts()
    {
        return $this->_observerNameConflicts;
    }
}
