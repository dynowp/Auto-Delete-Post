jQuery(document).ready(function($) {
    'use strict';
    
    // Tab functionality
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        
        // Remove active class from all tabs and content
        $('.nav-tab').removeClass('nav-tab-active');
        $('.adp-tab-content').removeClass('adp-tab-active');
        
        // Add active class to clicked tab and corresponding content
        $(this).addClass('nav-tab-active');
        $(target).addClass('adp-tab-active');
    });
    
    // Clear logs functionality
    $('#clear-logs').on('click', function() {
        if (!confirm(adpAjax.confirmClearLogs)) {
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).text('Clearing...');
        
        $.ajax({
            url: adpAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'adp_clear_logs',
                nonce: adpAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Logs cleared successfully!', 'success');
                    refreshLogs();
                } else {
                    showNotice('Failed to clear logs: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while clearing logs.', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text('Clear Logs');
            }
        });
    });
    
    // Download logs functionality
    $('#download-logs').on('click', function() {
        var downloadUrl = adpAjax.ajaxurl + '?action=adp_download_logs&nonce=' + adpAjax.nonce;
        window.location.href = downloadUrl;
    });
    
    // Reset statistics functionality
    $('#reset-statistics').on('click', function() {
        if (!confirm(adpAjax.confirmResetStats)) {
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).text('Resetting...');
        
        $.ajax({
            url: adpAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'adp_reset_statistics',
                nonce: adpAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Statistics reset successfully!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotice('Failed to reset statistics: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while resetting statistics.', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text('Reset Statistics');
            }
        });
    });
    
    // Refresh logs functionality
    $('#refresh-logs').on('click', function() {
        refreshLogs();
    });
    
    // Refresh statistics functionality
    $('#refresh-statistics').on('click', function() {
        refreshStatistics();
    });
    
    // Form validation
    $('form').on('submit', function() {
        var isValid = true;
        var errors = [];
        
        // Validate numeric inputs
        $('input[type="number"]').each(function() {
            var $input = $(this);
            var value = parseInt($input.val());
            var min = parseInt($input.attr('min'));
            var max = parseInt($input.attr('max'));
            
            if (isNaN(value) || value < min || (max && value > max)) {
                isValid = false;
                var fieldName = $input.closest('tr').find('th').text();
                errors.push(fieldName + ' must be between ' + min + ' and ' + (max || 'unlimited'));
            }
        });
        
        if (!isValid) {
            showNotice('Please fix the following errors:\n' + errors.join('\n'), 'error');
            return false;
        }
        
        return true;
    });
    
    // Toggle switch animations
    $('.adp-toggle input').on('change', function() {
        var $toggle = $(this).closest('.adp-toggle');
        if ($(this).is(':checked')) {
            $toggle.addClass('adp-toggle-checked');
        } else {
            $toggle.removeClass('adp-toggle-checked');
        }
    });
    
    // Initialize toggle states
    $('.adp-toggle input:checked').each(function() {
        $(this).closest('.adp-toggle').addClass('adp-toggle-checked');
    });
    
    // Auto-refresh status every 30 seconds when on status tab
    var statusRefreshInterval;
    
    function startStatusRefresh() {
        if ($('#status').hasClass('adp-tab-active')) {
            statusRefreshInterval = setInterval(function() {
                refreshStatus();
            }, 30000);
        }
    }
    
    function stopStatusRefresh() {
        if (statusRefreshInterval) {
            clearInterval(statusRefreshInterval);
        }
    }
    
    $('.nav-tab').on('click', function() {
        stopStatusRefresh();
        if ($(this).attr('href') === '#status') {
            setTimeout(startStatusRefresh, 100);
        }
    });
    
    // Start refresh if status tab is active on load
    if ($('#status').hasClass('adp-tab-active')) {
        startStatusRefresh();
    }
    
    // Helper functions
    function showNotice(message, type) {
        var noticeClass = 'notice notice-' + type;
        var $notice = $('<div class="' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.wrap h1').after($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    function refreshLogs() {
        var $button = $('#refresh-logs');
        var $container = $('#logs-container');
        
        $button.addClass('adp-loading-btn').prop('disabled', true);
        $container.addClass('adp-loading');
        
        $.ajax({
            url: adpAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'adp_refresh_logs',
                nonce: adpAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateLogsContainer(response.data.logs);
                    showNotice('Logs atualizados com sucesso!', 'success');
                } else {
                    showNotice('Erro ao atualizar logs: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('Erro ao conectar com o servidor.', 'error');
            },
            complete: function() {
                $button.removeClass('adp-loading-btn').prop('disabled', false);
                $container.removeClass('adp-loading');
            }
        });
    }
    
    function refreshStatistics() {
        var $button = $('#refresh-statistics');
        var $container = $('#statistics-container');
        
        $button.addClass('adp-loading-btn').prop('disabled', true);
        $container.addClass('adp-loading');
        
        $.ajax({
            url: adpAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'adp_refresh_statistics',
                nonce: adpAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatisticsContainer(response.data.statistics);
                    showNotice('Estatísticas atualizadas com sucesso!', 'success');
                } else {
                    showNotice('Erro ao atualizar estatísticas: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('Erro ao conectar com o servidor.', 'error');
            },
            complete: function() {
                $button.removeClass('adp-loading-btn').prop('disabled', false);
                $container.removeClass('adp-loading');
            }
        });
    }
    
    function refreshStatus() {
        var $statusGrid = $('#status-container');
        $statusGrid.addClass('adp-loading');
        
        $.ajax({
            url: adpAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'adp_refresh_status',
                nonce: adpAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatusGrid(response.data.status);
                }
            },
            complete: function() {
                $statusGrid.removeClass('adp-loading');
            }
        });
    }
    
    function updateLogsContainer(logs) {
        var $container = $('#logs-container');
        
        if (!logs || logs.length === 0) {
            $container.html('<p class="adp-no-logs">Nenhum log disponível.</p>');
            return;
        }
        
        var tableHtml = '<table class="wp-list-table widefat fixed striped">' +
            '<thead><tr><th>Timestamp</th><th>Level</th><th>Message</th></tr></thead><tbody>';
        
        logs.forEach(function(log) {
            var matches = log.match(/\[(.*?)\] \[(.*?)\] (.*)/);
            var timestamp = matches ? matches[1] : '';
            var level = matches ? matches[2] : 'INFO';
            var message = matches ? matches[3] : log;
            var levelClass = 'adp-log-' + level.toLowerCase();
            
            tableHtml += '<tr>' +
                '<td>' + escapeHtml(timestamp) + '</td>' +
                '<td><span class="adp-log-level ' + levelClass + '">' + escapeHtml(level) + '</span></td>' +
                '<td>' + escapeHtml(message) + '</td>' +
                '</tr>';
        });
        
        tableHtml += '</tbody></table>';
        $container.html(tableHtml);
    }
    
    function updateStatisticsContainer(statistics) {
        var $container = $('#statistics-container');
        
        var statsHtml = '<div class="adp-stat-item">' +
            '<h3>Posts Deleted</h3>' +
            '<span class="adp-stat-number">' + numberFormat(statistics.posts_deleted) + '</span>' +
            '</div>' +
            '<div class="adp-stat-item">' +
            '<h3>Comments Deleted</h3>' +
            '<span class="adp-stat-number">' + numberFormat(statistics.comments_deleted) + '</span>' +
            '</div>' +
            '<div class="adp-stat-item">' +
            '<h3>Categories Deleted</h3>' +
            '<span class="adp-stat-number">' + numberFormat(statistics.categories_deleted) + '</span>' +
            '</div>' +
            '<div class="adp-stat-item">' +
            '<h3>Tags Deleted</h3>' +
            '<span class="adp-stat-number">' + numberFormat(statistics.tags_deleted) + '</span>' +
            '</div>' +
            '<div class="adp-stat-item">' +
            '<h3>Total Executions</h3>' +
            '<span class="adp-stat-number">' + numberFormat(statistics.total_executions) + '</span>' +
            '</div>' +
            '<div class="adp-stat-item">' +
            '<h3>Last Execution</h3>' +
            '<span class="adp-stat-value">' + formatTimestamp(statistics.last_execution_time) + '</span>' +
            '</div>';
        
        $container.html(statsHtml);
    }
    
    function updateStatusGrid(status) {
        // Atualizar o grid de status se necessário
        // Esta função pode ser expandida conforme necessário
    }
    
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function numberFormat(num) {
        return parseInt(num).toLocaleString();
    }
    
    function formatTimestamp(timestamp) {
        if (!timestamp || timestamp === '0000-00-00 00:00:00') {
            return 'Never';
        }
        return timestamp;
    }
    
    // Confirmation dialogs for dangerous actions
    $('input[name="auto_delete_post_settings[enabled]"]').on('change', function() {
        if ($(this).is(':checked')) {
            var confirmed = confirm('Are you sure you want to enable the deletion process? This will start automatically deleting content based on your settings.');
            if (!confirmed) {
                $(this).prop('checked', false);
                $(this).closest('.adp-toggle').removeClass('adp-toggle-checked');
            }
        }
    });
    
    $('input[name="auto_delete_post_settings[posts][delete_attachments]"]').on('change', function() {
        if ($(this).is(':checked')) {
            var confirmed = confirm('Warning: This will permanently delete all images and files attached to posts. This action cannot be undone. Are you sure?');
            if (!confirmed) {
                $(this).prop('checked', false);
                $(this).closest('.adp-toggle').removeClass('adp-toggle-checked');
            }
        }
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+S to save settings
        if (e.ctrlKey && e.which === 83) {
            e.preventDefault();
            if ($('#settings').hasClass('adp-tab-active')) {
                $('form').submit();
            }
        }
        
        // Ctrl+R to refresh logs
        if (e.ctrlKey && e.which === 82) {
            e.preventDefault();
            if ($('#logs').hasClass('adp-tab-active')) {
                refreshLogs();
            }
        }
    });
    
    // Tooltips for status badges
    $('.adp-status-badge').each(function() {
        var $badge = $(this);
        var isEnabled = $badge.hasClass('adp-status-enabled');
        var tooltip = isEnabled ? 'This feature is currently active' : 'This feature is currently inactive';
        $badge.attr('title', tooltip);
    });
    
    // Real-time validation feedback
    $('input[type="number"]').on('input', function() {
        var $input = $(this);
        var value = parseInt($input.val());
        var min = parseInt($input.attr('min'));
        var max = parseInt($input.attr('max'));
        
        $input.removeClass('adp-input-error adp-input-valid');
        
        if (isNaN(value) || value < min || (max && value > max)) {
            $input.addClass('adp-input-error');
        } else {
            $input.addClass('adp-input-valid');
        }
    });
    
    // Date filter functionality
    $('input[name="auto_delete_post_settings[posts][date_filter_enabled]"]').on('change', function() {
        var $dateFields = $(this).closest('.adp-card').find('input[type="date"], input[name*="date_filter_mode"]');
        if ($(this).is(':checked')) {
            $dateFields.prop('disabled', false).closest('tr').show();
        } else {
            $dateFields.prop('disabled', true).closest('tr').hide();
        }
    });
    
    // User filter functionality
    $('input[name="auto_delete_post_settings[posts][user_filter_enabled]"]').on('change', function() {
        var $userFields = $(this).closest('.adp-card').find('.adp-user-selection, input[name*="user_filter_mode"]');
        if ($(this).is(':checked')) {
            $userFields.prop('disabled', false).closest('tr').show();
        } else {
            $userFields.find('input').prop('disabled', true);
            $userFields.closest('tr').hide();
        }
    });
    
    // Date validation
    $('input[type="date"]').on('change', function() {
        var $startDate = $('input[name="auto_delete_post_settings[posts][date_start]"]');
        var $endDate = $('input[name="auto_delete_post_settings[posts][date_end]"]');
        
        if ($startDate.val() && $endDate.val()) {
            var startDate = new Date($startDate.val());
            var endDate = new Date($endDate.val());
            
            $startDate.removeClass('adp-input-error');
            $endDate.removeClass('adp-input-error');
            
            if (startDate > endDate) {
                $startDate.addClass('adp-input-error');
                $endDate.addClass('adp-input-error');
                showNotice('Start date must be before end date', 'error');
            }
        }
    });
    
    // User selection helpers
    $('.adp-user-selection').each(function() {
        var $container = $(this);
        
        // Add select all/none buttons
        var $controls = $('<div class="adp-user-controls" style="margin-bottom: 10px;"></div>');
        var $selectAll = $('<button type="button" class="button button-small">Select All</button>');
        var $selectNone = $('<button type="button" class="button button-small">Select None</button>');
        
        $controls.append($selectAll).append(' ').append($selectNone);
        $container.before($controls);
        
        $selectAll.on('click', function() {
            $container.find('input[type="checkbox"]').prop('checked', true);
        });
        
        $selectNone.on('click', function() {
            $container.find('input[type="checkbox"]').prop('checked', false);
        });
    });
    
    // Initialize filter states on page load
    function initializeFilterStates() {
        // Date filter
        var $dateFilterEnabled = $('input[name="auto_delete_post_settings[posts][date_filter_enabled]"]');
        if (!$dateFilterEnabled.is(':checked')) {
            $dateFilterEnabled.closest('.adp-card').find('input[type="date"], input[name*="date_filter_mode"]').prop('disabled', true).closest('tr').hide();
        }
        
        // User filter
        var $userFilterEnabled = $('input[name="auto_delete_post_settings[posts][user_filter_enabled]"]');
        if (!$userFilterEnabled.is(':checked')) {
            $userFilterEnabled.closest('.adp-card').find('.adp-user-selection input, input[name*="user_filter_mode"]').prop('disabled', true).closest('tr').hide();
        }
    }
    
    // Initialize on page load
    initializeFilterStates();
});