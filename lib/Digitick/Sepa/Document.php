<?php

namespace Digitick\Sepa;

/**
 * User: s.rohweder@blage.net
 * Date: 7/10/13
 * Time: 11:13 PM
 * License: MIT
 */

abstract class Document extends FileBlock {

    /**
     * @var GroupHeader
     */
    protected $groupHeader;

    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    const INITIAL_STRING = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:%s"></Document>';

    public function __construct(GroupHeader $groupHeader, $painFormat) {
        $this->groupHeader = $groupHeader;
        $this->xml = simplexml_load_string(sprintf(self::INITIAL_STRING, $painFormat));

    }

    /**
     * @return \Digitick\Sepa\GroupHeader
     */
    public function getGroupHeader() {
        return $this->groupHeader;
    }
}