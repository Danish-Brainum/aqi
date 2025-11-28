export function initIncomingMessages() {
    const messagesTbody = document.getElementById('messages-tbody');
    const totalCountEl = document.getElementById('messages-total-count');
    const unreadCountEl = document.getElementById('messages-unread-count');
    const incomingMessagesTab = document.querySelector('[data-tab="incoming-messages"]');
    
    if (!messagesTbody) return;

    let refreshInterval = null;
    let lastMessageId = null;
    let isTabActive = false;

    // Track the last message ID when page loads
    const firstRow = messagesTbody.querySelector('tr[data-message-id]');
    if (firstRow) {
        lastMessageId = parseInt(firstRow.getAttribute('data-message-id'));
    }

    /**
     * Fetch and update messages
     */
    async function refreshMessages() {
        try {
            const response = await fetch('/incoming-messages/list');
            const data = await response.json();

            if (data.success && data.messages) {
                updateMessagesTable(data.messages);
                updateCounts(data.totalCount, data.unreadCount);
                updateUnreadBadge(data.unreadCount);
            }
        } catch (error) {
            console.error('Error refreshing messages:', error);
        }
    }

    // Expose refresh function globally
    window.refreshIncomingMessages = refreshMessages;

    /**
     * Update the messages table
     */
    function updateMessagesTable(messages) {
        if (!messages || messages.length === 0) {
            if (messagesTbody.querySelector('tr')) {
                // Only show empty state if table was already populated
                return;
            }
            messagesTbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        No messages received yet. Messages from users will appear here.
                    </td>
                </tr>
            `;
            return;
        }

        // Check if we have new messages (check first message ID)
        const newMessagesExist = messages.length > 0 && 
            (!lastMessageId || messages[0].id > lastMessageId);

        // Update last message ID
        if (messages.length > 0) {
            lastMessageId = messages[0].id;
        }

        // Build table rows
        const rowsHTML = messages.map(message => {
            const readClass = !message.read ? 'bg-indigo-50/50' : '';
            const statusBadge = !message.read 
                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Unread</span>'
                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Read</span>';

            const typeBadges = {
                'text': '💬 Text',
                'image': '🖼️ Image',
                'video': '🎥 Video',
                'audio': '🎵 Audio',
                'document': '📄 Document',
                'location': '📍 Location'
            };

            const typeColors = {
                'text': 'bg-blue-100 text-blue-800',
                'image': 'bg-green-100 text-green-800',
                'video': 'bg-purple-100 text-purple-800',
                'audio': 'bg-yellow-100 text-yellow-800',
                'document': 'bg-orange-100 text-orange-800',
                'location': 'bg-pink-100 text-pink-800'
            };

            const typeLabel = typeBadges[message.type] || message.type;
            const typeColor = typeColors[message.type] || 'bg-slate-100 text-slate-800';

            let messageContent = '';
            if (message.type === 'text') {
                messageContent = escapeHtml(message.message || '').substring(0, 100);
            } else if (message.type === 'location') {
                messageContent = `📍 Location (${parseFloat(message.latitude).toFixed(6)}, ${parseFloat(message.longitude).toFixed(6)})`;
            } else if (message.message) {
                messageContent = escapeHtml(message.message).substring(0, 50) + ' (Caption)';
            } else {
                messageContent = '<span class="text-slate-400">No caption</span>';
            }

            const createdAt = new Date(message.created_at);
            const dateStr = createdAt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const timeStr = createdAt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

            const markAsReadBtn = !message.read 
                ? `<button onclick="markAsRead(${message.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Mark as Read</button>`
                : '';

            return `
                <tr class="hover:bg-slate-50 ${readClass}" data-message-id="${message.id}">
                    <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900">${escapeHtml(message.from)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${typeColor}">
                            ${typeLabel}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-900">${messageContent}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                        ${dateStr}<br>${timeStr}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        ${markAsReadBtn}
                        <button onclick="deleteMessage(${message.id})" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
            `;
        }).join('');

        messagesTbody.innerHTML = rowsHTML;

        // Show notification if new messages arrived
        if (newMessagesExist && isTabActive) {
            showNewMessageNotification(messages.length > 0 ? 1 : 0);
        }
    }

    /**
     * Update count displays
     */
    function updateCounts(totalCount, unreadCount) {
        if (totalCountEl) {
            totalCountEl.textContent = totalCount || 0;
        }
        if (unreadCountEl) {
            unreadCountEl.textContent = unreadCount || 0;
        }
    }

    /**
     * Update unread badge on tab
     */
    function updateUnreadBadge(unreadCount) {
        if (!incomingMessagesTab) return;

        let badge = incomingMessagesTab.querySelector('.bg-red-600');
        
        if (unreadCount > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full';
                incomingMessagesTab.appendChild(badge);
            }
            badge.textContent = unreadCount;
        } else {
            if (badge) {
                badge.remove();
            }
        }
    }

    /**
     * Show notification when new messages arrive
     */
    function showNewMessageNotification(count) {
        // Optional: Show a toast notification
        // You can customize this notification style
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        notification.textContent = `📩 ${count} new message${count > 1 ? 's' : ''} received!`;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Start auto-refresh (only when tab is active)
     */
    function startAutoRefresh() {
        if (refreshInterval) return;
        
        // Refresh immediately when tab becomes active
        refreshMessages();
        
        // Then refresh every 3 seconds
        refreshInterval = setInterval(() => {
            refreshMessages();
        }, 3000);
    }

    /**
     * Stop auto-refresh
     */
    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    /**
     * Check if incoming messages tab is active
     */
    function checkTabActive() {
        const incomingMessagesSection = document.getElementById('incoming-messages');
        isTabActive = incomingMessagesSection && !incomingMessagesSection.classList.contains('hidden');
        
        if (isTabActive) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    }

    // Monitor tab changes
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            setTimeout(checkTabActive, 100); // Small delay to let tab switch complete
        });
    });

    // Check initial state
    checkTabActive();

    // Also check periodically in case tab is shown/hidden by other means
    setInterval(checkTabActive, 1000);

    // Manual refresh button
    const refreshBtn = document.getElementById('refresh-messages-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            refreshMessages();
        });
    }
}

// Global functions for inline onclick handlers
window.markAsRead = function(messageId) {
    fetch(`/incoming-messages/${messageId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh messages instead of reloading page
            const refreshFn = window.refreshIncomingMessages;
            if (refreshFn) refreshFn();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark message as read');
    });
};

window.deleteMessage = function(messageId) {
    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }
    
    fetch(`/incoming-messages/${messageId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh messages instead of reloading page
            const refreshFn = window.refreshIncomingMessages;
            if (refreshFn) refreshFn();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete message');
    });
};

