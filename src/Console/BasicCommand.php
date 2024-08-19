<?php

namespace OkamiChen\Database\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BasicCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $this->doRun();
        } catch (\Exception $e) {
            $message = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'options' => $this->options(),
                'arguments' => $this->arguments(),
            ];
            logger()->error(static::class, $message);
            throw $e;
        }
    }

    protected function doRun(): void
    {

    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        $addons = [];
        if (method_exists($this, 'getSubArguments')) {
            $addons = $this->getSubArguments();
        }
        $default = [
            ['example', InputArgument::OPTIONAL, 'An example argument.'],
        ];
        return array_merge($default, $addons);
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        $addons = [];
        if (method_exists($this, 'getSubOptions')) {
            $addons = $this->getSubOptions();
        }
        $default = [
            ['id', null, InputOption::VALUE_OPTIONAL, 'The Pk Value.', null],
        ];

        return array_merge($default, $addons);
    }
}
