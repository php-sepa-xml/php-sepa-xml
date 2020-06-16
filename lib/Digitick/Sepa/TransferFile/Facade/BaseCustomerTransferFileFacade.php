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

use Digitick\Sepa\DomBuilder\BaseDomBuilder;
use Digitick\Sepa\TransferFile\TransferFileInterface;

abstract class BaseCustomerTransferFileFacade implements CustomerTransferFileFacadeInterface
{
    /**
     * @var TransferFileInterface
     */
    protected $transferFile;

    /**
     * @var \Digitick\Sepa\DomBuilder\BaseDomBuilder
     */
    protected $domBuilder;

    /**
     * @var array
     */
    protected $payments = array();

    /**
     * @param TransferFileInterface $transferFile
     * @param BaseDomBuilder $domBuilder
     */
    public function __construct(TransferFileInterface $transferFile, BaseDomBuilder $domBuilder)
    {
        $this->transferFile = $transferFile;
        $this->domBuilder = $domBuilder;
    }

    /**
     * @return string
     */
    public function asXML()
    {
        foreach ($this->payments as $payment) {
            $this->transferFile->addPaymentInformation($payment);
        }
        $this->transferFile->accept($this->domBuilder);

        return $this->domBuilder->asXml();
    }

    /**
     * @return DateTime
     */
    public function createDueDateFromPaymentInformation(array $paymentInformation, $default = 'now')
    {
        if (isset($paymentInformation['dueDate'])) {
            if ($paymentInformation['dueDate'] instanceof \DateTime) {
                return $paymentInformation['dueDate'];
            } else {
                return new \DateTime($paymentInformation['dueDate']);
            }
        } else {
            return new \DateTime(date('Y-m-d', strtotime($default)));
        }
    }
}
