<?php

namespace WebAnvil\Placeholder;

use WebAnvil\Interfaces\ActionInterface;

class Response implements \WebAnvil\Interfaces\ResponseInterface
{
    public function respond(ActionInterface $action, $data = null)
    {
        return '';
    }
}
