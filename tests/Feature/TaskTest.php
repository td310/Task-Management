<?php

use App\Enums\StatusType;
use App\Livewire\Forms\ProjectForm;
use App\Livewire\Forms\TaskForm;
use App\Livewire\Task\Create;
use App\Livewire\Task\Edit;
use App\Livewire\Task\Index;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;


test('Kiểm tra tất cả các trường đều được điền', function () {
    $user = User::factory()->create();
    $project = Project::factory(['user_id' => $user->id])->create();

    Livewire::actingAs($user)
        ->test(Index::class, ['project' => $project])
        ->set('form.name', '')
        ->set('form.deadline', '')
        ->call('save')
        ->assertHasErrors('form.name')
        ->assertHasErrors('form.deadline');
});

test('Kiểm tra đóng form tạo task', function () {
    $this->actingAs($user = User::factory()->create());
    $this->assertEquals(0, Task::count());
    $current = Carbon::now();
    $project = Project::factory(['user_id' => $user->id])->create();

    Livewire::test(Index::class, ['project' => $project])
        ->set('form.name', 'New Task')
        ->set('form.project_name', $project->id)
        ->set('form.deadline', $current->addDays(rand(1, 10)))
        ->set('form.description', 'new task')
        ->set('form.priority', 'medium')
        ->call('save')
        ->assertSet('showModal', false); 

    $this->assertEquals(1, Task::count());
});

test('Kiểm tra đóng form sửa task', function () {
    $user = User::factory()->create();
    $project = Project::factory(['user_id' => $user->id])->create();
    $task = Task::factory(['user_id' => $user->id, 'project_id' => $project->id])->create();
    $current = Carbon::now();

    Livewire::actingAs($user)
        ->test(Index::class, ['task' => $task, 'project' => $project])
        ->set('form.name', 'New Task')
        ->set('form.project_name', 1)
        ->set('form.status', StatusType::STARTED->value)
        ->set('form.deadline', $current->addDays(rand(1, 10)))
        ->set('form.description', 'new task')
        ->call('save')
        ->assertSee('New Task', $task->name);
});

test('Kiểm tra chức năng thêm mới 1 task', function () {
    $user = User::factory()->create();
    $project = Project::factory(['user_id' => $user->id])->create();
    $deadline = now()->addDays(5);

    Livewire::actingAs($user)
        ->test(Index::class, ['project' => $project])
        ->set('form.name', 'Create Task')
        ->set('form.project_name', $project->id)
        ->set('form.priority', 'high')
        ->set('form.status', StatusType::STARTED->value)
        ->set('form.deadline', $deadline)
        ->set('form.description', 'This is a new task')
        ->call('save');

    $this->assertDatabaseHas('tasks', [
        'name' => 'Create Task',
        'priority' => 'high',
        'status' => StatusType::STARTED->value,
        'description' => 'This is a new task',
    ]);
});

test('Kiểm tra chức năng cập nhật task', function () {
    $user = User::factory()->create();
    $project = Project::factory(['user_id' => $user->id])->create();
    $task = Task::factory(['user_id' => $user->id, 'project_id' => $project->id])->create([
        'name' => 'Updated Task',
        'priority' => 'low',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class, ['project' => $project])
        ->set('form.id', $task->id)
        ->set('form.name', 'Updated Task')
        ->set('form.priority', 'low')
        ->call('save');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'name' => 'Updated Task',
        'priority' => 'low',
    ]);
});

test('Kiểm tra xóa task thành công', function () {
    $user = User::factory()->create();
    $project = Project::factory(['user_id' => $user->id])->create();
    $task = Task::factory(['project_id' => $project->id])->for($user)->create();

    Livewire::actingAs($user)
        ->test(Index::class, ['project' => $project])
        ->call('delete', $task->id)
        ->assertStatus(200);
});

test('Validation lỗi khi deadline là ngày trong quá khứ', function () {
    $user = User::factory()->create();
    $project = Project::factory(['user_id' => $user->id])->create();

    Livewire::actingAs($user)
        ->test(Index::class, ['project' => $project])
        ->set('form.name', 'Test Task')
        ->set('form.priority', 'low')
        ->set('form.deadline', now()->subDay())
        ->call('save')
        ->assertHasErrors(['form.deadline' => 'after_or_equal']);
});
