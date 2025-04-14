<?php

namespace HeimrichHannot\MenuBundle\EventListener\Contao;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class LoadDataContainerListener {
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ScopeMatcher
     */
    protected $scopeMatcher;

    public function __construct(RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

    public function __invoke($table)
    {
        if ($this->requestStack->getCurrentRequest() !== null && $this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest())) {
            $GLOBALS['TL_JAVASCRIPT']['contao-menu-bundle'] = 'bundles/heimrichhannotcontaomenu/contao-menu-bundle.js';
            $GLOBALS['TL_CSS']['contao-menu-bundle'] = 'bundles/heimrichhannotcontaomenu/contao-menu-bundle.css';
        }
    }
}
