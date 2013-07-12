<?php

namespace Digitick\Sepa;
use Digitick\Sepa\DomBuilder\DomBuilderInterface;

/**
 * User: s.rohweder@blage.net
 * Date: 7/9/13
 * Time: 11:28 PM
 * License: MIT
 */

class GroupHeader {

    /**
     * Weather this is a test Transaction
     *
     * @var boolean
     */
    protected $isTest;

    /**
     * @var string Unambiguously identify the message.
     */
    protected $messageIdentification;

    /**
     * The initiating Party for this payment
     *
     * @var string
     */
    protected $initiatingPartyId;

    /**
     * @var int
     */
    protected $numberOfTransactions = 0;

    /**
     * @var int
     */
    protected $controlSumCents = 0;

    /**
     * @var string
     */
    protected $initiatingPartyName;

    /**
     * @var \DateTime
     */
    protected $creationDateTime;

    /**
     * @param $messageIdentification
     * @param $isTest
     * @param $initiatingPartyName
     */
    function __construct($messageIdentification, $initiatingPartyName, $isTest = false) {
        $this->messageIdentification = $messageIdentification;
        $this->isTest = $isTest;
        $this->initiatingPartyName = $initiatingPartyName;
        $this->creationDateTime = new \DateTime();
    }

    public function accept(DomBuilderInterface $domBuilder) {
        $domBuilder->visitGroupHeader($this);
    }

    /**
     * @param int $controlSumCents
     */
    public function setControlSumCents($controlSumCents) {
        $this->controlSumCents = $controlSumCents;
    }

    /**
     * @return int
     */
    public function getControlSumCents() {
        return $this->controlSumCents;
    }

    /**
     * @param string $initiatingPartyId
     */
    public function setInitiatingPartyId($initiatingPartyId) {
        $this->initiatingPartyId = $initiatingPartyId;
    }

    /**
     * @return string
     */
    public function getInitiatingPartyId() {
        return $this->initiatingPartyId;
    }

    /**
     * @param string $initiatingPartyName
     */
    public function setInitiatingPartyName($initiatingPartyName) {
        $this->initiatingPartyName = $initiatingPartyName;
    }

    /**
     * @return string
     */
    public function getInitiatingPartyName() {
        return $this->initiatingPartyName;
    }

    /**
     * @param boolean $isTest
     */
    public function setIsTest($isTest) {
        $this->isTest = $isTest;
    }

    /**
     * @return boolean
     */
    public function getIsTest() {
        return $this->isTest;
    }

    /**
     * @param string $messageIdentification
     */
    public function setMessageIdentification($messageIdentification) {
        $this->messageIdentification = $messageIdentification;
    }

    /**
     * @return string
     */
    public function getMessageIdentification() {
        return $this->messageIdentification;
    }

    /**
     * @param int $numberOfTransactions
     */
    public function setNumberOfTransactions($numberOfTransactions) {
        $this->numberOfTransactions = $numberOfTransactions;
    }

    /**
     * @return int
     */
    public function getNumberOfTransactions() {
        return $this->numberOfTransactions;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDateTime() {
        return $this->creationDateTime;
    }


}