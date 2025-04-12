<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use Livewire\Component;
use Illuminate\Support\Collection;

class Dashboard extends Component
{
    public Collection  $tasks;
    public  $project_name;

    public function showTask($project_id)
    {
    }

    public function updatedProjectName(): void
    {
        $this->tasks = Task::with('project')->where([
            'user_id' => auth()->id(),
            'project_id' => $this->project_name,
        ])
            ->orderBy('priority')
            ->get();
    }

    public function render()
    {
        $totalProjects = auth()->user()->accessibleProjects()->count();
        $totalTasks = Task::where('user_id', auth()->id())->count();
        $projects = auth()->user()->accessibleProjects();

        return view('livewire.dashboard', compact('totalProjects', 'totalTasks', 'projects'));
    }
}