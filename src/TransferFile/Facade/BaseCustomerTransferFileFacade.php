<?php
/**
 * SEPA file generator.
 *
 * @copyright © Digitick <www.digitick.net> 2012-2013
 * @copyright © Blage <www.blage.net> 2013
 * @license GNU Lesser General Public License v3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Lesser Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Digitick\Sepa\TransferFile\Facade;

use \DOMDocument;
use Exception;
use DateTimeImmutable;
use DateTimeInterface;
use Digitick\Sepa\DomBuilder\BaseDomBuilder;
use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\TransferFileInterface;

abstract class BaseCustomerTransferFileFacade implements CustomerTransferFileFacadeInterface
{
    /**
     * @var TransferFileInterface
     */
    protected $transferFile;

    /**
     * @var BaseDomBuilder
     */
    protected $domBuilder;

    /**
     * @var array
     */
    protected $payments = [];

    public function __construct(TransferFileInterface $transferFile, BaseDomBuilder $domBuilder)
    {
        $this->transferFile = $transferFile;
        $this->domBuilder = $domBuilder;
    }

    /**
     * Return the payment info with the name passed by $paymentName.
     */
    public function getPaymentInfo(string $paymentName): ?PaymentInformation
    {
        return $this->payments[$paymentName] ?? null;
    }

    public function asXML(): string
    {
        foreach ($this->payments as $payment) {
            $this->transferFile->addPaymentInformation($payment);
        }
        $this->transferFile->accept($this->domBuilder);

        return $this->domBuilder->asXml();
    }

    public function asDOC(): DOMDocument
    {
        foreach ($this->payments as $payment) {
            $this->transferFile->addPaymentInformation($payment);
        }
        $this->transferFile->accept($this->domBuilder);

        return $this->domBuilder->asDoc();
    }

    /**
     * @param array{
     *     dueDate?: string|DateTimeInterface
     * } $paymentInformation
     * @param string $timestamp
     * @return DateTimeInterface
     * @throws InvalidArgumentException if the dueDate is not valid
     * @throws Exception
     */
    public function createDueDateFromPaymentInformation(array $paymentInformation, string $timestamp = 'now'): DateTimeInterface
    {
        if (isset($paymentInformation['dueDate'])) {
            if ($paymentInformation['dueDate'] instanceof DateTimeInterface) {
                return $paymentInformation['dueDate'];
            }

            try {
                return new DateTimeImmutable($paymentInformation['dueDate']);
            } catch (Exception $exception) {
                throw new InvalidArgumentException('Invalid due date', 0, $exception);
            }
        }

        return new DateTimeImmutable(date('Y-m-d', strtotime($timestamp)));
    }
}
