<?php

namespace LiveMapEngine\ContentExtra;

class DefaultHandler implements ContentExtraInterface
{

    public function render(string $source_data): mixed
    {
        return $source_data;
    }
}