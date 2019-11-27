<?php

namespace WebAnvil\Interfaces;

interface ResponseInterface
{
    public function respond(ActionInterface $action, $data = null);
}
