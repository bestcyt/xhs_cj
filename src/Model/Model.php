<?php

namespace Mt\Model;

class Model
{
    protected function getMainDbGroup()
    {
        return 'db/main';
    }

    protected function getScriptDbGroup()
    {
        return 'db/script';
    }
}