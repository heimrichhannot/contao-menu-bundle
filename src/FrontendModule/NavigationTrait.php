<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MenuBundle\FrontendModule;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Database;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\ModuleSitemap;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Routing\Exception\ExceptionInterface;

trait NavigationTrait
{
    protected function renderNavigation($pid, $level = 1, $host = null, $language = null, $module = null)
    {
        if (System::getContainer()->has('HeimrichHannot\EncoreBundle\Asset\FrontendAsset')) {
            System::getContainer()->get('HeimrichHannot\EncoreBundle\Asset\FrontendAsset')->addActiveEntrypoint(
                'contao-menu-bundle'
            );
        }

        // Get all active subpages
        $arrSubpages = static::getPublishedSubpagesByPid($pid, $this->showHidden, $this instanceof ModuleSitemap);

        if ($arrSubpages === null) {
            return '';
        }

        $items              = [];
        $security           = System::getContainer()->get('security.helper');
        $isMember           = $security->isGranted('ROLE_MEMBER');
        $blnShowUnpublished = System::getContainer()->get('contao.security.token_checker')->isPreviewMode();

        $objTemplate         = new FrontendTemplate($this->navigationTpl ?: 'nav_default');

        // TODO: test this
        if (isset($module) && \is_array($module)) {
            $objTemplate->setData($module->arrData);
        }

        $objTemplate->pid    = $pid;
        $objTemplate->type   = static::class;
        $objTemplate->cssID  = $this->cssID; // see #4897
        $objTemplate->level  = 'level_' . $level++;
        $objTemplate->module = $this; // see #155

        $db           = Database::getInstance();
        $urlGenerator = System::getContainer()->get('contao.routing.content_url_generator');

        global $objPage;

        // Browse subpages
        foreach ($arrSubpages as ['page' => $objSubpage, 'hasSubpages' => $blnHasSubpages]) {
            // Skip hidden sitemap pages
            if ($this instanceof ModuleSitemap && $objSubpage->sitemap == 'map_never') {
                continue;
            }

            $objSubpage->loadDetails();

            // Override the domain (see #3765)
            if ($host !== null) {
                $objSubpage->domain = $host;
            }

            // Hide the page if it is only visible to guests
            if ($objSubpage->guests && $isMember) {
                continue;
            }

            $subitems = '';

            // PageModel->groups is an array after calling loadDetails()
            if (!$objSubpage->protected || $this->showProtected || ($this instanceof ModuleSitemap && $objSubpage->sitemap == 'map_always') || $security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $objSubpage->groups)) {
                // Check whether there will be subpages
                if ($blnHasSubpages && (!$this->showLevel || $this->showLevel >= $level || (!$this->hardLimit && ($objPage->id == $objSubpage->id || \in_array($objPage->id, $db->getChildRecords($objSubpage->id, 'tl_page')))))) {
                    $subitems = $this->renderNavigation($objSubpage->id, $level, $host, $language);
                }

                if ($objSubpage->type == 'forward') {
                    if ($objSubpage->jumpTo) {
                        $objNext = PageModel::findPublishedById($objSubpage->jumpTo);
                    } else {
                        $objNext = PageModel::findFirstPublishedRegularByPid($objSubpage->id);
                    }

                    // Hide the link if the target page is invisible
                    if (!$objNext instanceof PageModel || (!$objNext->loadDetails()->isPublic && !$blnShowUnpublished)) {
                        continue;
                    }
                }

                try {
                    $href = $urlGenerator->generate($objSubpage);
                } catch (ExceptionInterface) {
                    continue;
                }

                if (str_starts_with($href, 'mailto:')) {
                    $href = StringUtil::encodeEmail($href);
                }

                $items[] = $this->compileNavigationRow($objPage, $objSubpage, $subitems, $href);
            }
        }

        $objTemplate->items = $items;

