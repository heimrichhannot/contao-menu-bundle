<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 * @package contao-menu-bundle
 * @author David Skorupko <d.skorupko@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\MenuBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use HeimrichHannot\MenuBundle\HeimrichHannotContaoMenuBundle;

class EncoreExtension implements EncoreExtensionInterface
{
    public function getBundle(): string
    {
        return HeimrichHannotContaoMenuBundle::class;
    }

    public function getEntries(): array
    {
        return [
            EncoreEntry::create('contao-menu-bundle', 'src/Resources/assets/js/contao-menu-bundle-init.js')
                ->setRequiresCss(true)
                ->addCssEntryToRemoveFromGlobals('contao-menu-bundle')
                ->addJsEntryToRemoveFromGlobals('contao-menu-bundle'),
        ];
    }
}

