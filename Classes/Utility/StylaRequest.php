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

namespace Ecentral\EcStyla\Utility;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * Class StylaRequest
 * @package Ecentral\EcStyla\Utility
 */
class StylaRequest implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $cachePeriod = 0;

    public function get($url)
    {
        /** @var RequestFactory $requestFactory */
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);

        $response = $requestFactory->request($url, 'GET', array('allow_redirects' => true));
        if ($response->getStatusCode() === 200) {
            $content = json_decode($response->getBody()->getContents());
            if (null !== $content &&
                strtolower($content->code) === 'success') {

                if (null != ($cacheControl = $response->getHeader('cache-control'))) {
                    $this->cachePeriod = $cacheControl;
                }

                return $content;
            } else {
                $this->logger->error(
                    'Styla api could not deliver the requested content',
                    array(
                        'code' => $content->code,
                    )
                );
            }
        } else {
            $this->logger->error(
                'Error during communication with styla api',
                array(
                    'url' => $url,
                    'page' => $GLOBALS['TSFE']->id,
                    'status' => $response->getStatusCode()
                )
            );

            return null;
        }
    }

    /**
     * Return cache period
     *
     * @return int
     */
    public function getCachePeriod()
    {
        return $this->cachePeriod;
    }
}