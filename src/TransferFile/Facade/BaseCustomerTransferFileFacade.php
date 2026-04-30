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

    /**
     * @var bool Whether the document has been rendered. Guards against
     *           re-flushing payments into the transfer file (which would
     *           double the NbOfTxs / CtrlSum counters on GroupHeader and
     *           duplicate <PmtInf> nodes in the DOM).
     */
    private $rendered = false;

    /**
     * @var string|null Cached XML produced by the first render.
     */
    private $renderedXml;

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

    /**
     * Suppress <CtrlSum> inside <GrpHdr>. Required by the German DK
     * pain.001.001.03 profile.
     */
    public function setOmitGroupHeaderControlSum(bool $omit): void
    {
        $this->domBuilder->setOmitGroupHeaderControlSum($omit);
    }

    /**
     * Omit the <CdtrAgt>/<DbtrAgt> wrapper altogether when the corresponding
     * BIC is missing, instead of emitting <Othr><Id>NOTPROVIDED</Id></Othr>.
     */
    public function setOmitAgentElementIfBicMissing(bool $omit): void
    {
        $this->domBuilder->setOmitAgentElementIfBicMissing($omit);
    }

    public function asXML(): string
    {
        $this->finalize();

        return $this->renderedXml;
    }

    public function asDOC(): DOMDocument
    {
        $this->finalize();

        return $this->domBuilder->asDoc();
    }

    /**
     * Guard used by subclasses to refuse mutation after the document has
     * been rendered. Otherwise the new payments/transfers would be silently
     * ignored by the cached XML.
     *
     * @throws \LogicException when asXML() or asDOC() has already been called.
     */
    protected function ensureNotFinalized(): void
    {
        if ($this->rendered) {
            throw new \LogicException(
                'Cannot modify a facade after asXML() or asDOC() has been called; '
                . 'create a new facade instead.'
            );
        }
    }

    /**
     * Flush queued payments into the transfer file, walk it with the
     * DomBuilder, and cache the result. Safe to call repeatedly — only the
     * first invocation performs work.
     */
    private function finalize(): void
    {
        if ($this->rendered) {
            return;
        }

        foreach ($this->payments as $payment) {
            $this->transferFile->addPaymentInformation($payment);
        }
        $this->transferFile->accept($this->domBuilder);
        $this->renderedXml = $this->domBuilder->asXml();
        $this->rendered = true;
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
