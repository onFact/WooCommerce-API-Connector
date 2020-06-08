<?php


class Onfact_Documents
{

    private static $initiated = false;

    /**
     * Initialize the class
     */
    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    /**
     * Initialize hooks
     *
     * Register onFact events wit hooks to create documents at the correct time
     */
    public static function init_hooks() {
        self::$initiated = true;

        // You can change the webhook to change the moment a document is created
        // add_action( 'woocommerce_order_status_pending', array('Onfact_Documents', 'create_document'), 10, 1);
        // add_action( 'woocommerce_order_status_processing', array('Onfact_Documents', 'create_document'), 10, 1);
        // add_action( 'woocommerce_order_status_on-hold', array('Onfact_Documents', 'create_document'), 10, 1);
        add_action( 'woocommerce_order_status_completed', array('Onfact_Documents', 'create_document'), 10, 1);
        //add_action( 'woocommerce_payment_complete', array('Onfact_Documents', 'create_document'), 10, 1);
    }

    /**
     * Create documents. 3 documents can be created, depending on the configured options
     */
    public static function create_document($order_id) {
        $order = wc_get_order($order_id);

        switch (get_option('document_to_create')) {
            case "Orderslip":
                self::create_orderslip($order);
                break;
            case "Deliveryslip":
                self::create_deliveryslip($order);
                break;
            default:
                self::create_invoice($order);
        }
    }

    /**
     * Create orderslip document
     *
     * @param $order
     */
    private static function create_orderslip($order) {
        try {
            $orderslip = new \OnFact\Model\Orderslip();

            $data = $order->get_data();

            $companiesApi = new \OnFact\Endpoint\Companies();
            $company = $companiesApi->read(0);

            $billing = isset($data['billing']) ? $data['billing'] : [];
            $orderslip->setVattype($order->get_prices_include_tax() ? 'incl' : 'excl');
            $orderslip->setDate($order->get_date_created()->format('Y-m-d'));
            $orderslip->setCurrencyId($order->get_currency());
            $orderslip->setCustomerEid($order->get_customer_id());
            $orderslip->setCustomerStreet(isset($billing['address_1']) ? $billing['address_1'] : '');
            $orderslip->setCustomerName(isset($billing['company']) && !empty($billing['company']) ? $billing['company'] : $billing['first_name'] . ' ' . $billing['last_name']);
            $orderslip->setCustomerCitycode(isset($billing['postcode']) ? $billing['postcode'] : '');
            $orderslip->setCustomerCity(isset($billing['city']) ? $billing['city'] : '');
            $orderslip->setCustomerEmail(isset($billing['email']) ? $billing['email'] : '');
            $orderslip->setCustomerPhone(isset($billing['phone']) ? $billing['phone'] : '');
            $orderslip_lines = [];

            // Change to $order language id depending on installed plugings.
            // Language id not default available in any WooCommece installation.
            try {
                $descriptionsApi = new \OnFact\Endpoint\Descriptions();
                $descriptionId = get_option('orderslip_description_' . $company->getLanguageId());
                $description = $descriptionsApi->read(is_numeric($descriptionId) ? $descriptionId  : 0);
                $orderslip->setText($description->getDescription());
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // Not found, leave empty
            }

            $items = $order->get_items();
            foreach ($items as $i => $item) {
                $lineData = $item->get_data();
                $product = wc_get_product($lineData['product_id']);
                $totalPrice = $orderslip->getVattype() == 'excl' ? $lineData['total'] : $lineData['total'] + $lineData['total_tax'];
                $orderslip_lines[] = [
                    'product_number' => $product->get_sku(),
                    'product_eid' => $product->get_id(),
                    'order ' => $i,
                    'name' => isset($lineData['name']) ? $lineData['name'] : '',
                    'quantity' => isset($lineData['quantity']) ? $lineData['quantity'] : 0,
                    'price' => isset($lineData['total']) && isset($lineData['quantity']) ? round($totalPrice / $lineData['quantity'], 2) : 0,
                    'vat' => isset($lineData['total_tax']) && isset($lineData['total']) ? round($lineData['total_tax'] / $lineData['total'] * 100,2) : 0,
                ];
            }
            $orderslip->setOrderslipLines($orderslip_lines);

            $orderslipsApi = new \OnFact\Endpoint\Orderslips();
            $orderslipsApi->create($orderslip, ['FIND-OR-CREATE-CUSTOMER', 'FIND-PRODUCTS']);
            self::send_orderslip_email($company, $order, $orderslip);
        } catch(\Exception $e) {
            error_log('Orderslip creation failed');
        }
    }

