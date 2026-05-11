@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'pages-content'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head admin-page-head--settings">
            <div>
                <h1 class="admin-page-title admin-page-title--settings">Update Homepage Content</h1>
            </div>

            <div class="admin-breadcrumb admin-breadcrumb--settings">
                <a href="{{ route('admin.dashboard') }}">Home</a>
                <span>/</span>
                <span>Update Content</span>
            </div>
        </section>

        <section class="panel admin-settings-panel">
            <div class="admin-settings-panel-bar">Homepage Content Management</div>

            <form class="admin-settings-form" method="post" action="{{ route('admin.pages-content.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="admin-settings-field">
                    <label class="admin-settings-label" for="hero_title">Hero Header Title</label>
                    <input
                        class="admin-settings-input"
                        id="hero_title"
                        type="text"
                        name="hero_title"
                        value="{{ old('hero_title', $homepageContent->hero_title) }}"
                        required
                    >
                </div>

                <div class="admin-settings-field">
                    <label class="admin-settings-label" for="hero_description">Hero Header Description</label>
                    <textarea
                        class="admin-settings-textarea"
                        id="hero_description"
                        name="hero_description"
                        rows="3"
                        required
                    >{{ old('hero_description', $homepageContent->hero_description) }}</textarea>
                </div>

                <div class="admin-settings-field">
                    <label class="admin-settings-label" for="hero_image">Hero Image (1280 x 720)</label>
                    <input
                        class="admin-settings-file"
                        id="hero_image"
                        type="file"
                        name="hero_image"
                        accept=".jpg,.jpeg,.png,.webp,image/*"
                    >

                    @if($homepageContent->heroImageUrl())
                        <div class="admin-settings-preview">
                            <p class="admin-settings-help">Current hero image</p>
                            <img src="{{ $homepageContent->heroImageUrl() }}" alt="Current homepage hero image">
                        </div>
                    @endif
                </div>

                <div class="admin-settings-actions">
                    <button type="submit" class="admin-primary-pill">Update Homepage</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
