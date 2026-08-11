<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Models\UiTemplate;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.local',
            'password' => 'secret',
            'role' => 'admin',
            'date_of_birth' => '1990-01-01',
            'is_active' => true,
        ]);
        $this->withSession([
            'user_id' => $this->admin->id,
            'user_role' => 'admin',
            'user_name' => $this->admin->name,
        ]);
    }

    public function test_admin_management_pages_render_real_data(): void
    {
        User::create([
            'name' => 'Respondent Test',
            'email' => 'respondent@test.local',
            'password' => 'secret',
            'role' => 'respondent',
            'date_of_birth' => '2000-02-03',
            'is_active' => true,
        ]);
        UiTemplate::create([
            'version' => 'A',
            'name' => 'Minimal Test',
            'description' => 'Template test',
            'config' => ['theme' => 'light'],
            'is_active' => true,
        ]);

        $this->get(route('admin.researchers'))->assertOk()->assertSee('Tambah Peneliti');
        $this->get(route('admin.respondents'))->assertOk()->assertSee('Respondent Test');
        $this->get(route('admin.experiments'))->assertOk()->assertSee('Eksperimen');
        $this->get(route('admin.templates'))->assertOk()->assertSee('Minimal Test');
        $this->get(route('admin.logs'))->assertOk()->assertSee('Audit Log');
        $this->get(route('admin.configuration'))->assertOk()->assertSee('Tambah Konfigurasi');
    }

    public function test_admin_can_create_and_deactivate_a_researcher_with_audit_trail(): void
    {
        $this->post(route('admin.researchers.store'), [
            'name' => 'Peneliti Baru',
            'email' => 'peneliti@test.local',
            'date_of_birth' => '1995-06-15',
        ])->assertRedirect(route('admin.researchers'));

        $researcher = User::where('email', 'peneliti@test.local')->firstOrFail();
        $this->assertSame('researcher', $researcher->role);
        $this->assertTrue($researcher->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'entity_id' => $researcher->id]);

        $this->patch(route('admin.users.status', $researcher))->assertRedirect();
        $this->assertFalse($researcher->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'deactivated', 'entity_id' => $researcher->id]);
    }

    public function test_admin_can_update_template_and_store_structured_configuration(): void
    {
        $template = UiTemplate::create([
            'version' => 'B',
            'name' => 'Template Lama',
            'config' => ['theme' => 'light'],
            'is_active' => true,
        ]);

        $this->put(route('admin.templates.update', $template), [
            'name' => 'Template Baru',
            'description' => 'Sudah diperbarui',
            'config' => json_encode(['theme' => 'dark', 'density' => 'compact']),
        ])->assertRedirect();
        $this->assertSame('dark', $template->fresh()->config['theme']);

        $this->post(route('admin.configuration.store'), [
            'key' => 'experiment.display',
            'group' => 'experiment',
            'description' => 'Pengaturan tampilan eksperimen',
            'value' => json_encode(['enabled' => true]),
        ])->assertRedirect();
        $this->assertDatabaseHas('configurations', ['key' => 'experiment.display', 'group' => 'experiment']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'updated', 'entity_type' => 'UiTemplate']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'entity_type' => 'Configuration']);
    }

    public function test_respondent_cannot_access_admin_management(): void
    {
        $respondent = User::create([
            'name' => 'Blocked Respondent',
            'email' => 'blocked@test.local',
            'password' => 'secret',
            'role' => 'respondent',
            'date_of_birth' => '2000-01-01',
            'is_active' => true,
        ]);

        $this->withSession([
            'user_id' => $respondent->id,
            'user_role' => 'respondent',
            'user_name' => $respondent->name,
        ])->get(route('admin.respondents'))->assertForbidden();
    }
}
