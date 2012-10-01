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
                    
                    Mage::log(print_r($configFile, true));
                    
                    /**
                     * Sometimes modules won't have specific nodes that this is
                     * trying to look up.  No need to error out if that happens,
                     * just fail silently.
                     */
                    try {
                        // Get model rewrites
                        $xml = $mergeModel->_xml->global->models->rewrite;
                        if (!empty($xml)) {
                            $modelsRewrites = $xml->asArray();

                            foreach ($modelsRewrites as $orig => $new) {
                                $rewritesArray['models'][$orig][] = $new;
                            }
                        }

                        // Get block rewrites
                        $xml = $mergeModel->_xml->global->blocks->rewrite;
                        if (!empty($xml)) {
                            $modelsRewrites = $xml->asArray();

                            foreach ($modelsRewrites as $orig => $new) {
                                $rewritesArray['blocks'][$orig][] = $new;
                            }
                        }

                        // Get helper rewrites
                        $xml = $mergeModel->_xml->global->helpers->rewrite;
                        if (!empty($xml)) {
                            $modelsRewrites = $xml->asArray();

                            foreach ($modelsRewrites as $orig => $new) {
                                $rewritesArray['helpers'][$orig][] = $new;
                            }
                        }
                    } catch (Exception $e) {
                        /**
                         * Fail silently.  Most likely failing because a module
                         * doesn't have a <global> node. 
                         */
                    }
                }
            }
        }
        
        return $rewritesArray;
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
        
        $observersArray = array();
        
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
                    
                    /**
                     * Sometimes modules won't have specific nodes that this is
                     * trying to look up.  No need to error out if that happens,
                     * just fail silently.
                     */
                    try {
                        $xml = $mergeModel->_xml->global->events;
                        if (!empty($xml)) {
                            $globalObservers = $xml->asArray();

                            // Make sure it's a valid observer
                            if (!empty($globalObservers) && is_array($globalObservers)) {

                                // Loop through each event that is being observed
                                foreach ($globalObservers as $event => $observers) {

                                    // Loop through each specific observer
                                    foreach ($observers['observers'] as $name => $details) {
                                        $observersArray[$event][$name] = $details;
                                        $observersArray[$event][$name]['module'] = $modName;
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        /**
                         * Fail silently.  Most likely failing because a module
                         * doesn't have a <global> node. 
                         */
                    }
                }
            }
        }
        return $observersArray;
    }
}
