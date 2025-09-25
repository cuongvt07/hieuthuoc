# Don Ban Le (Retail Order) UI Improvements

I've implemented the requested improvements to the "Don Ban Le" (Retail Order) interface. Here are the key changes:

## 1. Direct Product Addition from Product Table

- Products are now displayed in a table after selection from the dropdown
- Users can now click directly on a product row to add it to the cart without needing an additional button
- Products are shown with both original unit and retail unit options when available
- Users can easily see which products are available and their stock quantities

## 2. In-Line Cart Item Editing

- Quantity and tax rate fields are now editable directly in the cart
- Click on any quantity or tax field to edit its value
- Changes are validated to ensure quantities don't exceed available stock
- Cart totals are automatically recalculated when values change

## 3. Unit Selection from Product Table

- Both units (don_vi_goc and don_vi_ban) are displayed as separate rows in the product table
- Users can choose which unit they want when selecting a product
- Pricing automatically adjusts based on the selected unit
- Product table clearly shows which row corresponds to which unit

## 4. Stock Availability Checking

- System validates stock availability before adding products to the cart
- When increasing quantity, checks are performed to prevent exceeding available stock
- User receives clear error messages if they try to add more than available stock
- Stock information is displayed in the product listing

## 5. Bootstrap Icons Integration

- All icons have been updated to use Bootstrap Icons (bi bi-*)
- Examples: 
  - bi-arrow-left instead of fas fa-arrow-left
  - bi-search instead of fas fa-search
  - bi-save instead of fas fa-save
  - bi-plus-circle and bi-minus-circle for quantity controls
  - bi-trash for delete items

## Additional Improvements

- Better visual styling for editable fields
- More intuitive plus/minus controls for quantity adjustment
- Cleaner cart interface with better spacing and alignment
- Improved mobile responsiveness

## Usage Instructions

1. **Search for a product** using the select dropdown
2. **View available products** in the table below the search
3. **Click on any product** to add it directly to your cart
4. **Edit quantities** directly in the cart by clicking on the quantity field
5. **Adjust tax rates** by clicking on the percentage
6. **Use +/- buttons** to fine-tune quantities
7. **Remove items** using the trash icon
8. **Complete the order** when all items are added

These changes create a more streamlined, user-friendly interface that should significantly improve the user experience when creating retail orders.



- lô thuốc : giữ nguyên (hsd của thuốc)
- thuốc : 2 option filter :  1 trạng thái hsd (số lượng)  ;  sort bán chạy/ bán ế (top 5) - (số đơn, doanh số)
- kho : 2 option filter : 1 tất cả : giữ nguyên ; theo từng kho (list ds thuốc : sl , đơn vị auto gốc)
- khách hàng :  2 option filter: sort top 5 ông mua nhiều nhất ; theo từng thằng (Mã KH	Tên khách hàng	Số đơn hàng	Tổng SL mua	Giá trị mua)  // sắp done

				
giá thuốc : list full theo DB : , có bộ lọc giữ nguyên --> list ds   //  done

lịch sử tồn kho (ls truy xuất) :  loại thay đổi : bán , điều chỉnh

NavBar : Danh mục -> Qly thuốc
	 Đối tác -> Qly danh mục : qly ncc ; khách hàng ; nhân sự (xg cuối)
	 Kho & hàng : Qly nhập bán : qly kho , qly phiếu nhập, qly đơn bán, qly lô  // done navbar
	 BÁO CÁO : báo cáo lô thuốc , thuốc  , kho  , khách hàng , truy xuất (module : báo cáo ls truy xuất tồn:
 filter : chọn loại truy xuất : bán --> đơn
				điều chỉnh lô --> tăng ; giảm )
	
Dashboard : thêm khối doanh số bán , tổng nhập hàng ,khối sẽ có filter  từ ngày này - ngày này  // done cần chỉnh thêm  cho đẹp
thay Thuốc sắp hết hàng = top sp bán chạy bán ế (3-5-10 + thời gian theo tháng/năm)
top khách hàng mua nhiều nhất (3-5-10 + thời gian theo tháng/năm)

dược sĩ : dash , khách hàng , qly đơn bán.  // done

Quản Lý Kho : về dạng table list ( sl hiện tại cảu kho) --> chi tiết --> thuốc list -- list lô của thuốc  // done

Quản Lý Phiếu Nhập : auto trạng thái chờ xác nhận --> duyệt -> hoàn thành   //  done

Đình chỉnh  nhóm thuốc --> done

Phiếu nhập : // done


Báo Cáo Khách Hàng : cột sp  =  mã đơn , số lượng sp, ngày tạo đơn , bỏ đơn giá // done

Báo Cáo Thuốc :  báo cáo doanh thu theo thuốc  : filter (thuốc ) + thời gian   // done

Quản lý đơn bán lẻ : Trạng thái --> tạo đơn --> chờ hoàn thành --> hoàn thành   // done

Doanh số bán theo tháng (2025) : lọc theo năm  // done

phân quyền : dược sĩ + Quản Lý Thuốc & Nhóm Thuốc/Quản Lý Giá Thuốc / quản lú danh mục   --> Quản Lý Nhân Sự sửa thông tin chính nó + xem chi tiết / quản lý khách hàng   // done


Phân bố thuốc theo kho (bổ lọc theo kho - mặc định kho đầu tiên)  // done

Quản lý danh mục : k có xoá chỉ có đình chỉ // done


// nếu đơn bán + phiếu nhập đều chờ xác nhận thì sẽ phải làm luồng lưu tạm thông tin --> báo xác nhận hoàn thành thì mới update số lượng tồn kho  // done

cái biểu đồ tròn ở dashboard thì sẽ realtime tồn kho  // 
