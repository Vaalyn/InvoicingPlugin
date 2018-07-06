<?php

declare(strict_types=1);

namespace Sylius\InvoicingPlugin\Ui\Action;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\InvoicingPlugin\Email\InvoiceEmailSenderInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Repository\InvoiceRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ResendInvoiceAction
{
    /** @var InvoiceRepository */
    private $invoiceRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var InvoiceEmailSenderInterface */
    private $invoiceEmailSender;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        InvoiceEmailSenderInterface $invoiceEmailSender,
        OrderRepositoryInterface $orderRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceEmailSender = $invoiceEmailSender;
        $this->orderRepository = $orderRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(Request $request, string $id): Response
    {
        /** @var InvoiceInterface $invoice */
        $invoice = $this->invoiceRepository->get($id);

        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['number' => $invoice->orderNumber()]);

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        $this->invoiceEmailSender->sendInvoiceEmail($invoice, $customer->getEmail());

        return new RedirectResponse($this->urlGenerator->generate('sylius_admin_order_show', ['id' => $order->getId()]));
    }
}
