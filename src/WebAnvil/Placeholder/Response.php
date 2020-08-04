<?php

namespace WebAnvil\Placeholder;

class Response implements \WebAnvil\Interfaces\ResponseInterface
{
    /**
     * @param \WebAnvil\Interfaces\ActionInterface $action
     * @param array|null $data
     * @return mixed
     */
    public function respond($action, $data = null)
    {
        return '';
    }
}
