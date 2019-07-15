<?php
defined('TYPO3_MODE') || die('Access denied.');

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

call_user_func(
    function () {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Ecentral.EcStyla',
            'Teaser',
            [
                'Teaser' => 'show'
            ],
            // non-cacheable actions
            [
                'Teaser' => 'show'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Ecentral.EcStyla',
            'Contenthub',
            [
                'ContentHub' => 'show'
            ],
            // non-cacheable actions
            [
                'ContentHub' => 'show'
            ]
        );

        // wizards
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    teaser {
                        iconIdentifier = ec_styla-plugin-teaser
                        title = Styla Teaser
                        description = Teasers a content hub in different formats
                        tt_content_defValues {
                            CType = list
                            list_type = ecstyla_teaser
                        }
                    }
                    contenthub {
                        iconIdentifier = ec_styla-plugin-contenthub
                        title = LLL:EXT:ec_styla/Resources/Private/Language/locallang_db.xlf:tx_ec_styla_contenthub.name
                        description = LLL:EXT:ec_styla/Resources/Private/Language/locallang_db.xlf:tx_ec_styla_contenthub.description
                        tt_content_defValues {
                            CType = list
                            list_type = ecstyla_contenthub
                        }
                    }
                }
                show = *
            }
       }'
        );
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

        $iconRegistry->registerIcon(
            'ec_styla-plugin-teaser',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:ec_styla/Resources/Public/Icons/contenthub.svg']
        );

        $iconRegistry->registerIcon(
            'ec_styla-plugin-contenthub',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:ec_styla/Resources/Public/Icons/contenthub.svg']
        );

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['ConfigurationReader_postProc'][] = 'Ecentral\\EcStyla\\Hook\\Realurl->configure';

        if (false == is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ec_styla'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ec_styla'] = array();
        }

        if (false == isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ec_styla']['frontend'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ec_styla']['frontend'] = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
        }

        if ('7.6' == TYPO3_branch) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['Ecentral\\EcStyla\\Utility\\StylaRequest'] = array(
                'className' => '\Ecentral\EcStyla\Compatiblity\_7_6\Utility\StylaRequest::class'
            );
        }

    }
);
