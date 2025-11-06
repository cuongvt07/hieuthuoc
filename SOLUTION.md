# Giải pháp hoàn chỉnh cho 3 vấn đề

## ✅ Vấn đề 1: Phân trang độc lập
### Giải pháp:
- Thêm `data-block-type="nhom"` và `data-block-type="thuoc"` vào các khối HTML
- Thêm `id="pagination-nhom"` và `id="pagination-thuoc"` cho các container phân trang
- Sử dụng event delegation `$(document).on('click', '.pagination-link')` để bắt sự kiện
- Xác định khối bằng cách kiểm tra `paginationContainer.attr('id')`
- Mỗi khối gọi hàm riêng: `loadNhomThuoc(page)` hoặc `loadThuoc(page)`

### Code thay đổi:
```javascript
$(document).on('click', '.pagination-link', function(e) {
    const page = $(this).data('page');
    const paginationContainer = $(this).closest('.pagination-container');
    const blockType = paginationContainer.attr('id') === 'pagination-nhom' ? 'nhom' : 'thuoc';
    
    if (blockType === 'nhom') {
        currentNhomPage = page;
        loadNhomThuoc(page);
    } else {
        currentThuocPage = page;
        loadThuoc(page);
    }
});
```

## ✅ Vấn đề 2: Giữ filter khi phân trang
### Giải pháp:
- Thu thập tất cả filter parameters (search, nhom_id, kho_id) trong hàm `loadThuoc()`
- Truyền các parameters này vào AJAX request
- Backend ThuocController đã có `->appends($request->query())` để giữ query string trong links

### Code thay đổi:
```javascript
function loadThuoc(page = currentThuocPage) {
    const search = $('#search-thuoc').val();
    const nhomId = $('#filter-nhom').val() || selectedNhomId;
    const khoId = $('#filter-kho').val();
    const data = { page: page };
    
    if (search && search.trim() !== '') data.search = search.trim();
    if (nhomId && nhomId !== '') data.nhom_id = nhomId;
    if (khoId && khoId !== '') data.kho_id = khoId;
    
    $.ajax({
        url: "{{ route('thuoc.list') }}",
        data: data,
        // ...
    });
}
```

## ✅ Vấn đề 3: Phân biệt data source
### Giải pháp:
- `$nhomThuoc`: Dùng cho danh sách nhóm thuốc bên trái (paginated)
- `$nhomThuocData`: Dùng cho dropdown filter (all active records)
- Tạo endpoint mới `/nhom-thuoc/filter-data` để lấy dữ liệu active groups
- Tạo method `getFilterData()` trong NhomThuocController

### Files thay đổi:
1. **NhomThuocController.php**:
```php
public function getFilterData()
{
    $nhomThuocData = NhomThuoc::where('trang_thai', 1)
        ->orderBy('ten_nhom')
        ->get();
    return response()->json(['nhomThuocData' => $nhomThuocData]);
}
```

2. **routes/web.php**:
```php
Route::get('/nhom-thuoc/filter-data', [NhomThuocController::class, 'getFilterData']);
```

3. **index.blade.php (HTML)**:
```blade
<select id="filter-nhom" class="form-select">
    <option value="">-- Tất cả nhóm --</option>
    @foreach ($nhomThuocData as $nhom)
    <option value="{{ $nhom->nhom_id }}">{{ $nhom->ten_nhom }}</option>
    @endforeach
</select>
```

4. **index.blade.php (JavaScript)**:
```javascript
function updateNhomThuocDropdowns() {
    // Cập nhật dropdown filter (chỉ lấy nhóm active)
    $.ajax({
        url: "/nhom-thuoc/filter-data",
        success: function(response) {
            // Update filter dropdown with active groups only
        }
    });
    
    // Cập nhật dropdown trong modal (lấy tất cả nhóm)
    $.ajax({
        url: "/nhom-thuoc/all",
        success: function(response) {
            // Update modal dropdowns with all groups
        }
    });
}
```

## Tóm tắt các thay đổi:
1. ✅ Thêm `data-block-type` và `id` cho pagination containers
2. ✅ Sửa URL endpoints trong `loadNhomThuoc()` và `loadThuoc()` 
3. ✅ Thêm event handler phân trang độc lập
4. ✅ Thu thập và truyền filter parameters khi phân trang
5. ✅ Tạo endpoint mới `/nhom-thuoc/filter-data`
6. ✅ Phân biệt `nhomThuoc` (list) và `nhomThuocData` (filter dropdown)

## Kết quả:
- Phân trang nhóm thuốc và thuốc hoạt động độc lập
- Khi chuyển trang giữ nguyên tất cả filter parameters
- Dropdown filter sử dụng data riêng biệt (chỉ active groups)
- Danh sách sử dụng paginated data