    /**
     * Email the created orderslip
     *
     * @param $company
     * @param $order
     * @param $orderslip
     */
    private static function send_orderslip_email($company, $order, $orderslip)
    {
        try {
            // Change to $order language id depending on installed plugings.
            // Language id not default available in any WooCommece installation.
            $emaildescriptionsApi = new \OnFact\Endpoint\Emaildescriptions();
            $emaildescriptionId = get_option('orderslip_emaildescription_' . $company->getLanguageId());
            $emaildescription = $emaildescriptionsApi->read(is_numeric($emaildescriptionId) ? $emaildescriptionId : 0);

            $email = new \OnFact\Model\Email();
            $email->setTo($orderslip->getCustomerEmail());
            $email->setSubject($emaildescription->getSubject());
            $email->setText($emaildescription->getEmaildescription());
            $email->setPdfAsAttachment(true);
            $email->setXmlAsAttachment(true);

            $orderslipsApi = new \OnFact\Endpoint\Orderslips();
            $orderslipsApi->sendEmail($orderslip, $email);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            error_log('Sending email failed');
        }
    }

    /**
     * Create a deliveryslip document
     *
     * @param $order
     */
    private static function create_deliveryslip($order) {
        try {
            $deliveryslip = new \OnFact\Model\Deliveryslip();

            $data = $order->get_data();

            $companiesApi = new \OnFact\Endpoint\Companies();
            $company = $companiesApi->read(0);

            $billing = isset($data['billing']) ? $data['billing'] : [];
            $deliveryslip->setVattype($order->get_prices_include_tax() ? 'incl' : 'excl');
            $deliveryslip->setDate($order->get_date_created()->format('Y-m-d'));
            $deliveryslip->setCurrencyId($order->get_currency());
            $deliveryslip->setCustomerEid($order->get_customer_id());
            $deliveryslip->setCustomerStreet(isset($billing['address_1']) ? $billing['address_1'] : '');
            $deliveryslip->setCustomerName(isset($billing['company']) && !empty($billing['company']) ? $billing['company'] : $billing['first_name'] . ' ' . $billing['last_name']);
            $deliveryslip->setCustomerCitycode(isset($billing['postcode']) ? $billing['postcode'] : '');
            $deliveryslip->setCustomerCity(isset($billing['city']) ? $billing['city'] : '');
            $deliveryslip->setCustomerEmail(isset($billing['email']) ? $billing['email'] : '');
            $deliveryslip->setCustomerPhone(isset($billing['phone']) ? $billing['phone'] : '');
            $deliveryslip_lines = [];

            // Change to $order language id depending on installed plugings.
            // Language id not default available in any WooCommece installation.
            try {
                $descriptionsApi = new \OnFact\Endpoint\Descriptions();
                $descriptionId = get_option('deliveryslip_description_' . $company->getLanguageId());
                $description = $descriptionsApi->read(is_numeric($descriptionId) ? $descriptionId  : 0);
                $deliveryslip->setText($description->getDescription());
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // Not found, leave empty
            }

            $items = $order->get_items();
            foreach ($items as $i => $item) {
                $lineData = $item->get_data();
                $product = wc_get_product($lineData['product_id']);
                $totalPrice = $deliveryslip->getVattype() == 'excl' ? $lineData['total'] : $lineData['total'] + $lineData['total_tax'];
                $deliveryslip_lines[] = [
                    'product_number' => $product->get_sku(),
                    'product_eid' => $product->get_id(),
                    'order ' => $i,
                    'name' => isset($lineData['name']) ? $lineData['name'] : '',
                    'quantity' => isset($lineData['quantity']) ? $lineData['quantity'] : 0,
                    'price' => isset($lineData['total']) && isset($lineData['quantity']) ? round($totalPrice / $lineData['quantity'], 2) : 0,
                    'vat' => isset($lineData['total_tax']) && isset($lineData['total']) ? round($lineData['total_tax'] / $lineData['total'] * 100,2) : 0,
                ];
            }
            $deliveryslip->setDeliveryslipLines($deliveryslip_lines);

            $deliveryslipsApi = new \OnFact\Endpoint\Deliveryslips();
            $deliveryslipsApi->create($deliveryslip, ['FIND-OR-CREATE-CUSTOMER', 'FIND-PRODUCTS']);
            self::send_deliveryslip_email($company, $order, $deliveryslip);
        } catch(\Exception $e) {
            error_log('Deliveryslip creation failed');
        }
    }

    /**
     * Send the created deliveryslip with email
     *
     * @param $company
     * @param $order
     * @param $deliveryslip
     */
    private static function send_deliveryslip_email($company, $order, $deliveryslip)
    {
        try {
            // Change to $order language id depending on installed plugings.
            // Language id not default available in any WooCommece installation.
            $emaildescriptionsApi = new \OnFact\Endpoint\Emaildescriptions();
            $emaildescriptionId = get_option('deliveryslip_emaildescription_' . $company->getLanguageId());
            $emaildescription = $emaildescriptionsApi->read(is_numeric($emaildescriptionId) ? $emaildescriptionId : 0);

            $email = new \OnFact\Model\Email();
            $email->setTo($deliveryslip->getCustomerEmail());
            $email->setSubject($emaildescription->getSubject());
            $email->setText($emaildescription->getEmaildescription());
            $email->setPdfAsAttachment(true);
            $email->setXmlAsAttachment(true);

            $deliveryslipsApi = new \OnFact\Endpoint\Deliveryslips();
            $deliveryslipsApi->sendEmail($deliveryslip, $email);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            error_log('Sending email failed');
        }
    }

