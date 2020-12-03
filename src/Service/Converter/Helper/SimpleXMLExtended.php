<?php

namespace App\Service\Converter\Helper;

use SimpleXMLElement;

class SimpleXMLExtended extends SimpleXMLElement
{
    public function addCData($cdata_text): void
    {
        $node = dom_import_simplexml($this);
        $no   = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }
}