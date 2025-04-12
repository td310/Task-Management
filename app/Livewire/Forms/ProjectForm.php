<?php
namespace App\Livewire\Forms;

use App\Models\Project;
use App\Services\ChronoService;
use Illuminate\Support\Str;
use Livewire\Form;

class ProjectForm extends Form
{
    public ?Project $project;
    public $name = '';
    public $description = '';
    public $slug = '';

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string',],
        ];
    }

    public function setProject(Project $project)
    {
        $this->project = $project;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->slug = $project->slug;
    }

    public function store()
    {
        $this->validate();
        $chrono = new ChronoService();
        $code = $chrono->generateCode(new Project());
        Project::create([
            'user_id' => auth()->id(),
            'slug' => Str::slug($this->name) . time(),
            'name' => $this->name,
            'code' => $code,
            'description' => $this->description,

        ]);
        $this->reset();
    }

    public function update()
    {
        $this->validate();

        if (auth()->id() != $this->project->user_id) {
            abort(401);
        }

        $this->project->update([
            'slug' => Str::slug($this->name) . time(),
            'name' => $this->name,
            'description' => $this->description,

        ]);
        $this->reset();
    }
}
