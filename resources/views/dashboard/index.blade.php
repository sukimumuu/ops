@extends('layouts.app')
@section('page-title', 'Mihom Dashboard')
@section('content')
@section('content')
    <div class="max-w-2xl mx-auto">

        <x-card-dashboard title="Project Details"
                subtitle="Fill in the information below to create a new project.">
            <x-slot name="actions">
                <x-button-dashboard variant="ghost" size="sm">Cancel</x-button-dashboard>
            </x-slot>

            <form method="POST" action="" class="space-y-5">
                @csrf

                <x-form-text-input-dashboard name="name" label="Project Name" required
                              placeholder="e.g. Website Redesign"
                              :error="$errors->first('name')" />

                <x-form-select-dashboard name="status" label="Status" required
                                    :error="$errors->first('status')">
                    <option value="">Select a status…</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="done">Done</option>
                </x-form-select-dashboard>

                <x-form-text-area-dashboard name="description" label="Description"
                                       hint="A short summary shown on the project card."
                                       :error="$errors->first('description')"></x-form-text-area-dashboard>

                <div class="flex items-center gap-3 pt-2">
                    <x-button-dashboard type="submit" variant="primary">
                        Create Project
                    </x-button>
                    <x-button-dashboard type="button" variant="outline">
                        Save as Draft
                    </x-button>
                </div>
            </form>

            <x-slot name="footer">
                <p class="text-xs text-slate-500">
                    Changes are saved automatically after submission.
                </p>
            </x-slot>
        </x-card-dashboard>

    </div>
@endsection
@endsection