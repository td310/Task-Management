<?php
namespace App\Livewire\Forms;

use App\Enums\StatusType;
use App\Models\Task;
use App\Services\ChronoService;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Support\Str;

class TaskForm extends Form
{
    public ?Task $task;
    public $name = '';
    public $project_name = '';
    public $slug = '';
    public $deadline = '';
    public $priority = 'low';
    public $status = '';
    public $description = '';

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
            'description' => ['nullable',],
        ];
    }
    public function setProjectId($project)
    {
        $this->project_name = $project->id;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;
        $this->name = $task->name;
        $this->project_name = $task->project_id;
        $this->status = $task->status;
        $this->description = $task->description;
        $this->deadline = $task->deadline->format('Y-m-d');
    }

    public function store()
    {
        $this->validate();
        $chrono = new ChronoService();
        $code = $chrono->generateCode(new Task());

        Task::create([
            'user_id' => auth()->id(),
            'slug' => Str::slug($this->name) . time(),
            'code' => $code,
            'name' => $this->name,
            'project_id' => $this->project_name,
            'priority' => $this->priority,
            'status' => StatusType::STARTED->value,
            'deadline' => $this->deadline,
            'description' => $this->description

        ]);
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        if (auth()->id() != $this->task->user_id) {
            abort(401);
        }

        $this->task->update([
            'slug' => Str::slug($this->name) . time(),
            'name' => $this->name,
            'project_id' => $this->project_name,
            'status' => $this->status,
            'priority' => $this->priority,
            'deadline' => $this->deadline,
            'description' => $this->description,

        ]);
        $this->reset();
    }
}
