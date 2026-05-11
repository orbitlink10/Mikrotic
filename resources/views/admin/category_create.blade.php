@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'categories'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Create Category</h1>
                <p class="admin-page-copy">Add a new category or sub category for the admin catalog.</p>
            </div>
        </section>

        <section class="panel admin-form-panel admin-list-panel">
            <form class="form-grid" method="post" action="{{ route('admin.categories.store') }}">
                @csrf
                <label>
                    Name
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </label>
                <label>
                    Parent Category
                    <select name="parent_id">
                        <option value="">Top Level Category</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id', $defaultParentId) == $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-form-full">
                    Image URL
                    <input type="url" name="image_url" value="{{ old('image_url') }}">
                </label>
                <button type="submit">Save Category</button>
            </form>
        </section>
    </div>
</div>
@endsection
