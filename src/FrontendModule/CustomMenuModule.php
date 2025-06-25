<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MenuBundle\FrontendModule;

use Contao\BackendTemplate;
use Contao\ModuleCustomnav;
use Contao\StringUtil;
use Contao\System;

class CustomMenuModule extends ModuleCustomnav
{
    const TYPE = 'huh_custom_menu';
    protected $strTemplate = 'mod_huh_custom_menu';

    public function generate()
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $objTemplate           = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['FMD'][$this->type][0] . ' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = StringUtil::specialcharsUrl(System::getContainer()->get('router')->generate('contao_backend', ['do' => 'themes', 'table' => 'tl_module', 'act' => 'edit', 'id' => $this->id]));

            return $objTemplate->parse();
        }

        $strBuffer = parent::generate();

        return $this->Template->items ? $strBuffer : '';
    }

    protected function compile()
    {
        parent::compile();
    }
}
