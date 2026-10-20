<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomerStorefrontVisibilityTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('customerPages')]
    public function test_customer_pages_do_not_expose_owner_topsis_tools(string $url): void
    {
        $response = $this->get($url);

        $response->assertOk()
            ->assertDontSee('TOPSIS')
            ->assertDontSee('admin/topsis');
    }

    public function test_store_navigation_is_in_the_header_instead_of_the_footer(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee(route('home'), false)
            ->assertSee(route('about'), false)
            ->assertSee(route('contact'), false)
            ->assertDontSee('Navigasi Toko');
    }

    public static function customerPages(): array
    {
        return [
            'home' => ['/'],
            'product catalog' => ['/produk'],
            'about page' => ['/tentang'],
            'contact page' => ['/kontak'],
        ];
    }
}
