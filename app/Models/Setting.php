<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'currency_symbol',
        'showcase_config',
        'login_offers',
    ];

    protected function casts(): array
    {
        return [
            'showcase_config' => 'array',
            'login_offers' => 'array',
        ];
    }

    public static function defaultLoginOffers(): array
    {
        return [
            'enabled' => true,
            'interval_seconds' => 4,
            'slides' => [
                [
                    'id' => 1,
                    'product_sku' => 'PERF-001',
                    'product_name' => 'LoEwe Solo Vulcan Eau de Parfum',
                    'original_price' => 9500.00,
                    'discount_price' => 8550.00,
                    'discount_percent' => 10,
                    'badge' => 'PREMIUM QUALITY',
                    'discount' => '10% OFF',
                    'title' => 'LoEwe Solo',
                    'subtitle' => 'Vulcan Eau de Parfum',
                    'image' => 'assets/images/behance/product_loewe_solo.png',
                    'tag' => 'EXCLUSIVE',
                ],
                [
                    'id' => 2,
                    'product_sku' => 'PERF-002',
                    'product_name' => 'Montale Soleil de Capri',
                    'original_price' => 9500.00,
                    'discount_price' => 8075.00,
                    'discount_percent' => 15,
                    'badge' => 'SIGNATURE FRAGRANCE',
                    'discount' => '-15% OFF',
                    'title' => 'Montale Soleil',
                    'subtitle' => 'Soleil de Capri Eau de Parfum',
                    'image' => 'assets/images/behance/product_montale_soleil.png',
                    'tag' => 'SPECIAL OFFER',
                ],
                [
                    'id' => 3,
                    'product_sku' => 'PERF-003',
                    'product_name' => 'Cosmic Intense Eau de Parfum',
                    'original_price' => 8500.00,
                    'discount_price' => 6800.00,
                    'discount_percent' => 20,
                    'badge' => 'ARTISANAL ESSENCE',
                    'discount' => '-20% OFF',
                    'title' => 'Cosmic Intense',
                    'subtitle' => 'Intense Eau de Parfum Natural Spray',
                    'image' => 'assets/images/behance/perfume_hero_bottle.png',
                    'tag' => 'DISCOUNT',
                ],
                [
                    'id' => 4,
                    'product_sku' => 'SAREE-009',
                    'product_name' => 'Designer Silk Saree (Luxury Atelier)',
                    'original_price' => 12500.00,
                    'discount_price' => 10625.00,
                    'discount_percent' => 15,
                    'badge' => 'FESTIVE ATELIER',
                    'discount' => '-15% OFF',
                    'title' => 'Mulberry Silk',
                    'subtitle' => 'Handcrafted Golden Zari Designer Weave',
                    'image' => 'assets/images/products/designer-saree-gold.png',
                    'tag' => 'EXCLUSIVE',
                ],
            ],
        ];
    }

    public function getLoginOffers(): array
    {
        $defaults = self::defaultLoginOffers();
        $stored = $this->login_offers ?? [];

        if (!empty($stored) && is_array($stored)) {
            $slides = (!empty($stored['slides']) && is_array($stored['slides']))
                ? array_values($stored['slides'])
                : $defaults['slides'];

            $merged = [
                'enabled' => array_key_exists('enabled', $stored) ? (bool)$stored['enabled'] : ($defaults['enabled'] ?? true),
                'interval_seconds' => !empty($stored['interval_seconds']) ? (int)$stored['interval_seconds'] : ($defaults['interval_seconds'] ?? 4),
                'slides' => $slides,
            ];
        } else {
            $merged = $defaults;
        }

        // Enrich slides with live product pricing if available
        if (! empty($merged['slides']) && is_array($merged['slides'])) {
            try {
                $productsBySku = Product::where('status', true)->get()->keyBy('sku');
                $productsById = $productsBySku->keyBy('id');

                foreach ($merged['slides'] as &$slide) {
                    $p = null;
                    if (! empty($slide['product_id'])) {
                        $p = $productsById->get((int)$slide['product_id']);
                    } elseif (! empty($slide['product_sku'])) {
                        $p = $productsBySku->get($slide['product_sku']);
                    }

                    if ($p) {
                        $slide['product_id'] = $p->id;
                        $slide['product_name'] = $p->name;
                        $slide['original_price'] = (float)$p->selling_price;
                        $slide['discount_price'] = (float)$p->effective_price;
                        $slide['discount_percent'] = $p->discount_percent_calculated ?? ($slide['discount_percent'] ?? 10);
                        $slide['has_discount'] = $p->has_discount;
                        if (empty($slide['image']) || $slide['image'] === 'assets/images/products/default.svg') {
                            $slide['image'] = $p->image_url;
                        }
                    }
                }
                unset($slide);
            } catch (\Throwable) {
                // Return gracefully if products table isn't migrated yet
            }
        }

        return $merged;
    }

    public static function defaultShowcaseConfig(): array
    {
        return [
            'enabled' => true,
            'enable_3d' => true,
            'tagline' => 'TAJ ATELIER · 3D LUXURY CLOTHING SHOWCASE',
            'features' => [
                'feature_1' => '🌿 100% Breathable Weave',
                'feature_2' => '✨ Golden Zari & Fine Stitching',
                'feature_3' => '🚚 Express Secure Delivery',
            ],
            'loupe' => [
                'kicker' => 'MACRO FABRIC INSPECTION',
                'title' => 'Microscopic Weave & Thread Analysis',
                'desc' => 'Examine the fine zari threads, warp-and-weft silk density, and seam precision under ultra-high definition zoom.',
                'image' => 'assets/images/products/saree-fabric-texture.jpg',
                'specs' => [
                    ['label' => 'Weave Density', 'value' => '320 Threads/Inch'],
                    ['label' => 'Zari Type', 'value' => 'Golden Dipped Zari'],
                    ['label' => 'Fiber', 'value' => '100% Pure Mulberry Silk'],
                    ['label' => 'Finish', 'value' => 'Hand-Polished Soft Lustre'],
                ],
            ],
            'hotspots' => [
                [
                    'id' => 1,
                    'top' => 24,
                    'left' => 36,
                    'card_top' => 14,
                    'card_left' => 4,
                    'title' => 'Mulberry Silk & Weave',
                    'desc' => 'Handwoven 4-ply organic silk with exquisite fluid drape and breathable featherlight softness.',
                ],
                [
                    'id' => 2,
                    'top' => 50,
                    'left' => 56,
                    'card_top' => 40,
                    'card_right' => 4,
                    'title' => 'Artisan Zari Embroidery',
                    'desc' => 'Intricate gold-thread woven border crafted by master artisans with heirloom royal finish.',
                ],
                [
                    'id' => 3,
                    'top' => 74,
                    'left' => 32,
                    'card_top' => 66,
                    'card_left' => 2,
                    'title' => 'Regal Fall & Silhouette',
                    'desc' => 'Precision pleating engineered for effortless modern movement and timeless elegance.',
                ],
                [
                    'id' => 4,
                    'top' => 86,
                    'left' => 62,
                    'card_top' => 76,
                    'card_right' => 2,
                    'title' => 'Heritage Longevity',
                    'desc' => 'Colorfast natural dyes · Pre-shrunk weave · Designed to last across generations.',
                ],
            ],
            'swatches' => [],
            'gallery' => [],
        ];
    }

    public function getShowcaseSettings(): array
    {
        $defaults = self::defaultShowcaseConfig();
        $stored = $this->showcase_config ?? [];
        return array_replace_recursive($defaults, $stored);
    }
}
