<div>
    <h1>Управление категориями</h1>
    <a href="{{ route('admin.categories.create') }}">Добавить категорию</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Slug</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        @foreach($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>{{ $category->name }}</td>
                <td>{{ $category->slug }}</td>
                <td>
                    <a href="{{ route('admin.categories.edit', $category) }}">Редактировать</a>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Удалить категорию?')">Удалить</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $categories->links() }}
</div>
