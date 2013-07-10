<?php

namespace Digitick\Sepa;

/**
 * User: s.rohweder@blage.net
 * Date: 7/9/13
 * Time: 11:06 PM
 * License: MIT
 */


interface TransferFile {

    public function asXML();

    /**
     * @return GroupHeader
     */
    public function getGroupHeader();
}