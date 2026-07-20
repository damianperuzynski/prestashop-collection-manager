<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;

class DfcollectionSwitchModuleFrontController extends ModuleFrontController
{
    /** JSON + tryb ajax */
    public $ssl = true;
    public $ajax = true;

    public function initContent()
    {
        parent::initContent();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idCategory = (int) Tools::getValue('id_category');
            if (!$idCategory) {
                throw new Exception('Brak parametru id_category.');
            }

            // Pobierz AKTYWNY rekord kolekcji dla danej kategorii
            $row = Db::getInstance()->getRow(
                'SELECT *
                 FROM `' . _DB_PREFIX_ . 'dfcollection`
                 WHERE `id_category` = ' . (int) $idCategory . '
                   AND `active` = 1'
            );

            if (!$row) {
                throw new Exception('Nie znaleziono aktywnej kolekcji dla tej kategorii.');
            }

            $idFeatured     = (int) $row['id_featured_product'];
            $imageUrl       = (string) $row['image_url'];
            $imageUrlMobile = isset($row['image_url_mobile']) ? (string) $row['image_url_mobile'] : '';
            $imageUrlXS     = isset($row['image_url_xs']) ? (string) $row['image_url_xs'] : '';
            $customTitle    = (string) $row['title'];
            $sliderInfinite = isset($row['slider_infinite']) ? (int) $row['slider_infinite'] : 1;
            $sliderInfinite = $sliderInfinite ? 1 : 0;
            $sliderSort       = isset($row['slider_sort']) ? (string) $row['slider_sort'] : 'random';
            $shortDescription = isset($row['short_description']) ? (string)$row['short_description'] : '';
			$showFeaturedCountdown = isset($row['show_featured_countdown']) ? (int)$row['show_featured_countdown'] : 0;
            $imageCompareUrl     = isset($row['image_compare_url']) ? (string) $row['image_compare_url'] : '';
            $arrangementImageUrl = isset($row['arrangement_image_url']) ? (string) $row['arrangement_image_url'] : '';
            $compareStartPercent = isset($row['compare_start_percent']) ? (int) $row['compare_start_percent'] : 50;
            $compareLabel        = isset($row['compare_label']) ? (string) $row['compare_label'] : '';
            $badge1 = isset($row['badge_1']) ? trim((string)$row['badge_1']) : '';
            $badge2 = isset($row['badge_2']) ? trim((string)$row['badge_2']) : '';
            $badge3 = isset($row['badge_3']) ? trim((string)$row['badge_3']) : '';
            $badge4 = isset($row['badge_4']) ? trim((string)$row['badge_4']) : '';
            $collectionScope = isset($row['collection_scope']) ? trim((string)$row['collection_scope']) : '';

            // =========================
            // BUNDLE (najczęściej kupowane razem)
            // =========================
            $bundleItemsRaw = Db::getInstance()->executeS(
                'SELECT *
                 FROM `'._DB_PREFIX_.'dfcollection_bundle_item`
                 WHERE id_dfcollection = '.(int)$row['id_dfcollection'].'
                 ORDER BY position ASC'
            );

            $dfcBundleItems = [];
            $totalBundlePrice = 0;

            if (!empty($bundleItemsRaw)) {
                foreach ($bundleItemsRaw as $item) {

                    if (!(int)$item['active']) {
                        continue;
                    }

                    $product = $this->presentOneById((int)$item['id_product']);

                    if (!$product) {
                        continue;
                    }

                    $price = isset($product['price_amount']) ? (float)$product['price_amount'] : 0;
                    $deliveryInfo = $this->getDeliveryInfoForProduct((int)$item['id_product']);

                    $dfcBundleItems[] = [
                        'id_product'    => (int)$item['id_product'],
                        'name'          => isset($product['name']) ? $product['name'] : '',
                        'url'           => isset($product['url']) ? $product['url'] : '',
                        'image'         => isset($product['cover']['bySize']['home_default']['url'])
                                ? $product['cover']['bySize']['home_default']['url']
                                : '',
                        'price'         => isset($product['price']) ? $product['price'] : '',
                        'regular_price' => isset($product['regular_price']) ? $product['regular_price'] : '',
                        'price_amount'          => isset($product['price_amount']) ? (float)$product['price_amount'] : 0,
                        'regular_price_amount'  => isset($product['regular_price_amount']) ? (float)$product['regular_price_amount'] : 0,
                        'has_discount'          => !empty($product['has_discount']),
                        'discount_percentage'   => !empty($product['discount_percentage']) ? $product['discount_percentage'] : '',
                        'discount'              => !empty($product['discount']) ? $product['discount'] : '',
                        'delivery_text'                 => $deliveryInfo['delivery_text'],
                        'shipping_cost_gross'           => $deliveryInfo['shipping_cost_gross'],
                        'shipping_cost_gross_formatted' => $deliveryInfo['shipping_cost_gross_formatted'],
                        'free_shipping_from'            => $deliveryInfo['free_shipping_from'],
                        'free_shipping_from_formatted'  => $deliveryInfo['free_shipping_from_formatted'],
                        'custom_label'          => (string)$item['custom_label'],
                        'active'                => 1,
                    ];

                    $totalBundlePrice += $price;
                }
            }

            // format ceny zestawu + oszczędności
            $bundleTotalFormatted = '';
            $bundleTotalSavings = 0.0;
            $bundleSavingsFormatted = '';
            $bundleTotalRegularFormatted = $this->getBundleTotalRegularPrice($dfcBundleItems);
            $bundleShippingFromAmount = $this->getBundleShippingFromAmount($dfcBundleItems);
            $bundleShippingFrom = $bundleShippingFromAmount > 0
                ? Tools::displayPrice($bundleShippingFromAmount, $this->context->currency)
                : '';
            $bundleDeliveryText = $this->getBundleDeliveryText($dfcBundleItems);

            foreach ($dfcBundleItems as $bundleItem) {
                $regular = isset($bundleItem['regular_price_amount']) ? (float)$bundleItem['regular_price_amount'] : 0;
                $current = isset($bundleItem['price_amount']) ? (float)$bundleItem['price_amount'] : 0;

                if ($regular > $current && $current > 0) {
                    $bundleTotalSavings += ($regular - $current);
                }
             }

             if ($totalBundlePrice > 0) {
                 $bundleTotalFormatted = Tools::displayPrice($totalBundlePrice, $this->context->currency);
             }

             if ($bundleTotalSavings > 0) {
                 $bundleSavingsFormatted = Tools::displayPrice($bundleTotalSavings, $this->context->currency);
             }

            // LIMIT per kolekcja (1..20)
            $limit = isset($row['slider_limit']) ? (int) $row['slider_limit'] : 8;
            if ($limit < 1) {
                $limit = 1;
            }
            if ($limit > 20) {
                $limit = 20;
            }

            $idLang = (int) $this->context->language->id;
            $productsCount = $this->getCollectionProductsCount((int)$idCategory);
            $totalCollections = (int) Db::getInstance()->getValue(
                'SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'dfcollection`
                WHERE `active` = 1'
            );
            $lowestPriceData = $this->getCollectionLowestPriceData((int)$idCategory);
            $freeShippingData = $this->getCollectionFreeShippingFromData((int)$idCategory);

            // pobierz większą pulę produktów, żeby po wykluczeniu featured nadal było z czego ciąć
            $products = $this->presentFromCategory($idCategory, $idLang, 30, $sliderSort);

            // wyklucz produkt polecany ze slidera
            if ($idFeatured > 0) {
                $products = array_values(array_filter($products, function ($product) use ($idFeatured) {
                    if (is_array($product) && isset($product['id_product'])) {
                        return (int)$product['id_product'] !== (int)$idFeatured;
                    }

                    if (is_object($product) && isset($product->id_product)) {
                        return (int)$product->id_product !== (int)$idFeatured;
                    }

                    return true;
                }));
            }

            // dopiero po odfiltrowaniu featured przytnij do ustawionego limitu
            $products = array_slice($products, 0, $limit);

            // Produkt polecany (opcjonalny)
            $featured = null;
            $featuredUrl = '';

            if ($idFeatured > 0) {
               $featured = $this->presentOneById($idFeatured);

               if ($featured && !empty($featured['url'])) {
                   $featuredUrl = (string)$featured['url'];
               } else {
                  $featuredUrl = $this->context->link->getProductLink((int)$idFeatured);
               }
            }

			$showFeaturedCountdown = $showFeaturedCountdown ? 1 : 0;
            $featuredCountdownProductId = 0;

            if ($showFeaturedCountdown && $idFeatured > 0) {
                $featuredCountdownProductId = (int)$idFeatured;
            }

            // Tytuł – jeśli brak własnego, weź nazwę kategorii
            $cat = new Category($idCategory, $idLang);
            if (!Validate::isLoadedObject($cat)) {
                throw new Exception('Nie udało się załadować kategorii.');
            }

            $title = trim($customTitle) !== '' ? $customTitle : (string) $cat->name;
            $link  = $this->context->link->getCategoryLink($idCategory);

            // Assign dla partiali
            $this->context->smarty->assign([
                'dfc_title'            => $title,
                'dfc_image_url'        => $imageUrl,
                'dfc_image_url_mobile' => $imageUrlMobile,
                'dfc_image_url_xs'     => $imageUrlXS,
                'dfc_image_compare_url'      => $imageCompareUrl,
                'dfc_compare_start_percent'  => max(0, min(100, (int) $compareStartPercent)),
                'dfc_compare_label'          => $compareLabel,
                'dfc_arrangement_image_url'  => $arrangementImageUrl,                           
                'dfc_badge_1' => $badge1,
                'dfc_badge_2' => $badge2,
                'dfc_badge_3' => $badge3,
                'dfc_badge_4' => $badge4,
                'dfc_lowest_price'                 => $lowestPriceData['price'],
                'dfc_lowest_price_amount'          => $lowestPriceData['price_amount'],
                'dfc_lowest_price_regular'         => $lowestPriceData['regular_price'],
                'dfc_lowest_price_regular_amount'  => $lowestPriceData['regular_price_amount'],
                'dfc_lowest_price_has_discount'    => $lowestPriceData['has_discount'],
                'dfc_lowest_price_discount_label'  => $lowestPriceData['discount_label'],
                'dfc_lowest_price_product_id'      => $lowestPriceData['id_product'],
                'dfc_lowest_price_product_name'    => $lowestPriceData['name'],
                'dfc_lowest_price_product_url'     => $lowestPriceData['url'],                           
                'dfc_collection_scope' => $collectionScope,                           
                'dfc_featured'         => $featured,
                'dfc_featured_url'     => $featuredUrl,
				'dfc_show_featured_countdown'       => $showFeaturedCountdown,
                'dfc_featured_countdown_product_id' => $featuredCountdownProductId,						   
                'dfc_products'         => $products,
                'dfc_products_count'   => (int)$productsCount,
                'dfc_total_collections' => $totalCollections,
                'dfc_current_cat_id'   => (int) $idCategory,
                'dfc_category_link'    => $link,
                'dfc_category_name'    => (string) $cat->name,
                'dfc_slider_infinite'  => $sliderInfinite,
                'dfc_short_description' => $shortDescription,
                'dfc_bundle_items' => $dfcBundleItems,
                'dfc_bundle_total_regular_price' => $bundleTotalRegularFormatted,                           
                'dfc_bundle_total_price' => $bundleTotalFormatted,
                'dfc_bundle_total_savings' => $bundleSavingsFormatted,
                'dfc_bundle_delivery_text' => $bundleDeliveryText,
                'dfc_bundle_shipping_from' => $bundleShippingFrom,
                'dfc_bundle_shipping_from_amount' => $bundleShippingFromAmount,
                'dfc_free_shipping_from_amount' => $freeShippingData['amount'],
                'dfc_free_shipping_from'        => $freeShippingData['formatted'],                           
            ]);

            // Render dwóch fragmentów
            $htmlMain = $this->context->smarty->fetch(
                'module:dfcollection/views/templates/hook/partials/main.tpl'
            );
            $htmlSlider = $this->context->smarty->fetch(
                'module:dfcollection/views/templates/hook/partials/slider.tpl'
            );

            die(json_encode([
                'ok'          => true,
                'title'       => $title,
                'featuredUrl'   => $featuredUrl,            
                'htmlMain'    => $htmlMain,
                'htmlSlider'  => $htmlSlider,
				'showFeaturedCountdown' => $showFeaturedCountdown,
                'featuredCountdownProductId' => $featuredCountdownProductId,			
                'imageMobile' => $imageUrlMobile,
                'imageXS'     => $imageUrlXS,
                'limit'       => $limit,
                'infinite'    => $sliderInfinite,
                'sort'        => $sliderSort,
                'productsCount' => (int)$productsCount,
            ], JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            http_response_code(500);

            die(json_encode([
                'ok'    => false,
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE));
        }
    }

    protected function getCollectionProductsCount($idCategory)
    {
        $idCategory = (int)$idCategory;
        $idShop = (int)$this->context->shop->id;

        if ($idCategory <= 0) {
            return 0;
        }

        $sql = '
            SELECT COUNT(DISTINCT cp.id_product)
            FROM `'._DB_PREFIX_.'category_product` cp
            INNER JOIN `'._DB_PREFIX_.'product_shop` ps
                ON ps.id_product = cp.id_product
               AND ps.id_shop = '.(int)$idShop.'
               AND ps.active = 1
            WHERE cp.id_category = '.(int)$idCategory;

        return (int)Db::getInstance()->getValue($sql);
    }

    protected function getCollectionLowestPriceData($idCategory)
    {
        $idCategory = (int)$idCategory;
        $idShop = (int)$this->context->shop->id;

        if ($idCategory <= 0) {
            return [
                'id_product' => 0,
                'name' => '',
                'url' => '',
                'price' => '',
                'price_amount' => 0.0,
                'regular_price' => '',
                'regular_price_amount' => 0.0,
                'has_discount' => 0,
                'discount_label' => '',
            ];
        }

        $rows = Db::getInstance()->executeS('
            SELECT DISTINCT cp.id_product
            FROM `'._DB_PREFIX_.'category_product` cp
            INNER JOIN `'._DB_PREFIX_.'product_shop` ps
                ON ps.id_product = cp.id_product
               AND ps.id_shop = '.(int)$idShop.'
               AND ps.active = 1
            WHERE cp.id_category = '.(int)$idCategory.'
            ORDER BY cp.position ASC
        ');

        if (empty($rows)) {
            return [
                'id_product' => 0,
                'name' => '',
                'url' => '',
                'price' => '',
                'price_amount' => 0.0,
                'regular_price' => '',
                'regular_price_amount' => 0.0,
                'has_discount' => 0,
                'discount_label' => '',
            ];
        }

        $best = null;

        foreach ($rows as $row) {
            $idProduct = (int)$row['id_product'];
            if ($idProduct <= 0) {
                continue;
            }

            $product = $this->presentOneById($idProduct);
            if (!$product) {
                continue;
            }

            $currentPrice = isset($product['price_amount']) ? (float)$product['price_amount'] : 0.0;
            $regularPrice = isset($product['regular_price_amount']) ? (float)$product['regular_price_amount'] : 0.0;

            if ($currentPrice <= 0) {
                continue;
            }

            $hasDiscount = ($regularPrice > $currentPrice && $currentPrice > 0) ? 1 : 0;
            $discountLabel = '';

            if ($hasDiscount && !empty($product['discount_percentage'])) {
                $discountLabel = (string)$product['discount_percentage'];
            } elseif ($hasDiscount && !empty($product['discount'])) {
                $discountLabel = (string)$product['discount'];
            }

            if ($best === null || $currentPrice < $best['price_amount']) {
                $best = [
                    'id_product' => $idProduct,
                    'name' => !empty($product['name']) ? (string)$product['name'] : '',
                    'url' => !empty($product['url']) ? (string)$product['url'] : $this->context->link->getProductLink($idProduct),
                    'price' => !empty($product['price']) ? (string)$product['price'] : '',
                    'price_amount' => $currentPrice,
                    'regular_price' => $hasDiscount && !empty($product['regular_price']) ? (string)$product['regular_price'] : '',
                    'regular_price_amount' => $hasDiscount ? $regularPrice : 0.0,
                    'has_discount' => $hasDiscount,
                    'discount_label' => $discountLabel,
                ];
            }
        }

        if ($best === null) {
            return [
                'id_product' => 0,
                'name' => '',
                'url' => '',
                'price' => '',
                'price_amount' => 0.0,
                'regular_price' => '',
                'regular_price_amount' => 0.0,
                'has_discount' => 0,
                'discount_label' => '',
            ];
        }

        return $best;
    }

    protected function getCollectionFreeShippingFromData($idCategory)
    {
        $idCategory = (int)$idCategory;

        $result = [
            'amount' => 0.0,
            'formatted' => '',
        ];

        if ($idCategory <= 0) {
            return $result;
        }

        // brak freelivery → brak danych
        if (!Module::isInstalled('freelivery') || !Module::isEnabled('freelivery')) {
            return $result;
        }

        $defaultCountryId = (int)Configuration::get('PS_COUNTRY_DEFAULT');
        $defaultZoneId = (int)Country::getIdZone($defaultCountryId);

        // grupy klienta
        $groups = [];
        if (!empty($this->context->customer) && (int)$this->context->customer->id > 0) {
            $groups = Customer::getGroupsStatic((int)$this->context->customer->id);
            if (!empty($groups)) {
                $groups = array_map('intval', $groups);
            }
        }

        if (empty($groups)) {
            $groups = [(int)Configuration::get('PS_UNIDENTIFIED_GROUP')];
        }

        // reguły freelivery
        $rules = Db::getInstance()->executeS('
            SELECT f.*
            FROM `'._DB_PREFIX_.'freelivery` f
            WHERE (f.`id_zone` = 0 OR f.`id_zone` = '.(int)$defaultZoneId.')
              AND (
                    (f.`date_start` IS NULL AND f.`date_end` IS NULL)
                 OR (f.`date_start` IS NULL AND f.`date_end` > NOW())
                 OR (f.`date_end` IS NULL AND f.`date_start` <= NOW())
                 OR (NOW() BETWEEN f.`date_start` AND f.`date_end`)
              )
            ORDER BY
                CASE WHEN f.`min_price` > 0 THEN f.`min_price` ELSE 999999999 END ASC,
                f.`id` ASC
        ');

        if (empty($rules)) {
            return $result;
        }

        foreach ($rules as $rule) {
            $idCondition = (int)$rule['id'];

            // grupy
            $ruleGroups = Db::getInstance()->executeS('
                SELECT `id_group`
                FROM `'._DB_PREFIX_.'freelivery_groups`
                WHERE `id_condition` = '.(int)$idCondition
            );

            if (!empty($ruleGroups)) {
                $ruleGroupIds = array_map('intval', array_column($ruleGroups, 'id_group'));
                if (!array_intersect($groups, $ruleGroupIds)) {
                    continue;
                }
            }

            // wykluczone kategorie
            $excludedRows = Db::getInstance()->executeS('
                SELECT `id_category`
                FROM `'._DB_PREFIX_.'freelivery_excluded_categories`
                WHERE `id_condition` = '.(int)$idCondition
            );

            if (!empty($excludedRows)) {
                $excludedIds = array_map('intval', array_column($excludedRows, 'id_category'));
                if (in_array($idCategory, $excludedIds, true)) {
                    continue;
                }
            }

            $amount = isset($rule['min_price']) ? (float)$rule['min_price'] : 0.0;

            return [
                'amount' => $amount,
                'formatted' => $amount > 0
                    ? Tools::displayPrice($amount, $this->context->currency)
                    : '',
            ];
        }

        return $result;
    }

    protected function getDeliveryInfoForProduct($idProduct)
    {
        $idProduct = (int)$idProduct;

        if ($idProduct <= 0) {
            return [
                'delivery_text' => '',
                'shipping_cost_gross' => 0.0,
                'shipping_cost_gross_formatted' => '',
                'free_shipping_from' => 0.0,
                'free_shipping_from_formatted' => '',
            ];
        }

        $row = Db::getInstance()->getRow('
            SELECT
                `additional_delivery_times`,
                `delivery_information`,
                `delivery_in_stock`,
                `delivery_out_stock`,
                `shipping_cost_gross`,
                `free_shipping_from`,
                `listing_delivery_text_auto`
            FROM `'._DB_PREFIX_.'dfdeliveryinfo_product_source`
            WHERE `id_product` = '.(int)$idProduct
        );

        if (!$row) {
            return [
                'delivery_text' => '',
                'shipping_cost_gross' => 0.0,
                'shipping_cost_gross_formatted' => '',
                'free_shipping_from' => 0.0,
                'free_shipping_from_formatted' => '',
            ];
        }

        $deliveryText = '';
        $mode = isset($row['additional_delivery_times']) ? (int)$row['additional_delivery_times'] : 0;

        if ($mode === 1 && !empty($row['delivery_information'])) {
            $deliveryText = trim((string)$row['delivery_information']);
        } elseif ($mode === 2 && !empty($row['delivery_in_stock'])) {
            $deliveryText = trim((string)$row['delivery_in_stock']);
        } elseif (!empty($row['listing_delivery_text_auto'])) {
            $deliveryText = trim((string)$row['listing_delivery_text_auto']);
        }

        $shippingCostGross = isset($row['shipping_cost_gross']) ? (float)$row['shipping_cost_gross'] : 0.0;
        $freeShippingFrom = isset($row['free_shipping_from']) ? (float)$row['free_shipping_from'] : 0.0;

        return [
            'delivery_text' => $deliveryText,
            'shipping_cost_gross' => $shippingCostGross,
            'shipping_cost_gross_formatted' => $shippingCostGross > 0
                ? Tools::displayPrice($shippingCostGross, $this->context->currency)
                : '',
            'free_shipping_from' => $freeShippingFrom,
            'free_shipping_from_formatted' => $freeShippingFrom > 0
                ? Tools::displayPrice($freeShippingFrom, $this->context->currency)
                : '',
        ];
    }

    protected function presentProductsForListing(array $rawProducts)
    {
        $productsForTemplate = [];

        if (empty($rawProducts)) {
            return $productsForTemplate;
        }

        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();

        $presenter = new ProductListingPresenter(
            new ImageRetriever($this->context->link),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        foreach ($rawProducts as $rawProduct) {
            if (!$rawProduct) {
                continue;
            }

            $productsForTemplate[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $productsForTemplate;
    }

    protected function presentFromCategory($idCategory, $idLang, $limit = null, $sort = 'random')
    {
        if ($limit === null) {
            $limit = 8;
        }
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 20) {
            $limit = 20;
        }

        $orderBy = 'position';
        $orderWay = 'ASC';

        switch ($sort) {
            case 'random':
                $orderBy = 'position';
                $orderWay = 'ASC';
                break;

            case 'newest':
                $orderBy = 'date_add';
                $orderWay = 'DESC';
                break;

            case 'price_asc':
                $orderBy = 'price';
                $orderWay = 'ASC';
                break;

            case 'price_desc':
                $orderBy = 'price';
                $orderWay = 'DESC';
                break;

            case 'bestseller':
                $orderBy = 'sales';
                $orderWay = 'DESC';
                break;

            case 'position':
            default:
                $orderBy = 'position';
                $orderWay = 'ASC';
                break;
        }

        $ids = Product::getProducts($idLang, 0, 30, $orderBy, $orderWay, (int) $idCategory, true);
        if (!is_array($ids) || empty($ids)) {
            return [];
        }

        if ($sort === 'random') {
            shuffle($ids);
        }

        $ids = array_slice($ids, 0, $limit);

        $rawProducts = [];

        foreach ($ids as $prod) {
            if (empty($prod['id_product'])) {
                continue;
            }

            $idProduct = (int) $prod['id_product'];
            $raw = (array) new Product($idProduct, false, (int) $idLang);

            if (empty($raw['id'])) {
                continue;
            }

            $raw['id_product'] = $idProduct;

            $cover = Product::getCover($idProduct);
            if (!empty($cover['id_image'])) {
                $raw['id_image'] = $idProduct . '-' . (int) $cover['id_image'];
            }

            $rawProducts[] = $raw;
        }

        return $this->presentProductsForListing($rawProducts);
    }

    protected function presentOneById($idProduct)
    {
        $idProduct = (int) $idProduct;
        $idLang = (int) $this->context->language->id;

        $raw = (array) new Product($idProduct, false, $idLang);
        if (empty($raw['id'])) {
            return null;
        }

        $raw['id_product'] = $idProduct;

        $cover = Product::getCover($idProduct);
        if (!empty($cover['id_image'])) {
            $raw['id_image'] = $idProduct . '-' . (int) $cover['id_image'];
        }

        $presented = $this->presentProductsForListing([$raw]);

        return !empty($presented[0]) ? $presented[0] : null;
    }

    protected function getBundleDeliveryText(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return '';
        }

        $texts = [];

        foreach ($bundleItems as $item) {
            if (!empty($item['delivery_text'])) {
                $texts[] = trim((string)$item['delivery_text']);
            }
        }

        if (empty($texts)) {
            return '';
        }

        usort($texts, function ($a, $b) {
            return mb_strlen($b) - mb_strlen($a);
        });

        return (string)$texts[0];
    }

    protected function getBundleTotalRegularPrice(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return '';
        }

        $totalRegular = 0.0;

        foreach ($bundleItems as $item) {
            $regular = 0.0;
            $current = 0.0;

            if (!empty($item['regular_price_amount'])) {
                $regular = (float)$item['regular_price_amount'];
            }

            if (!empty($item['price_amount'])) {
                $current = (float)$item['price_amount'];
            }

            if ($regular > $current && $current > 0) {
                $totalRegular += $regular;
            } else {
                $totalRegular += $current;
            }
        }

        if ($totalRegular <= 0) {
            return '';
        }

        return Tools::displayPrice($totalRegular, $this->context->currency);
    }

    protected function getBundleShippingFromAmount(array $bundleItems)
    {
        if (empty($bundleItems)) {
            return 0.0;
        }

        $maxShipping = 0.0;

        foreach ($bundleItems as $item) {
            $shipping = isset($item['shipping_cost_gross']) ? (float)$item['shipping_cost_gross'] : 0.0;

            if ($shipping > $maxShipping) {
                $maxShipping = $shipping;
            }
        }

        return (float)$maxShipping;
    }
}