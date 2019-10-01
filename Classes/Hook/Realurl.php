<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 entwicklung@ecentral.de <>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace Ecentral\EcStyla\Hook;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

/**
 * Class Realurl
 * @package Ecentral\EcStyla\Hook
 */
class Realurl implements SingletonInterface {
    /** @var  \TYPO3\CMS\Extbase\Object\ObjectManager */
    protected $objectManager;

    /**
     * @var array
     */
    protected $valuedExtensionConfiguration;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
    }

    /**
     * Set postVarSet_failureMode when a certain uri segment is present
     *
     * @param $parameters
     */
    public function configure($parameters) {

        $uriSegments = array_map('trim', explode(',', $this->getExtensionConfiguration('rootPath')));

        $signalSlotDispatcher = $this->objectManager->get(Dispatcher::class);
        foreach ($uriSegments as $uriSegment) {
            list($uriSegment) = $signalSlotDispatcher->dispatch(__CLASS__, 'beforeCheckingForRootPath', array($uriSegment));

            if ($this->isStylaRequest($uriSegment)) {
                $parameters['configuration']['init']['postVarSet_failureMode'] = 'ignore';
            }
        }
    }

    protected function isStylaRequest($uriSegment)
    {
        $pattern = sprintf('~/%s/~', trim($uriSegment, '/'));
        return (bool)preg_match($pattern, $_SERVER['REQUEST_URI']);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getExtensionConfiguration($key)
    {
        if (!is_array($this->valuedExtensionConfiguration)) {
            /** @var ConfigurationUtility $configurationUtility */
            $configurationUtility = $this->objectManager->get(ConfigurationUtility::class);
            $extensionConfiguration = $configurationUtility->getCurrentConfiguration('ec_styla');
            $this->valuedExtensionConfiguration = $configurationUtility->convertNestedToValuedConfiguration($extensionConfiguration);
        }

        $configKey = sprintf('%s.value', $key);
        if (array_key_exists($configKey, $this->valuedExtensionConfiguration)) {
            return $this->valuedExtensionConfiguration[$configKey]['value'];
        } else {
            return null;
        }
    }
}
