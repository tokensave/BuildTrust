<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_new_users_can_register_and_become_director(): void
    {
        $inn = '1234567890'; // валидный ИНН
        $response = $this->post('/register', [
            'username' => 'TestUser',
            'email' => 'test@example.com',
            'inn' => $inn,
            'company_name' => 'Test Company',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        // Проверим, что компания создана
        $this->assertDatabaseHas('companies', [
            'inn' => $inn,
            'name' => 'Test Company',
        ]);

        // Проверим, что пользователь создан
        $this->assertDatabaseHas('users', [
            'username' => 'TestUser',
            'email' => 'test@example.com',
            'role' => 'director',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_second_user_becomes_manager(): void
    {
        $inn = '1234567890';

        // Создаем первого пользователя
        $this->post('/register', [
            'username' => 'FirstUser',
            'email' => 'first@example.com',
            'inn' => $inn,
            'company_name' => 'Test Company',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        auth()->logout();

        // Регистрируем второго пользователя
        $response = $this->post('/register', [
            'username' => 'SecondUser',
            'email' => 'second@example.com',
            'inn' => $inn,
            'company_name' => 'Ignored Company Name', // company уже есть
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = User::where('email', 'second@example.com')->first();
        $this->assertEquals('manager', $user->role);
        $this->assertEquals('Test Company', $user->company->name);
    }
}
