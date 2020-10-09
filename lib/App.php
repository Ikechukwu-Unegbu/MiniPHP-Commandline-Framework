<?php

namespace Minicli;

class App
{
    protected $printer;

    protected $command_registry;

    protected $app_signature;

    public function __construct()
    {
        $this->printer = new CliPrinter();
        $this->command_registry = new CommandRegistry(__DIR__ . '/../app/Command');
    }

    public function getPrinter()
    {
        return $this->printer;
    }

    public function getSignature()
    {
        return $this->app_signature;
    }

    public function printSignature()
    {
        $this->getPrinter()->display(sprintf("usage: %s", $this->getSignature()));
    }

    public function setSignature($app_signature)
    {
        $this->app_signature = $app_signature;
    }


    public function registerCommand($name, $callable)
    {
        $this->command_registry->registerCommand($name, $callable);
    }

    public function runCommand(array $argv = [])
    {
        $input = new CommandCall($argv);

        if (count($input->args) < 2) {
            $this->printSignature();
            exit;
        }

        $controller = $this->command_registry->getCallableController($input->command, $input->subcommand);

        if ($controller instanceof CommandController) {
            $controller->boot($this);
            $controller->run($input);
            $controller->teardown();
            exit;
        }

        $this->runSingle($input);
    }

    protected function runSingle(CommandCall $input)
    {
        try {
            $callable = $this->command_registry->getCallable($input->command);
            call_user_func($callable, $input);
        } catch (\Exception $e) {
            $this->getPrinter()->display("ERROR: " . $e->getMessage());
            $this->printSignature();
            exit;
        }
    }

}