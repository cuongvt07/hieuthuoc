document.addEventListener('DOMContentLoaded', function() {
    const notificationDropdown = document.getElementById('notificationsDropdown');
    const notificationsList = document.querySelector('.notification-list');
    const notificationBadge = notificationDropdown.querySelector('.badge');
    
    function loadNotifications() {
        fetch('/api/notifications/unread')
            .then(response => response.json())
            .then(notifications => {
                notificationsList.innerHTML = '';
                
                if (notifications.length === 0) {
                    notificationsList.innerHTML = `
                        <div class="dropdown-item text-center">
                            <div class="text-muted">Không có thông báo mới</div>
                        </div>
                    `;
                    notificationBadge.style.display = 'none';
                } else {
                    notificationBadge.style.display = 'block';
                    notificationBadge.textContent = notifications.length;
                    
                    notifications.forEach(notification => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item notification-item';
                        item.innerHTML = `
                            <div class="d-flex">
                                <div class="me-2">
                                    <i class="bi bi-exclamation-circle-fill text-warning"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">${dayjs(notification.thoi_gian).fromNow()}</div>
                                    <div>${notification.noi_dung}</div>
                                </div>
                            </div>
                        `;
                        
                        item.addEventListener('click', () => markAsRead(notification.thong_bao_id));
                        notificationsList.appendChild(item);
                    });
                }
            })
            .catch(error => console.error('Error loading notifications:', error));
    }
    
    function markAsRead(id) {
        fetch(`/api/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }
    
    // Load notifications initially
    loadNotifications();
    
    // Refresh notifications every minute
    setInterval(loadNotifications, 60000);
});
