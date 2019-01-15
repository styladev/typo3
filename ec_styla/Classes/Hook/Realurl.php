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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Realurl
 * @package Ecentral\EcStyla\Hook
 */
class Realurl {
    /** @var  \TYPO3\CMS\Extbase\Object\ObjectManager */
    protected $objectManager;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
    }

    /**
     * Set postVarSet_failureMode when a certain uri segment is present
     *
     * @param $parameters
     */
    public function configure($parameters) {

        $configuration = $this->getExtensionConfiguration('ec_styla');

        if ((null != $configuration['contenthub_segment']) &&
            ('' != $configuration['contenthub_segment'])) {
            $uriSegment = $configuration['contenthub_segment'];
        } else {
            $uriSegment = 'magazine';
        }

        $uriSegment = '/' . $uriSegment . '\//';

        $signalSlotDispatcher = $this->objectManager->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'beforeCheckingForContenthubSegment', array('ec_styla', &$uriSegment));

        if (preg_match($uriSegment, $_SERVER['REQUEST_URI']) ) {
            $parameters['configuration']['init']['postVarSet_failureMode'] = 'ignore';
        }
    }

    /**
     * Returns the settings section of the given extension
     *
     * @param $name
     * @return mixed
     */
    protected function getExtensionConfiguration($name) {
        $configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $setup = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        return $setup['plugin.']['tx_' . $name .'.']['settings.'];
    }
}