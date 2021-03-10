<?php

namespace Ecentral\EcStyla\Controller;

use Ecentral\EcStyla\Utility\StylaRequest;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

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
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var array
     */
    protected $disabledMetaTagsArray = [];

    /**
     * Default lifetime of cached data
     * @var int
     */
    protected $cachePeriod = 3600;

    /** @var ExtensionConfiguration */
    protected $extensionConfiguration;

    /** @var CacheManager */
    protected $cacheManager;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    public function __construct(ExtensionConfiguration $extensionConfiguration, CacheManager $cacheManager)
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->cacheManager = $cacheManager;
        try {
            $this->cache = $cacheManager->getCache('ec_styla');
        } catch (NoSuchCacheException $e) {
            throw new NoSuchCacheException('Styla cache could not be found. Please run the database tool from the maintenance tab inside TYPO3 backend.');
        }
    }

    /**
     * action show
     *
     * Add seo relevant elements to html body, either by fetching
     * the data from remote or using the cached data.
     *
     * @return void
     */
    public function showAction(): void
    {
        $configuration = $this->extensionConfiguration->get('ec_styla');

        $cacheIdentifier = $this->getCacheIdentifier();
        $cachedContent = $this->cache->get($cacheIdentifier);

        if (false == $cachedContent) {
            $path = strtok(str_replace(
                $this->getControllerContext()->getRequest()->getBaseUri(),
                '',
                $this->getControllerContext()->getRequest()->getRequestUri()
            ), '?');

            $url = sprintf(
                $configuration['api_url'] . self::API_URI_QUERYSTRING,
                $this->settings['contenthub']['id'],
                $path
            );

            $request = GeneralUtility::makeInstance(StylaRequest::class);
            $content = $request->get($url);
            if (null !== $content) {
                $this->cachePeriod = $request->getCachePeriod();
                $this->cacheContent(
                    $content,
                    array(
                        'styla',
                        $this->settings['contenthub']['id']
                    )
                );
            }
        } else {
            $content = $cachedContent;
        }

        $signalSlotDispatcher = $this->objectManager->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        list($content) = $signalSlotDispatcher->dispatch(__CLASS__, 'beforeProcessingSeoContent', array($content));

        if (!$content || $content->error) {
            $this->view->assign('seoHtml', '');
            return;
        }

        $this->disabledMetaTagsArray = array_map('trim', explode(',', $configuration['disabled_meta_tags']));

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
     * Cache serializable item
     *
     * @param $item
     * @param array $tags
     * @param int $cachePeriod
     */
    protected function cacheContent($item, $tags = array('styla'), $cachePeriod = 3600): void
    {
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
    protected function getCacheIdentifier(): string
    {
        $path = strtok($this->getControllerContext()->getRequest()->getRequestUri(), '?');

        return 'styla-' . $this->settings['contenthub']['id'] . '-' . md5($path);
    }

    /**
     * Return html element for item
     *
     * @param $item
     * @return string
     */
    protected function getHtmlForTagItem($item): string
    {
        switch ($item->tag) {
            case 'meta':
                if (null != $item->attributes->name && !in_array($item->attributes->name, $this->disabledMetaTagsArray)) {
                    return '<meta name="' . $item->attributes->name . '" content="' . $item->attributes->content . '" />';
                }
                if (!in_array($item->attributes->property, $this->disabledMetaTagsArray)) {
                    return '<meta property="' . $item->attributes->property . '" content="' . $item->attributes->content . '" />';
                }
                return '';
            case 'link':
                $hreflang = isset($item->attributes->hreflang) ? 'hreflang="' . $item->attributes->hreflang . '"' : '';
                return '<link rel="' . $item->attributes->rel . '" href="' . $item->attributes->href . '" ' . $hreflang . '/>';
            case 'title':
                return '<title>' . $item->content . '</title>';
            default:
                return '';
        }
    }
}
