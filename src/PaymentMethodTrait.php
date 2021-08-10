<?php declare(strict_types=1);

namespace Digitick\Sepa;

use Digitick\Sepa\Exception\InvalidArgumentException;

trait PaymentMethodTrait
{
    /**
     * @var string|null Payment method.
     */
    protected $paymentMethod = null;

    /**
     * Valid Payment Methods set by the TransferFile
     *
     * @var string[]
     */
    protected $validPaymentMethods = [];

    /**
     * @param string[] $validPaymentMethods
     */
    public function setValidPaymentMethods(array $validPaymentMethods): void
    {
        $this->validPaymentMethods = $validPaymentMethods;
    }

    /**
     * @return string[] $validPaymentMethods
     */
    public function getValidPaymentMethods(): array
    {
        return $this->validPaymentMethods;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setPaymentMethod(string $method): void
    {
        $method = strtoupper($method);
        if (!in_array($method, $this->validPaymentMethods)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid Payment Method: %s, must be one of %s',
                $method,
                implode(',', $this->validPaymentMethods)
            ));
        }
        $this->paymentMethod = $method;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }
}
