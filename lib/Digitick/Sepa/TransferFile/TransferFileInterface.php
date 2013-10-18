<?php

namespace Digitick\Sepa\TransferFile;
use Digitick\Sepa\DomBuilder\DomBuilderInterface;
use Digitick\Sepa\GroupHeader;

/**
 * User: s.rohweder@blage.net
 * Date: 7/9/13
 * Time: 11:06 PM
 * License: MIT
 */


interface TransferFileInterface {


    public function __construct(GroupHeader $groupHeader);

    /**
     * @return GroupHeader
     */
    public function getGroupHeader();

    /**
     * Validate the transferfile
     * @return mixed
     */
    public function validate();

    public function accept(DomBuilderInterface $domBuilder);

}