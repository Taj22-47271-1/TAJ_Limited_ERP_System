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
                    'badge' => '🎉 EID UL FITR EXCLUSIVE',
                    'discount' => '15% DISCOUNT',
                    'title' => 'Exclusive Eid Ethnic Luxury Collection',
                    'subtitle' => 'Handcrafted pure silk sarees, royal festive kurtas & shirts. Claim your celebratory discount today.',
                    'image' => 'assets/images/offers/eid-offer.svg',
                    'tag' => 'EID OFFER',
                ],
                [
                    'id' => 2,
                    'badge' => '✨ DURGA PUJA UTSAV',
                    'discount' => '20% DISCOUNT',
                    'title' => 'Traditional Artisanal Weaves & Zari Drapes',
                    'subtitle' => 'Celebrate the festival of joy with heirloom craftsmanship, shimmering gold borders, and pure heritage.',
                    'image' => 'assets/images/offers/puja-offer.svg',
                    'tag' => 'PUJA OFFER',
                ],
                [
                    'id' => 3,
                    'badge' => '🎄 BORO DIN & NEW YEAR',
                    'discount' => '10% DISCOUNT',
                    'title' => 'Winter Festive Specials & Modern Elegance',
                    'subtitle' => 'Warm festive savings across luxury formal wear, accessories, and seasonal arrivals.',
                    'image' => 'assets/images/offers/winter-offer.svg',
                    'tag' => 'BORO DIN OFFER',
                ],
            ],
        ];
    }

    public function getLoginOffers(): array
    {
        $defaults = self::defaultLoginOffers();
        $stored = $this->login_offers ?? [];
        return array_replace_recursive($defaults, $stored);
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
                ['key' => 'rose', 'name' => 'Champagne Rose', 'color' => '#e5b2a2', 'image' => 'assets/images/products/designer-saree.png', 'tag' => 'Rose Gold Weave'],
                ['key' => 'sapphire', 'name' => 'Royal Sapphire', 'color' => '#2b59c3', 'image' => '', 'tag' => 'Deep Navy Indigo'],
                ['key' => 'noir', 'name' => 'Midnight Noir', 'color' => '#1a1a24', 'image' => '', 'tag' => 'Obsidian Black'],
                ['key' => 'emerald', 'name' => 'Imperial Emerald', 'color' => '#1a7f53', 'image' => '', 'tag' => 'Royal Forest'],
                ['key' => 'gold', 'name' => 'Regal Gold', 'color' => '#d4af37', 'image' => '', 'tag' => 'Antique Zari'],
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
