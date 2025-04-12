<?php

use Laravel\Fortify\Features;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

test('Kiểm tra màn hình đăng nhập có thể hiển thị', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('Kiểm tra người dùng có thể đăng nhập với thông tin hợp lệ', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123')
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('Kiểm tra người dùng không thể đăng nhập với mật khẩu sai', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123')
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('Kiểm tra người dùng có thể đăng nhập với "ghi nhớ đăng nhập"', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123')
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
        'remember' => 'on',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertNotNull($user->fresh()->remember_token);
});

test('Kiểm tra người dùng có thể đăng xuất', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('Kiểm tra có link quên mật khẩu trên màn hình đăng nhập', function () {
    $response = $this->get('/login');

    if (Route::has('password.request')) {
        $response->assertSee(route('password.request'));
    }
});

test('Kiểm tra có link đăng ký trên màn hình đăng nhập nếu tính năng đăng ký được bật', function () {
    $response = $this->get('/login');

    if (Features::enabled(Features::registration()) && Route::has('register')) {
        $response->assertSee(route('register'));
    }
})->skip(function () {
    return ! Features::enabled(Features::registration());
}, 'Tính năng đăng ký không được bật.');