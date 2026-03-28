<div class="grid gap-6">
    <div>
        <label class="text-sm font-medium text-slate-700" for="name">Category name</label>
        <input class="form-input" id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700" for="description">Description</label>
        <textarea class="form-textarea" id="description" name="description">{{ old('description', $category->description) }}</textarea>
    </div>
</div>
