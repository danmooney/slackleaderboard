<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

abstract class CommandAbstract extends Command
{
    public function getSignature()
    {
        return $this->signature;
    }
}