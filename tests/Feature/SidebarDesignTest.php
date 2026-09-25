<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SidebarDesignTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
    }

    private function dashboard(): string
    {
        return $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/dashboard')->assertOk()->getContent();
    }

    public function test_sidebar_is_a_single_persistent_navigation_surface(): void
    {
        $html = $this->dashboard();

        $this->assertStringContainsString('<aside class="app-sidebar no-print"', $html);
        $this->assertStringContainsString('class="app-navigation" id="nav-panel"', $html);
        $this->assertStringContainsString('class="sidebar-logo"', $html);
        $this->assertStringContainsString('alt="Tracket"', $html);
        $this->assertStringNotContainsString('class="app-rail"', $html);
        $this->assertStringNotContainsString('class="rail-toggle"', $html);
    }

    public function test_sidebar_has_no_hide_or_collapse_effect(): void
    {
        $html = $this->dashboard();

        $this->assertStringNotContainsString('nav-collapsed', $html);
        $this->assertStringNotContainsString('.app-rail', $html);
        $this->assertStringNotContainsString('.rail-toggle', $html);
        $this->assertStringNotContainsString('localStorage.getItem(\'tracket.navCollapsed\')', $html);
    }

    public function test_sidebar_uses_one_surface_and_fixed_content_offset(): void
    {
        $html = $this->dashboard();

        $this->assertStringContainsString('.app-sidebar{', $html);
        $this->assertStringContainsString('display:flex;flex-direction:column;width:var(--nav-width)', $html);
        $this->assertStringContainsString('.app-topbar{', $html);
        $this->assertStringContainsString('left:var(--nav-width)', $html);
        $this->assertStringContainsString('.app-main{', $html);
        $this->assertStringContainsString('margin-left:var(--nav-width)', $html);
    }

    public function test_navigation_hooks_are_preserved(): void
    {
        $html = $this->dashboard();

        $this->assertStringContainsString('id="nav-panel"', $html);
        $this->assertStringContainsString('aria-label="Navigasi utama"', $html);
        $this->assertStringContainsString('class="navlink active"', $html);
        $this->assertSame(1, substr_count($html, '>Suku Cadang</span>'));
        $this->assertStringNotContainsString('>Stok Kritis</span>', $html);
    }
}
