<?php

/****************************************************************
 *  Copyright notice
 *
 *  (c) 2015 entwicklung@ecentral.de <entwicklung@ecentral.de>
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


namespace Ecentral\EcStyla\Compatiblity\_7_6\Utility;

use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\HttpRequest;

/**
 * Class StylaRequest
 * @package Ecentral\EcStyla\Compatiblity\_7_6\Utility
 */
class StylaRequest
{
    protected $logger;
    protected $cachePeriod = 0;

    public function get($url)
    {
        /** @var \TYPO3\CMS\Core\Log\Logger $logger */
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        /** @var HttpRequest $request */
        $request = GeneralUtility::makeInstance(HttpRequest::class);

        try {
            $request->setUrl($url);
            $request->setConfig('follow_redirects', true);
            $request->setMethod(\HTTP_Request2::METHOD_GET);

            $response = $request->send();

            if (true == isset($response) && 200 === $response->getStatus()) {
                $content = json_decode($response->getBody());
                if (null !== $content &&
                    strtolower($content->code) === 'success') {
                    $cacheControl = $response->getHeader('cache-control');

                    if (null !== $cacheControl) {
                        $this->cachePeriod = $cacheControl;
                    }

                    return $content;
                } else {
                    $this->logger->error(
                        'Styla api could not deliver requested content',
                        array(
                            'code' => $content->code,
                        )
                    );
                }
            } else {
                throw new \HTTP_Request2_Exception("Unexpected response status: " . $response->getStatus());
            }
        } catch (\HTTP_Request2_Exception $e) {
            $this->logger->error(
                'Exception during communication with styla api',
                array(
                    'url' => $url,
                    'page' => $GLOBALS['TSFE']->id,
                    'message' => $e->getMessage()
                )
            );
        }

        return null;
    }

    /**
     * @return int
     */
    public function getCachePeriod()
    {
        return $this->cachePeriod;
    }
}