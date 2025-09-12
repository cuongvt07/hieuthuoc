$('#filter-thuoc').change(function() {
    loadData();
});

$('#searchBtn').click(function() {
    loadData();
});

$('#resetFilterBtn').click(function() {
    $('#filter-thuoc').val('');
    $('#filter-from-date').val('');
    $('#filter-to-date').val('');
    loadData();
});

function loadData() {
    showLoading($('#gia-thuoc-table tbody'));
    
    $.get('/gia-thuoc', {
        thuoc_id: $('#filter-thuoc').val(),
        ngay_bat_dau: $('#filter-from-date').val(),
        ngay_ket_thuc: $('#filter-to-date').val(),
    }, function(response) {
        $('#gia-thuoc-table tbody').html($(response.html).find('tbody').html());
        $('#pagination').html(response.links);
    });
}

// Xử lý form thêm mới
$('#addGiaThuocForm').submit(function(e) {
    e.preventDefault();
    
    const formData = {
        thuoc_id: $('#thuoc_id').val(),
        gia_ban: $('#gia_ban').val().replace(/\D/g, ''),
        ghi_chu: $('#ghi_chu').val()
    };
    
    $.ajax({
        url: '{{ route('gia-thuoc.store') }}',
        type: 'POST',
        data: formData,
        success: function(response) {
            if(response.success) {
                toastr.success(response.message);
                $('#addGiaThuocModal').modal('hide');
                $('#addGiaThuocForm')[0].reset();
                loadData();
            }
        },
        error: function(xhr) {
            if(xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(key => {
                    $(`#${key}_error`).text(errors[key][0]);
                    $(`#${key}`).addClass('is-invalid');
                });
            } else {
                toastr.error('Có lỗi xảy ra. Vui lòng thử lại sau.');
            }
        }
    });
});

// Xử lý mở modal cập nhật
$(document).on('click', '.edit-btn', function() {
    const giaId = $(this).data('id');
    const thuocId = $(this).data('thuoc-id');
    const thuocName = $(this).data('thuoc');
    const giaCu = $(this).data('gia');

    $('#edit_gia_id').val(giaId);
    $('#edit_thuoc_id').val(thuocId);
    $('#edit_thuoc_name').val(thuocName);
    $('#edit_gia_cu').val(Number(giaCu).toLocaleString('vi-VN') + ' đ');
    $('#edit_gia_ban').val('');
    $('#editGiaThuocModal').modal('show');
});

// Xử lý form cập nhật
$('#editGiaThuocForm').submit(function(e) {
    e.preventDefault();
    
    const giaId = $('#edit_gia_id').val();
    const thuocId = $('#edit_thuoc_id').val();
    const ngayBatDau = $('#edit_ngay_bat_dau').val();
    const ngayKetThuc = $('#edit_ngay_ket_thuc').val();
    const formData = {
        _method: 'PUT',
        thuoc_id: thuocId,
        gia_ban: $('#edit_gia_ban').val().replace(/\D/g, ''),
        ngay_bat_dau: ngayBatDau,
        ngay_ket_thuc: ngayKetThuc
    };
    
    $.ajax({
        url: `/gia-thuoc/${giaId}`,
        type: 'POST',
        data: formData,
        success: function(response) {
            if(response.success) {
                toastr.success(response.message);
                $('#editGiaThuocModal').modal('hide');
                $('#editGiaThuocForm')[0].reset();
                loadData();
            }
        },
        error: function(xhr) {
            if(xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(key => {
                    $(`#edit_${key}_error`).text(errors[key][0]);
                    $(`#edit_${key}`).addClass('is-invalid');
                });
            } else {
                toastr.error('Có lỗi xảy ra. Vui lòng thử lại sau.');
            }
        }
    });
});

// Reset form khi đóng modal
$('.modal').on('hidden.bs.modal', function() {
    const form = $(this).find('form');
    form[0].reset();
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
});

// Xử lý phân trang
$(document).on('click', '#pagination a', function(e) {
    e.preventDefault();
    const url = $(this).attr('href');
    $.get(url, function(response) {
        $('#gia-thuoc-table tbody').html($(response.html).find('tbody').html());
        $('#pagination').html(response.links);
    });
});
