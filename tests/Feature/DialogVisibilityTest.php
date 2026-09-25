<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * A closed <dialog> must not paint or swallow clicks. The shared theme used to set
 * `display:flex` on `.confirm-dialog` unconditionally, so the logout card rendered
 * centred over the dashboard before the user ever asked for it, and its buttons
 * stayed hit-testable — an unrequested click on "Ya, keluar" logged the user out.
 */
class DialogVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
    }

    private function themeCss(): string
    {
        $view = $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/dashboard')->assertOk()->getContent();
        preg_match('/<style>(.*?)<\/style>/s', $view, $m);
        $this->assertNotEmpty($m[1] ?? '', 'shared theme stylesheet is missing from the layout');

        return $m[1];
    }

    public function test_closed_confirm_dialog_is_not_rendered(): void
    {
        $css = $this->themeCss();

        $this->assertStringContainsString('.confirm-dialog:not([open]){display:none}', $css,
            'a closed .confirm-dialog must be display:none, otherwise the logout card shows on every page load');

        $this->assertDoesNotMatchRegularExpression('/\.confirm-dialog\{[^}]*display:flex/', $css,
            '.confirm-dialog must not force display:flex unconditionally');
    }

    public function test_open_dialog_still_centres_its_card(): void
    {
        $css = $this->themeCss();

        $this->assertStringContainsString('.confirm-dialog[open]{display:flex', $css,
            'an open dialog must lay out as a centring flex container');
    }

    public function test_global_hidden_rule_covers_dialogs(): void
    {
        $css = $this->themeCss();

        $this->assertStringContainsString('[hidden]{display:none!important}', $css,
            'the global [hidden] rule must apply to dialogs too');

        $this->assertStringNotContainsString('[hidden]:not(dialog)', $css,
            'exempting dialog from [hidden] leaves hidden dialogs visible');
    }

    public function test_account_panel_still_overrides_hidden_for_its_slide_animation(): void
    {
        $this->assertStringContainsString('.account-panel[hidden]{display:block!important', $this->themeCss(),
            'the account panel animates via visibility, so it must keep overriding [hidden]');
    }

    public function test_logout_dialog_markup_is_present_on_authenticated_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'technician']))->get('/dashboard')->assertOk()
            ->assertSee('id="logout-dialog"', false)
            ->assertSee('data-logout-open', false)
            ->assertSee('data-logout-confirm', false)
            ->assertSee('id="logout-form"', false);
    }
}
