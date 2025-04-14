<?php

namespace HeimrichHannot\MenuBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoMenuBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
