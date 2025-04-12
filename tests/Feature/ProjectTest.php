<?php

use App\Livewire\Forms\ProjectForm;
use App\Livewire\Project\Create;
use App\Livewire\Project\Edit;
use App\Livewire\Project\Index;
use App\Livewire\Task\Index as TaskIndex;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('Kiểm tra màn hình danh sách project có thể hiển thị với component Index', function () {
    $this->actingAs($user = User::factory()->create());

    $response = $this->get('/projects');
    $response->assertSeeLivewire(Index::class);
    $response->assertStatus(200);
});

test('Kiểm tra màn hình tạo project có thể hiển thị với component Create', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/projects/create');
    $response->assertSeeLivewire(Create::class);
    $response->assertStatus(200);
});

test('Kiểm tra màn hình chỉnh sửa project có thể hiển thị với component Edit', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory(['user_id' => $user->id])->create();

    $response = $this->get('/projects/' . $project->id . '/edit');
    $response->assertSeeLivewire(Edit::class, ['project' => $project]);
    $response->assertStatus(200);
});

test('Kiểm tra bắt buộc nhập tất cả các trường khi tạo project', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.name', '')
        ->set('form.description', '')
        ->call('save')
        ->assertHasErrors('form.name');
});

test('Kiểm tra chuyển hướng về danh sách project sau khi tạo thành công', function () {
    $this->actingAs($user = User::factory()->create());
    $this->assertEquals(0, Project::count());

    Livewire::test(Create::class)
        ->set('form.name', 'New Project')
        ->set('form.description', 'Living the lavender description')
        ->call('save')
        ->assertRedirect('/projects');

    $this->assertEquals(1, Project::count());
});

test('Kiểm tra chuyển hướng về danh sách project sau khi chỉnh sửa thành công', function () {
    $user = User::factory()->create();
    $project = Project::factory(['user_id' => $user->id])->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['project' => $project])
        ->set('form.name', 'Living the lavender life')
        ->set('form.description', 'Living the lavender description')
        ->call('save')
        ->assertRedirect('/projects');
});

test('Kiểm tra người dùng không thể chỉnh sửa project của người khác', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->for($stranger)->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['project' => $project])
        ->set('form.name', 'Living the lavender life')
        ->set('form.description', 'Living the lavender description')
        ->call('save')
        ->assertUnauthorized();
});

test('Kiểm tra có thể xoá project thuộc sở hữu người dùng', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('delete', $project->id)
        ->assertStatus(200);
});

test('Kiểm tra không thể xoá project của người khác', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $project = Project::factory()->for($stranger)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('delete', $project->id)
        ->assertUnauthorized();
});

test('Kiểm tra có thể xem chi tiết project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory(['user_id' => $user->id])->create();

    $response = $this->get('/projects/' . $project->id);
    $response->assertSeeLivewire(TaskIndex::class, ['project' => $project]);
    $response->assertStatus(200);
});