        return !empty($items) ? $objTemplate->parse() : '';
    }

    protected function compileNavigationRow(PageModel $objPage, PageModel $objSubpage, $subitems, $href)
    {
        $row   = $objSubpage->row();
        $trail = \in_array($objSubpage->id, $objPage->trail);

        // Use the path without query string to check for active pages (see #480)
        [$path] = explode('?', Environment::get('requestUri'), 2);

        // Active page
        if (($objPage->id == $objSubpage->id || ($objSubpage->type == 'forward' && $objPage->id == $objSubpage->jumpTo)) && !($this instanceof ModuleSitemap) && $href == $path) {
            // Mark active forward pages (see #4822)
            $strClass = (($objSubpage->type == 'forward' && $objPage->id == $objSubpage->jumpTo) ? 'forward' . ($trail ? ' trail' : '') : 'active') . ($subitems ? ' submenu' : '') . ($objSubpage->protected ? ' protected' : '') . ($objSubpage->cssClass ? ' ' . $objSubpage->cssClass : '');

            $row['isActive'] = true;
            $row['isTrail']  = false;
        } // Regular page
        else {
            $strClass = ($subitems ? 'submenu' : '') . ($objSubpage->protected ? ' protected' : '') . ($trail ? ' trail' : '') . ($objSubpage->cssClass ? ' ' . $objSubpage->cssClass : '');

            // Mark pages on the same level (see #2419)
            if ($objSubpage->pid == $objPage->pid) {
                $strClass .= ' sibling';
            }

            $row['isActive'] = false;
            $row['isTrail']  = $trail;
        }

        $row['subitems']    = $subitems;
        $row['class']       = trim($strClass);
        $row['title']       = StringUtil::specialchars($objSubpage->title, true);
        $row['pageTitle']   = StringUtil::specialchars($objSubpage->pageTitle, true);
        $row['link']        = $objSubpage->title;
        $row['href']        = $href;
        $row['rel']         = '';
        $row['target']      = '';
        $row['description'] = str_replace(["\n", "\r"], [' ', ''], (string)$objSubpage->description);

        $arrRel = [];

        // Override the link target
        if ($objSubpage->type == 'redirect' && $objSubpage->target) {
            $arrRel[] = 'noreferrer';
            $arrRel[] = 'noopener';

            $row['target'] = ' target="_blank"';
        }

        // Set the rel attribute
        if (!empty($arrRel)) {
            $row['rel'] = ' rel="' . implode(' ', $arrRel) . '"';
        }

        // Tag the page
        if (System::getContainer()->has('fos_http_cache.http.symfony_response_tagger')) {
            $responseTagger = System::getContainer()->get('fos_http_cache.http.symfony_response_tagger');
            $responseTagger->addTags(['contao.db.tl_page.' . $objSubpage->id]);
        }

        return $row;
    }

    protected static function getPublishedSubpagesByPid($intPid, $blnShowHidden = false, $blnIsSitemap = false): array|null
    {
        $time              = Date::floorToMinute();
        $tokenChecker      = System::getContainer()->get('contao.security.token_checker');
        $blnBeUserLoggedIn = $tokenChecker->isPreviewMode();
        $unroutableTypes   = System::getContainer()->get('contao.routing.page_registry')->getUnroutableTypes();

        $arrPages = Database::getInstance()->prepare("SELECT p1.id, EXISTS(SELECT * FROM tl_page p2 WHERE p2.pid=p1.id AND p2.type!='root' AND p2.type NOT IN ('" . implode("', '",
                $unroutableTypes) . "')" . (!$blnShowHidden ? ($blnIsSitemap ? " AND (p2.hide=0 OR sitemap='map_always')" : " AND p2.hide=0") : "") . (!$blnBeUserLoggedIn ? " AND p2.published=1 AND (p2.start='' OR p2.start<=$time) AND (p2.stop='' OR p2.stop>$time)" : "") . ") AS hasSubpages FROM tl_page p1 WHERE p1.pid=? AND p1.type!='root' AND p1.type NOT IN ('" . implode("', '",
                $unroutableTypes) . "')" . (!$blnShowHidden ? ($blnIsSitemap ? " AND (p1.hide=0 OR sitemap='map_always')" : " AND p1.hide=0") : "") . (!$blnBeUserLoggedIn ? " AND p1.published=1 AND (p1.start='' OR p1.start<=$time) AND (p1.stop='' OR p1.stop>$time)" : "") . " ORDER BY p1.sorting")
            ->execute($intPid)
            ->fetchAllAssoc();

        if (\count($arrPages) < 1) {
            return null;
        }

        // Load models into the registry with a single query
        PageModel::findMultipleByIds(array_column($arrPages, 'id'));

        return array_map(
            static function (array $row): array {
                return [
                    'page'        => PageModel::findById($row['id']),
                    'hasSubpages' => (bool)$row['hasSubpages'],
                ];
            },
            $arrPages
        );
    }
}
