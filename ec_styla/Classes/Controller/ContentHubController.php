<?php
namespace Ecentral\EcStyla\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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

/**
 * Class ContentHubController
 * @package Ecentral\EcStyla\Controller
 */
class ContentHubController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    const API_URI_QUERYSTRING = '%s?url=/%s';

    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cache;

    /**
     * Default lifetime of cached data
     * @var int
     */
    protected $cachePeriod = 3600;

    /** @var  \TYPO3\CMS\Extbase\Object\ObjectManager */
    protected $objectManager;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
    }

    /**
     * action show
     *
     * Add seo relevant elements to html body, either by fetching
     * the data from remote or using the cached data.
     *
     * @return void
     */
    public function showAction()
    {
        $this->cache = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class)->getCache('ec_styla');
        $cacheIdentifier = $this->getCacheIdentifier();
        $cachedContent = $this->cache->get($cacheIdentifier);

        if (false == $cachedContent) {
            $uriWithoutBaseUrl = str_replace(
                $this->getControllerContext()->getRequest()->getBaseUri(),
                '',
                $this->getControllerContext()->getRequest()->getRequestUri()
            );

            $contentHubUrlSegment = str_replace (
                $this->settings['contenthub_segment'],
                '',
                $uriWithoutBaseUrl
            );

            $url = sprintf(
                $this->settings['api_url'] . self::API_URI_QUERYSTRING,
                $this->settings['contenthub']['id'],
                $contentHubUrlSegment
            );

            $content = $this->fetchContentHubSeo($url);

            $this->cacheContent(
                $content,
                array (
                    'styla',
                    $this->settings['contenthub']['id']
                )
            );
       } else {
            $content = $cachedContent;
        }

        $signalSlotDispatcher = $this->objectManager->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'beforeProcessingSeoContent', array('ec_styla', &$content));

        foreach ($content->tags as $item) {
            if ('' != ($headerElement = $this->getHtmlForTagItem($item))) {
                // If Cache-Control is set to no-cache upon request, the page renderer
                // may not add additional meta information for this request. Hence the
                // additional header elements are directly added to the header elements list.
                $GLOBALS['TSFE']->additionalHeaderData[] = $headerElement;
            }
        }

        $this->view->assign('seoHtml', $content->html->body);
    }

    /**
     * Get SEO relevant data from remote
     *
     * @param $url
     * @return mixed|null
     */
    protected function fetchContentHubSeo($url) {
        /** @var \TYPO3\CMS\Core\Http\RequestFactory $requestFactory */
        $requestFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Http\RequestFactory::class);

        $response = $requestFactory->request($url, 'GET');
        if ($response->getStatusCode() === 200) {
            $content = json_decode($response->getBody()->getContents());
            if ((null != $content) &&
                ($content->code === 'success')) {

                if (null != ($cacheControl = $response->getHeader('cache-control'))) {
                    $this->cachePeriod = $cacheControl;
                }

                return $content;
            } else {
                /** @var $logger \TYPO3\CMS\Core\Log\Logger */
                $this->logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);

                $this->logger->error(
                    'Styla api could not deliver the requested content',
                    array(
                        'code' => $content->code,
                    )
                );
            }
        } else {
            /** @var $logger \TYPO3\CMS\Core\Log\Logger */
            $logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);

            $logger->error(
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
     * Cache serializable item
     *
     * @param $item
     * @param array $tags
     * @param int $cachePeriod
     */
    protected function cacheContent($item, $tags = array('styla'), $cachePeriod = 3600) {
        $this->cache->set(
            $this->getCacheIdentifier(),
            $item,
            $tags,
            $cachePeriod
        );
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function getCacheIdentifier() {
        return 'styla-' . $this->settings['contenthub']['id'] . '-'. md5($this->getControllerContext()->getRequest()->getRequestUri());
    }

    /**
     * Return html element for item
     *
     * TODO: Implement generic approach
     *
     * @param $item
     * @return string
     */
    protected function getHtmlForTagItem($item) {
        switch ($item->tag) {
            case 'meta':
                if(null != $item->attributes->name) {
                    return '<meta name="' . $item->attributes->name  . '" content="' . $item->attributes->content . '" />';
                }
                return '<meta property="' .  $item->attributes->property  . '" content="' . $item->attributes->content . '" />';
                break;
            case 'link':
                return '<link rel="' . $item->attributes->rel . '" href="' . $item->attributes->href . '" />';
                break;
            case 'title':
                return '<title>' . $item->content . '</title>';
                break;
            default:
                return '';
        }
    }
}
