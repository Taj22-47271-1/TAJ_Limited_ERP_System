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
                    'product_sku' => 'SAREE-009',
                    'product_name' => 'Designer Silk Saree (Luxury Atelier)',
                    'original_price' => 4500.00,
                    'discount_price' => 3825.00,
                    'discount_percent' => 15,
                    'badge' => '🎉 EID UL FITR EXCLUSIVE',
                    'discount' => '15% OFF',
                    'title' => 'Exclusive Eid Ethnic Luxury Collection',
                    'subtitle' => 'Handcrafted pure silk sarees with royal zari drapes. Claim your festive 15% discount today.',
                    'image' => 'assets/images/products/designer-saree-rose.png',
                    'tag' => 'EID OFFER',
                ],
                [
                    'id' => 2,
                    'product_sku' => 'KB-002',
                    'product_name' => 'Pro Tactile Mechanical Keyboard',
                    'original_price' => 2400.00,
                    'discount_price' => 1920.00,
                    'discount_percent' => 20,
                    'badge' => '✨ DURGA PUJA UTSAV',
                    'discount' => '20% OFF',
                    'title' => 'High-Performance Workspace Essentials',
                    'subtitle' => 'Precision mechanical switches, aircraft aluminum top plate, and dynamic backlight.',
                    'image' => 'assets/images/products/mechanical-keyboard.svg',
                    'tag' => 'PUJA OFFER',
                ],
                [
                    'id' => 3,
                    'product_sku' => 'HUB-003',
                    'product_name' => 'Ultra-Fast 7-in-1 USB-C Power Hub',
                    'original_price' => 1750.00,
                    'discount_price' => 1487.50,
                    'discount_percent' => 15,
                    'badge' => '🎄 BORO DIN & WINTER SPECIAL',
                    'discount' => '15% OFF',
                    'title' => 'Winter Festive Specials & Modern Elegance',
                    'subtitle' => 'Fast 4K HDMI 60Hz, 100W Power Delivery, and multi-display productivity hub.',
                    'image' => 'assets/images/products/usb-c-hub.svg',
                    'tag' => 'BORO DIN OFFER',
                ],
                [
                    'id' => 4,
                    'product_sku' => 'WM-001',
                    'product_name' => 'Ergonomic Precision Wireless Mouse',
                    'original_price' => 650.00,
                    'discount_price' => 585.00,
                    'discount_percent' => 10,
                    'badge' => '⚡ FLASH SALE',
                    'discount' => '10% OFF',
                    'title' => 'Whisper-Quiet Ergonomic Wireless Mouse',
                    'subtitle' => 'Dual Bluetooth + 2.4GHz wireless connectivity with 18-month battery life.',
                    'image' => 'assets/images/products/wireless-mouse.svg',
                    'tag' => 'FLASH SALE',
                ],
            ],
        ];
    }

    public function getLoginOffers(): array
    {
        $defaults = self::defaultLoginOffers();
        $stored = $this->login_offers ?? [];
        $merged = array_replace_recursive($defaults, $stored);

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
            'swatches' => [
                ['key' => 'rose', 'name' => 'Champagne Rose', 'color' => '#e5b2a2', 'image' => 'assets/images/products/designer-saree-rose.png', 'tag' => 'Rose Gold Weave'],
                ['key' => 'sapphire', 'name' => 'Royal Sapphire', 'color' => '#1e3a8a', 'image' => 'assets/images/products/designer-saree-sapphire.png', 'tag' => 'Deep Navy Indigo'],
                ['key' => 'noir', 'name' => 'Midnight Noir', 'color' => '#18181b', 'image' => 'assets/images/products/designer-saree-noir.png', 'tag' => 'Obsidian Black'],
                ['key' => 'emerald', 'name' => 'Imperial Emerald', 'color' => '#065f46', 'image' => 'assets/images/products/designer-saree-emerald.png', 'tag' => 'Royal Forest'],
                ['key' => 'gold', 'name' => 'Regal Gold', 'color' => '#d97706', 'image' => 'assets/images/products/designer-saree-gold.png', 'tag' => 'Antique Zari'],
            ],
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
