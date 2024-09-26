<?php

namespace LiveMapEngine;

interface DataCollectionInterface
{
    public function __construct(string $data = null, callable $parser = null);
    public function import(mixed $data = null):self;

    public function setParser(callable $parser):self;
    public function setIsAssociative(bool $is_associative = true):self;
    public function setSeparator(string $separator = '->'):self;
    public function setDefault(mixed $default = ''):self;

    public function parse($source = null):mixed;

    public function getData(?string $path = '', mixed $default = null, ?string $separator = null, mixed $casting = null):mixed;
    public function setData(string $path = '', mixed $value = null, ?string $separator = null): bool;
    public function hasKey(string $path, ?string $separator = null):bool;


}