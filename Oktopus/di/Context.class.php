<?php
namespace Oktopus;



class ContainerComponentReference
{
}

interface IContainerComponentConfigurator
{
    public function configure ($pObject);
}

interface IContainerDefinition
{
    public function addMethodCall ($pMethodName, array $pParameters = array());
    public function addProperty ($pPropertyName, $pValue);
    public function setShared ($pShared);
    public function setConfigurator (IContainerComponentConfigurator $configurator);
}