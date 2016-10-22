<?php
// library/AnnotationFinder/Finder/Finder.php

namespace AnnotationFinder\Finder;

interface Finder
{
    public function find(): Finder;

    public function in($path): array;

    public function when($event, $callback);
}