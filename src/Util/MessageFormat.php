<?php

namespace Digitick\Sepa\Util;

use InvalidArgumentException;

class MessageFormat
{
    public const VALIDATION_REG_EXP = '/^[a-z]{4}\.[0-9]{3}\.[0-9]{3}\.[0-9]{2}$/i';
    /** @var string $messageName */
    private $messageName;
    /** @var string $type */
    private $type;
    /** @var int $subType */
    private $subType;
    /** @var int $variant */
    private $variant;
    /** @var int $version */
    private $version;

    private static $supportedMessageFormats = [
        /* Credit Transfers: */
        'pain.001.001.03',
        'pain.001.001.04',
        'pain.001.001.05',
        'pain.001.001.06',
        'pain.001.001.07',
        'pain.001.001.08',
        'pain.001.001.09',
        'pain.001.001.10',
        'pain.001.001.12',
        /* Variants: */
        'pain.001.002.03', // (STPCreditTransferInitiationV03)
        'pain.001.003.03', // (EUSTPCreditTransferInitiationV03)
        /* Direct Debits: */
        'pain.008.001.02',
        'pain.008.001.03',
        'pain.008.001.04',
        'pain.008.001.05',
        'pain.008.001.06',
        'pain.008.001.07',
        'pain.008.001.08',
        'pain.008.001.09',
        'pain.008.001.10',
        'pain.008.001.11',
        /* Variants: */
        'pain.008.002.02',
        'pain.008.003.02'
    ];

    private static $defaultMessageFormats = [
        'SCT' =>  'pain.001.001.09',
        'SDD' => 'pain.008.001.09'
    ];

    public function __construct(string $messageName)
    {
        $this->parseMessageName($messageName);
    }

    public function getMessageName(): string
    {
        return $this->messageName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubType(): int
    {
        return $this->subType;
    }

    public function getVariant(): int
    {
        return $this->variant;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getSupportedMessageFormats(): array
    {
        return self::$supportedMessageFormats;
    }

    public function isOf(string $messageType, string $messageSubType): bool
    {
        return ($this->type == $messageType && $this->subType == self::filterToInt($messageSubType));
    }

    public function isDirectDebit(): bool
    {
        return $this->isOf('pain', '008');
    }

    public function isCreditTransfer(): bool
    {
        return $this->isOf('pain', '001');
    }

    public function isSupported(?string $messageName = null): bool
    {
        if (null == $messageName) {
            return in_array($this->messageName, self::getSupportedMessageFormats());
        }

        return in_array($messageName, self::getSupportedMessageFormats());
    }

    public static function getDefaultMessageFormats(): array
    {
        return self::$defaultMessageFormats;
    }

    /**
     * @param string $messageName
     * @return void
     */
    private function parseMessageName(string $messageName): void
    {
        if (!preg_match(self::VALIDATION_REG_EXP, $messageName)) {
            throw new InvalidArgumentException(sprintf('Invalid message name "%s"', $messageName));
        }
        $this->messageName = $messageName;

        $msgNm = explode(".", $messageName);
        $this->type    = (string) preg_replace('/[^a-z]+/i', '', $msgNm[0]);
        $this->subType = self::filterToInt($msgNm[1]);
        $this->variant = self::filterToInt($msgNm[2]);
        $this->version = self::filterToInt($msgNm[3]);
    }

    private static function filterToInt(string $input): int
    {
        return (int) preg_replace('/^0+/i', '', $input);
    }
}