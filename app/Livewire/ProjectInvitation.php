<?php

namespace App\Livewire;

use App\Livewire\Forms\InvitationForm;
use App\Models\Project;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;

class ProjectInvitation extends Component
{
    public ?Project $project;
    public bool $showModal = false;

    public function mount(Project $project)
    {
        $this->form->setProject($project);
    }

    public function save()
    {
        $this->form->store();
        session()->flash('message', 'Task added successfully.');
        $this->reset('showModal');
    }

    public function delete($id)
    {
        if (auth()->id() != $this->project->user_id) {
            abort(401);
        }
        $user = User::whereId($id)->first();
        $this->project->removeMember($user);;
        session()->flash('message', 'Colabotor removed successfully.');
    }

    #[On('resetModal')]
    public function resetModal(): void
    {
        $this->reset('form', 'showModal');
    }

    public function render()
    {
        return view('livewire.invitation.project-invitation');
    }
}