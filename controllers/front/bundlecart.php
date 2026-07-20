<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class DfcollectionBundlecartModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $ajax = true;

    public function postProcess()
    {
        parent::postProcess();

        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!$this->context->cart || !Validate::isLoadedObject($this->context->cart)) {
                $this->context->cart = new Cart();
                $this->context->cart->id_currency = (int)$this->context->currency->id;
                $this->context->cart->id_lang = (int)$this->context->language->id;
                $this->context->cart->id_shop_group = (int)$this->context->shop->id_shop_group;
                $this->context->cart->id_shop = (int)$this->context->shop->id;
                $this->context->cart->id_customer = $this->context->customer ? (int)$this->context->customer->id : 0;
                $this->context->cart->id_address_delivery = $this->context->customer ? (int)$this->context->customer->id_address_delivery : 0;
                $this->context->cart->id_address_invoice = $this->context->customer ? (int)$this->context->customer->id_address_invoice : 0;

                if (!$this->context->cart->add()) {
                    throw new Exception('Nie udało się utworzyć koszyka.');
                }

                $this->context->cookie->id_cart = (int)$this->context->cart->id;
                $this->context->cookie->write();
            }

            $bundleItems = Tools::getValue('bundle_items', []);

            if (is_string($bundleItems)) {
                $decoded = json_decode($bundleItems, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $bundleItems = $decoded;
                }
            }

            if (!is_array($bundleItems) || empty($bundleItems)) {
                throw new Exception('Brak produktów do dodania.');
            }

            $addedProducts = [];
            $errors = [];

            foreach ($bundleItems as $item) {
                $idProduct = 0;
                $qty = 1;
                $idProductAttribute = 0;

                if (is_array($item)) {
                    $idProduct = isset($item['id_product']) ? (int)$item['id_product'] : 0;
                    $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
                    $idProductAttribute = isset($item['id_product_attribute']) ? (int)$item['id_product_attribute'] : 0;
                } else {
                    $idProduct = (int)$item;
                }

                if ($idProduct <= 0) {
                    continue;
                }

                if ($qty <= 0) {
                    $qty = 1;
                }

                $product = new Product($idProduct, false, (int)$this->context->language->id, (int)$this->context->shop->id);

                if (!Validate::isLoadedObject($product)) {
                    $errors[] = 'Nie znaleziono produktu ID '.$idProduct.'.';
                    continue;
                }

                if (!(bool)$product->active) {
                    $errors[] = 'Produkt ID '.$idProduct.' jest nieaktywny.';
                    continue;
                }

                $updateResult = $this->context->cart->updateQty(
                    (int)$qty,
                    (int)$idProduct,
                    (int)$idProductAttribute,
                    false,
                    'up',
                    0,
                    null,
                    true
                );

                if ($updateResult === false || $updateResult < 0) {
                    $errors[] = 'Nie udało się dodać produktu ID '.$idProduct.' do koszyka.';
                    continue;
                }

                $addedProducts[] = [
                    'id_product' => (int)$idProduct,
                    'qty' => (int)$qty,
                    'id_product_attribute' => (int)$idProductAttribute,
                ];
            }

            if (empty($addedProducts)) {
                throw new Exception(!empty($errors) ? implode(' ', $errors) : 'Nie udało się dodać żadnego produktu.');
            }

            CartRule::autoRemoveFromCart($this->context);
            CartRule::autoAddToCart($this->context);

            $this->context->cart->update();

            $this->context->cookie->id_cart = (int)$this->context->cart->id;
            $this->context->cookie->write();

            die(json_encode([
                'ok' => true,
                'message' => 'Zestaw został dodany do koszyka.',
                'added_products' => $addedProducts,
                'cart_id' => (int)$this->context->cart->id,
                'errors' => $errors,
            ], JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            http_response_code(500);

            die(json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE));
        }
    }
}