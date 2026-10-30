@php
    $isEdit = isset($product) && $product !== null;
@endphp

@if ($errors->any())
    <div class="validation-errors" role="alert">
        <strong>Validasi gagal. Perbaiki field berikut:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-grid">
    <label class="field full">
        <span>Nama produk</span>
        <input type="text" name="name" value="{{ old('name', $isEdit ? $product->name : '') }}" required maxlength="255" autocomplete="off">
    </label>

    <label class="field">
        <span>Kategori</span>
        <input type="text" name="category" value="{{ old('category', $isEdit ? $product->category : '') }}" required maxlength="100" list="kategori-demo" autocomplete="off">
        <datalist id="kategori-demo">
            @foreach ($categories as $category)
                <option value="{{ $category }}"></option>
            @endforeach
        </datalist>
    </label>

    <label class="field">
        <span>Harga (Rp)</span>
        <input type="number" name="price" value="{{ old('price', $isEdit ? $product->price : '') }}" required min="0" step="100">
    </label>

    <label class="field">
        <span>Stok</span>
        <input type="number" name="stock" value="{{ old('stock', $isEdit ? $product->stock : '') }}" required min="0" step="1">
    </label>

    <label class="field full">
        <span>Deskripsi (opsional)</span>
        <textarea name="description" rows="4" maxlength="1000">{{ old('description', $isEdit ? $product->description : '') }}</textarea>
    </label>
</div>

<div class="form-actions" style="margin-top:.8rem;">
    <button type="submit" class="btn">{{ $submitLabel }}</button>
    <a class="btn btn-ghost" href="{{ $isEdit ? route('web.products.show', $product->id) : route('web.products.index') }}">Batal</a>
</div>
