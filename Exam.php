composer create-project --prefer-dist laravel/laravel ProductManagement
cd ProductManagement
DB_DATABASE=ProductManagerment
DB_USERNAME=root
DB_PASSWORD=yourpassword
php artisan make:migration create_products_table

Schema::create('products', function (Blueprint $table) {
    $table->increments('id');
    $table->string('ProductType', 3);
    $table->string('ProductCode', 6);
    $table->string('ProductName', 50);
    $table->decimal('Quantity', 8, 2);
    $table->string('Note', 60)->nullable();
    $table->timestamps();
});

php artisan migrate

php artisan make:model Product

php artisan make:controller ProductController --resource

Route::resource('products', ProductController::class);
Route::get('products/report', [ProductController::class, 'report'])->name('products.report');

public function store(Request $request) {
    $request->validate([
        'ProductType' => 'required|regex:/^[A-Za-z0-9]+$/',
        'ProductCode' => 'required|regex:/^[A-Za-z0-9]+$/',
        'ProductName' => 'required|max:50',
        'Quantity' => 'required|numeric',
        'Note' => 'nullable|max:60',
    ]);
    Product::create($request->all());
    return redirect()->route('products.index');
}

public function update(Request $request, $id) {
    $request->validate([
        'ProductType' => 'required|regex:/^[A-Za-z0-9]+$/',
        'ProductCode' => 'required|regex:/^[A-Za-z0-9]+$/',
        'ProductName' => 'required|max:50',
        'Quantity' => 'required|numeric',
        'Note' => 'nullable|max:60',
    ]);
    $product = Product::findOrFail($id);
    $product->update($request->all());
    return redirect()->route('products.index');
}

public function report() {
    $report = Product::select('ProductType', DB::raw('SUM(Quantity) as total_quantity'))
                      ->groupBy('ProductType')
                      ->get();
    return view('products.report', compact('report'));
}

@extends('layout')
@section('content')
<h1>Danh Sách Sản Phẩm</h1>
<a href="{{ route('products.create') }}">Thêm Sản Phẩm</a>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Loại</th>
            <th>Mã</th>
            <th>Tên</th>
            <th>Số Lượng</th>
            <th>Ghi Chú</th>
            <th>Hành Động</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->ProductType }}</td>
            <td>{{ $product->ProductCode }}</td>
            <td>{{ $product->ProductName }}</td>
            <td>{{ $product->Quantity }}</td>
            <td>{{ $product->Note }}</td>
            <td>
                <a href="{{ route('products.edit', $product->id) }}">Chỉnh Sửa</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

@extends('layout')
@section('content')
<h1>Thêm Sản Phẩm</h1>
<form action="{{ route('products.store') }}" method="POST">
    @csrf
    <div>
        <label>Loại</label>
        <input type="text" name="ProductType">
    </div>
    <div>
        <label>Mã</label>
        <input type="text" name="ProductCode">
    </div>
    <div>
        <label>Tên</label>
        <input type="text" name="ProductName">
    </div>
    <div>
        <label>Số Lượng</label>
        <input type="text" name="Quantity">
    </div>
    <div>
        <label>Ghi Chú</label>
        <input type="text" name="Note">
    </div>
    <button type="submit">Thêm</button>
</form>
@endsection

@extends('layout')
@section('content')
<h1>Báo Cáo Sản Phẩm</h1>
<table>
    <thead>
        <tr>
            <th>Loại</th>
            <th>Tổng Số Lượng</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report as $item)
        <tr>
            <td>{{ $item->ProductType }}</td>
            <td>{{ $item->total_quantity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
