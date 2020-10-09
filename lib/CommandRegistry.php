<?php

namespace Minicli;

class CommandRegistry
{
    protected $commands_path;

    protected $namespaces = [];

    protected $default_registry = [];

    public function __construct($commands_path)
    {
        $this->commands_path = $commands_path;
        $this->autoloadNamespaces();
    }

    public function autoloadNamespaces()
    {
        foreach (glob($this->getCommandsPath() . '/*', GLOB_ONLYDIR) as $namespace_path) {
            $this->registerNamespace(basename($namespace_path));
        }
    }

    public function registerNamespace($command_namespace)
    {
        $namespace = new CommandNamespace($command_namespace);
        $namespace->loadControllers($this->getCommandsPath());
        $this->namespaces[strtolower($command_namespace)] = $namespace;
    }

    public function getNamespace($command)
    {
        return isset($this->namespaces[$command]) ? $this->namespaces[$command] : null;
    }

    public function getCommandsPath()
    {
        return $this->commands_path;
    }

    public function registerCommand($name, $callable)
    {
        $this->default_registry[$name] = $callable;
    }

    public function getCommand($command)
    {
        return isset($this->default_registry[$command]) ? $this->default_registry[$command] : null;
    }

    public function getCallableController($command, $subcommand = null)
    {
        $namespace = $this->getNamespace($command);

        if ($namespace !== null) {
            return $namespace->getController($subcommand);
        }

        return null;
    }

    public function getCallable($command)
    {
        $single_command = $this->getCommand($command);
        if ($single_command === null) {
            throw new \Exception(sprintf("Command \"%s\" not found.", $command));
        }

        return $single_command;
    }

}