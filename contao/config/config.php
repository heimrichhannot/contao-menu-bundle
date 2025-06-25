<?php

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['navigationMenu'][\HeimrichHannot\MenuBundle\FrontendModule\MenuModule::TYPE] = \HeimrichHannot\MenuBundle\FrontendModule\MenuModule::class;
$GLOBALS['FE_MOD']['navigationMenu'][\HeimrichHannot\MenuBundle\FrontendModule\CustomMenuModule::TYPE] = \HeimrichHannot\MenuBundle\FrontendModule\CustomMenuModule::class;

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [\HeimrichHannot\MenuBundle\EventListener\Contao\LoadDataContainerListener::class, '__invoke'];
