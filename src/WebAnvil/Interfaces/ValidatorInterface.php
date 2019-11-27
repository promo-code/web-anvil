<?php

namespace WebAnvil\Interfaces;

interface ValidatorInterface
{
    public function validate($data, $rules);
}
