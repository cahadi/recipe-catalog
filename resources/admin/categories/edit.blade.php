<div>
    <h1>Новая категория</h1>
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        <div>
            <label for="name">Название категории</label>
            <input type="text" id="name" name="name" value="{{old('name')}}" required>
            @error('name')
        </div>
        <button type="submit">Сохранить</button>
        <a href="{{ route('admin.categories.index') }}">Отмена</a>
    </form>
</div>
