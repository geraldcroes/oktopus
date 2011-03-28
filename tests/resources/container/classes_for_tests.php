<?php
namespace Oktopus\ContainerTest;

class Fruit
{
}

class Apple extends Fruit
{
}

interface ITools
{
}

class Tools implements ITools
{
}

class Peeler extends Tools
{
}

class Juicer
{
    private $_foo;

    private $_fruit;
    private $_tool;
}