    /**
     * Create Invoice document
     *
     * @param $order
     */
    private static function create_invoice($order) {
        try {
            $invoice = new \OnFact\Model\Invoice();

            $data = $order->get_data();

            $companiesApi = new \OnFact\Endpoint\Companies();
            $company = $companiesApi->read(0);

            $billing = isset($data['billing']) ? $data['billing'] : [];
            $invoice->setVattype($order->get_prices_include_tax() ? 'incl' : 'excl');
            $invoice->setDate($order->get_date_created()->format('Y-m-d'));
            $invoice->setCurrencyId($order->get_currency());
            $invoice->setCustomerEid($order->get_customer_id());
            $invoice->setCustomerStreet(isset($billing['address_1']) ? $billing['address_1'] : '');
            $invoice->setCustomerName(isset($billing['company']) && !empty($billing['company']) ? $billing['company'] : $billing['first_name'] . ' ' . $billing['last_name']);
            $invoice->setCustomerCitycode(isset($billing['postcode']) ? $billing['postcode'] : '');
            $invoice->setCustomerCity(isset($billing['city']) ? $billing['city'] : '');
            $invoice->setCustomerEmail(isset($billing['email']) ? $billing['email'] : '');
            $invoice->setCustomerPhone(isset($billing['phone']) ? $billing['phone'] : '');
            $invoice_lines = [];

            // Change to $order language id depending on installed plugings.
            // Language id not default available in any WooCommece installation.
            try {
                $descriptionsApi = new \OnFact\Endpoint\Descriptions();
                $descriptionId = get_option('invoice_description_' . $company->getLanguageId());
                $description = $descriptionsApi->read(is_numeric($descriptionId) ? $descriptionId  : 0);
                $invoice->setText($description->getDescription());
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // Not found, leave empty
            }

            $items = $order->get_items();
            foreach ($items as $i => $item) {
                $lineData = $item->get_data();
                $product = wc_get_product($lineData['product_id']);
                $totalPrice = $invoice->getVattype() == 'excl' ? $lineData['total'] : $lineData['total'] + $lineData['total_tax'];
                $invoice_lines[] = [
                    'product_number' => $product->get_sku(),
                    'product_eid' => $product->get_id(),
                    'order ' => $i,
                    'name' => isset($lineData['name']) ? $lineData['name'] : '',
                    'quantity' => isset($lineData['quantity']) ? $lineData['quantity'] : 0,
                    'price' => isset($lineData['total']) && isset($lineData['quantity']) ? round($totalPrice / $lineData['quantity'], 2) : 0,
                    'vat' => isset($lineData['total_tax']) && isset($lineData['total']) ? round($lineData['total_tax'] / $lineData['total'] * 100,2) : 0,
                ];
            }
            $invoice->setInvoiceLines($invoice_lines);

            $invoicesApi = new \OnFact\Endpoint\Invoices();
            $invoicesApi->create($invoice, ['FIND-OR-CREATE-CUSTOMER', 'FIND-PRODUCTS']);
            self::send_invoice_email($company, $order, $invoice);
            self::update_paid_status($order, $invoice);
        } catch(\Exception $e) {
            error_log('Invoice creation failed');
        }

    }

    /**
     * Email the created Invoice to the customer
     *
     * @param $company
     * @param $order
     * @param $invoice
     */
    private static function send_invoice_email($company, $order, $invoice)
    {
        try {
            // Change to $order language id depending on installed plugings.
            // Language id not default available in any WooCommece installation.
            $emaildescriptionsApi = new \OnFact\Endpoint\Emaildescriptions();
            $emaildescriptionId = get_option('invoice_emaildescription_' . $company->getLanguageId());
            $emaildescription = $emaildescriptionsApi->read(is_numeric($emaildescriptionId) ? $emaildescriptionId : 0);

            $email = new \OnFact\Model\Email();
            $email->setTo($invoice->getCustomerEmail());
            $email->setSubject($emaildescription->getSubject());
            $email->setText($emaildescription->getEmaildescription());
            $email->setPdfAsAttachment(true);
            $email->setXmlAsAttachment(true);

            $invoicesApi = new \OnFact\Endpoint\Invoices();
            $invoicesApi->sendEmail($invoice, $email);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            error_log('Sending email failed');
        }
    }

    /**
     * If the order was paid, set the Invoice as paid.
     *
     * @param $order
     * @param $invoice
     */
    private static function update_paid_status($order, $invoice) {
        try {
            if ($order->get_date_paid()) {
                $documentEvent = new \OnFact\Model\DocumentEvent();
                $documentEvent->setStatus('paid');

                $invoicesApi = new \OnFact\Endpoint\Invoices();
                $invoicesApi->addDocumentEvent($invoice, $documentEvent);
            }
        } catch(\Exception $e) {
            error_log('Invoice creation failed');
        }
    }


}
