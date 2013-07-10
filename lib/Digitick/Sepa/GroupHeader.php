<?php

namespace Digitick\Sepa;

/**
 * User: s.rohweder@blage.net
 * Date: 7/9/13
 * Time: 11:28 PM
 * License: MIT
 */

class GroupHeader extends FileBlock {

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
     * @param $messageIdentification
     * @param $isTest
     * @param $initiatingPartyName
     */
    function __construct($messageIdentification, $initiatingPartyName, $isTest = false) {
        $this->messageIdentification = $messageIdentification;
        $this->isTest = $isTest;
        $this->initiatingPartyName = $initiatingPartyName;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function generateXml(\SimpleXMLElement $parentNode) {

        $datetime = new \DateTime();
        $creationDateTime = $datetime->format('Y-m-d\TH:i:s');

        // -- Group Header -- \\

        $groupHeader = $parentNode->addChild('GrpHdr');

        if ($this->messageIdentification === '' || $this->messageIdentification === null) {
            throw new Exception('Missing messageIdentification in Group Header', 1373406415);
        }
        $groupHeader->addChild('MsgId', $this->messageIdentification);

        $groupHeader->addChild('CreDtTm', $creationDateTime);
        if ($this->isTest) {
            $groupHeader->addChild('Authstn')->addChild('Prtry', 'TEST');
        }

        if ($this->numberOfTransactions === 0) {
            throw new Exception('The transaction count is 0', 1373406515);
        }
        $groupHeader->addChild('NbOfTxs', $this->numberOfTransactions);

        if ($this->controlSumCents === 0) {
            throw new Exception('The control sum is 0', 1373406553);
        }
        $groupHeader->addChild('CtrlSum', $this->intToCurrency($this->controlSumCents));

        if ($this->initiatingPartyName === '' || $this->initiatingPartyName === null) {
            throw new Exception('The initiating party name must be set', 1373406617);
        }
        $groupHeader->addChild('InitgPty')->addChild('Nm', $this->initiatingPartyName);
        if (isset($this->initiatingPartyId)) {
            $groupHeader->addChild('InitgPty')->addChild('Id', $this->initiatingPartyId);
        }

        return $groupHeader;
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

}