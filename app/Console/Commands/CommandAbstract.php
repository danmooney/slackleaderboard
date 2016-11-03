<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

abstract class CommandAbstract extends Command
{
    public function getSignature($remove_arguments = true)
    {
        if ($remove_arguments) {
            $signature = preg_replace('#^(\w+:\w+).*$#', '$1', $this->signature);
        } else {
            $signature = $this->signature;
        }

        return $signature;
    }
}