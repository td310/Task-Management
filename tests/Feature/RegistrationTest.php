<?php

use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('Kiểm tra màn hình đăng ký có hiển thị', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
})->skip(function () {
    return ! Features::enabled(Features::registration());
}, 'Tính năng đăng ký không được bật.');

test('Kiểm tra người dùng có thể đăng ký', function () {
    $response = $this->post('/register', [
        'name' => 'Người Dùng Test',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('Kiểm tra validation đăng ký thất bại khi thiếu tên', function () {
    $response = $this->post('/register', [
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertGuest();
});

test('Kiểm tra validation đăng ký thất bại khi email không hợp lệ', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('Kiểm tra validation đăng ký thất bại khi email đã tồn tại', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('Kiểm tra validation đăng ký thất bại khi mật khẩu quá ngắn', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('Kiểm tra validation đăng ký thất bại khi mật khẩu không khớp', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password456',
    ]);

    $response->assertSessionHasErrors('password_confirmation');
    $this->assertGuest();
});

test('Kiểm tra có link đăng nhập trên màn hình đăng ký', function () {
    $response = $this->get('/register');

    $response->assertSee(route('login'));
});

test('Kiểm tra có các trường bắt buộc trên form đăng ký', function () {
    $response = $this->get('/register');

    $response->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false);

    if (Jetstream::hasTermsAndPrivacyPolicyFeature()) {
        $response->assertSee('name="terms"', false);
    }
});

test('Kiểm tra sau khi đăng ký thành công, user được chuyển hướng đến dashboard', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('Kiểm tra sau khi đăng ký, thông tin user được lưu đúng trong database', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ];

    $this->post('/register', array_merge($userData, [
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
    ]));

    $this->assertDatabaseHas('users', [
        'name' => $userData['name'],
        'email' => $userData['email'],
    ]);

    $user = User::where('email', $userData['email'])->first();
    $this->assertTrue(Hash::check($userData['password'], $user->password));
});