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

namespace Ecentral\EcStyla\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Redirect implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $contentHubConfiguration = $extensionConfiguration->get('ec_styla', 'rootPath');
        $contentHubPaths = array_map('trim', explode(',', $contentHubConfiguration));

        $path = $request->getUri()->getPath();
        $rootPath = $this->determineRootPath($contentHubPaths, $path);

        if (null !== $rootPath) {
            $uri = $request->getUri();

            if (false === $this->endsWith($path, '/')) {
                return new RedirectResponse($uri->withPath($uri->getPath() . '/'));
            }

            $site = $request->getAttribute('site', null);
            $routing = $request->getAttribute('routing');
            $language = $routing['language'] ?? null;

            $siteRouteResult = new SiteRouteResult($uri, $site, $language, $rootPath);
            $request = $request->withAttribute('routing', $siteRouteResult);
        }

        return $handler->handle($request);
    }

    protected function endsWith($path, $character): bool
    {
        return substr($path, -strlen($character)) === $character;
    }


    protected function determineRootPath($contentHubSegments, $url): ?string
    {
        foreach ($contentHubSegments as $hubSegment) {
            $path = strstr($url, $hubSegment);

            if ($path) return '/' . $hubSegment . '/';
        }

        return null;
    }
}