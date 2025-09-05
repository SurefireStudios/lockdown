/**
 * Admin JavaScript for Custom Auth & Lockdown plugin
 */

(function($) {
    'use strict';

    var CAL_Admin = {
        init: function() {
            this.setupTabs();
            this.setupFormHandling();
            this.setupQuickActions();
        },

        setupTabs: function() {
            // Tab switching functionality
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                var targetTab = $(this).attr('href');
                
                // Update active tab
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Show target content
                $('.tab-content').hide();
                $(targetTab).show();
                
                // Save active tab in localStorage
                localStorage.setItem('cal_active_tab', targetTab);
            });

            // Restore active tab from localStorage
            var activeTab = localStorage.getItem('cal_active_tab');
            if (activeTab && $(activeTab).length) {
                $('.nav-tab[href="' + activeTab + '"]').click();
            }
        },

        setupFormHandling: function() {
            // Enhanced form submission with AJAX
            $('#cal-settings-form').on('submit', function(e) {
                var $form = $(this);
                var $submitButton = $form.find('input[type="submit"]');
                
                // Add loading state
                CAL_Admin.setLoadingState($submitButton, true);
                
                // Form will submit normally, but we enhance UX
                setTimeout(function() {
                    CAL_Admin.setLoadingState($submitButton, false);
                }, 2000);
            });

            // Page selection enhancements
            $('select[name*="custom_"][name*="_page"]').on('change', function() {
                var $select = $(this);
                var pageId = $select.val();
                var fieldName = $select.attr('name');
                
                if (pageId) {
                    CAL_Admin.showPagePreview(pageId, fieldName);
                }
            });

            // Bulk page selection
            $('#cal-select-all-pages').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('input[name="cal_options[allowed_pages][]"]').prop('checked', isChecked);
            });

            // Lockdown toggle with confirmation
            $('input[name="cal_options[lockdown_enabled]"]').on('change', function() {
                var $checkbox = $(this);
                var isEnabled = $checkbox.is(':checked');
                
                if (isEnabled) {
                    var confirmation = confirm('Are you sure you want to enable site lockdown? This will restrict access to your website for non-logged-in users.');
                    if (!confirmation) {
                        $checkbox.prop('checked', false);
                    }
                }
            });
        },

        setupQuickActions: function() {
            // Quick toggle for lockdown in admin bar
            if (typeof window.wp !== 'undefined' && window.wp.adminbar) {
                this.addAdminBarQuickToggle();
            }

            // Test login form button
            $('.cal-test-login-form').on('click', function(e) {
                e.preventDefault();
                CAL_Admin.testLoginForm();
            });

            // Clear cache button
            $('.cal-clear-cache').on('click', function(e) {
                e.preventDefault();
                CAL_Admin.clearCache();
            });

            // User approval/rejection buttons
            $('.cal-approve-user').on('click', function(e) {
                e.preventDefault();
                var userId = $(this).data('user-id');
                CAL_Admin.approveUser(userId, $(this));
            });

            $('.cal-reject-user').on('click', function(e) {
                e.preventDefault();
                var userId = $(this).data('user-id');
                CAL_Admin.rejectUser(userId, $(this));
            });

            // Bulk approval functionality
            $('#cal-bulk-select-all').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.cal-user-checkbox').prop('checked', isChecked);
                CAL_Admin.updateBulkActionButtons();
            });

            $('.cal-user-checkbox').on('change', function() {
                CAL_Admin.updateBulkActionButtons();
                
                // Update select all checkbox
                var totalCheckboxes = $('.cal-user-checkbox').length;
                var checkedCheckboxes = $('.cal-user-checkbox:checked').length;
                $('#cal-bulk-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            });

            $('.cal-bulk-approve').on('click', function(e) {
                e.preventDefault();
                CAL_Admin.bulkApproveUsers();
            });

            $('.cal-bulk-reject').on('click', function(e) {
                e.preventDefault();
                CAL_Admin.bulkRejectUsers();
            });

            // Migration button
            $('.cal-migrate-users').on('click', function(e) {
                e.preventDefault();
                CAL_Admin.migrateExistingUsers();
            });

            // Emergency disable button
            $('.cal-emergency-disable').on('click', function(e) {
                e.preventDefault();
                CAL_Admin.emergencyDisable();
            });
        },

        addAdminBarQuickToggle: function() {
            // This would be implemented with WordPress admin bar API
            // For now, just add a quick toggle in the admin
        },

        testLoginForm: function() {
            var loginPageId = $('select[name="cal_options[custom_login_page]"]').val();
            
            if (!loginPageId) {
                alert('Please select a custom login page first.');
                return;
            }

            // Open login page in new tab
            var loginUrl = this.getPageUrl(loginPageId);
            window.open(loginUrl, '_blank');
        },

        clearCache: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cal_clear_cache',
                    nonce: cal_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        CAL_Admin.showNotice('Cache cleared successfully!', 'success');
                    } else {
                        CAL_Admin.showNotice('Failed to clear cache.', 'error');
                    }
                },
                error: function() {
                    CAL_Admin.showNotice('An error occurred while clearing cache.', 'error');
                }
            });
        },

        approveUser: function(userId, $button) {
            if (!confirm('Are you sure you want to approve this user?')) {
                return;
            }

            var originalText = $button.text();
            CAL_Admin.setLoadingState($button, true);
            $button.text('Approving...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cal_approve_user',
                    user_id: userId,
                    nonce: window.cal_ajax ? window.cal_ajax.nonce : ''
                },
                success: function(response) {
                    if (response.success) {
                        CAL_Admin.showNotice('User approved successfully!', 'success');
                        // Remove the row or update the status
                        var $row = $button.closest('tr');
                        if ($row.length) {
                            $row.fadeOut(function() {
                                $row.remove();
                                // Check if table is empty
                                if ($('.cal-pending-users-list tbody tr').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            location.reload();
                        }
                    } else {
                        CAL_Admin.showNotice(response.data || 'Failed to approve user.', 'error');
                        $button.text(originalText);
                        CAL_Admin.setLoadingState($button, false);
                    }
                },
                error: function() {
                    CAL_Admin.showNotice('An error occurred while approving user.', 'error');
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                }
            });
        },

        rejectUser: function(userId, $button) {
            if (!confirm('Are you sure you want to reject this user? This action cannot be undone.')) {
                return;
            }

            var originalText = $button.text();
            CAL_Admin.setLoadingState($button, true);
            $button.text('Rejecting...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cal_reject_user',
                    user_id: userId,
                    nonce: window.cal_ajax ? window.cal_ajax.nonce : ''
                },
                success: function(response) {
                    if (response.success) {
                        CAL_Admin.showNotice('User rejected successfully.', 'success');
                        // Remove the row or update the status
                        var $row = $button.closest('tr');
                        if ($row.length) {
                            $row.fadeOut(function() {
                                $row.remove();
                                // Check if table is empty
                                if ($('.cal-pending-users-list tbody tr').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            location.reload();
                        }
                    } else {
                        CAL_Admin.showNotice(response.data || 'Failed to reject user.', 'error');
                        $button.text(originalText);
                        CAL_Admin.setLoadingState($button, false);
                    }
                },
                error: function() {
                    CAL_Admin.showNotice('An error occurred while rejecting user.', 'error');
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                }
            });
        },

        updateBulkActionButtons: function() {
            var checkedBoxes = $('.cal-user-checkbox:checked');
            var hasSelection = checkedBoxes.length > 0;
            
            $('.cal-bulk-approve, .cal-bulk-reject').prop('disabled', !hasSelection);
        },

        bulkApproveUsers: function() {
            var selectedUsers = $('.cal-user-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedUsers.length === 0) {
                alert('Please select users to approve.');
                return;
            }

            var confirmMessage = 'Are you sure you want to approve ' + selectedUsers.length + ' user(s)?';
            if (!confirm(confirmMessage)) {
                return;
            }

            var $button = $('.cal-bulk-approve');
            var originalText = $button.text();
            CAL_Admin.setLoadingState($button, true);
            $button.text('Approving...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cal_bulk_approve_users',
                    user_ids: selectedUsers,
                    nonce: window.cal_ajax ? window.cal_ajax.nonce : ''
                },
                success: function(response) {
                    if (response.success) {
                        CAL_Admin.showNotice(response.data.message, 'success');
                        // Remove approved rows
                        $('.cal-user-checkbox:checked').closest('tr').fadeOut(function() {
                            $(this).remove();
                            if ($('.cal-pending-users-list tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                        CAL_Admin.updateBulkActionButtons();
                        $('#cal-bulk-select-all').prop('checked', false);
                    } else {
                        CAL_Admin.showNotice(response.data || 'Failed to approve users.', 'error');
                    }
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                },
                error: function() {
                    CAL_Admin.showNotice('An error occurred while approving users.', 'error');
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                }
            });
        },

        bulkRejectUsers: function() {
            var selectedUsers = $('.cal-user-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedUsers.length === 0) {
                alert('Please select users to reject.');
                return;
            }

            var confirmMessage = 'Are you sure you want to reject ' + selectedUsers.length + ' user(s)? This action cannot be undone.';
            if (!confirm(confirmMessage)) {
                return;
            }

            var $button = $('.cal-bulk-reject');
            var originalText = $button.text();
            CAL_Admin.setLoadingState($button, true);
            $button.text('Rejecting...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cal_bulk_reject_users',
                    user_ids: selectedUsers,
                    nonce: window.cal_ajax ? window.cal_ajax.nonce : ''
                },
                success: function(response) {
                    if (response.success) {
                        CAL_Admin.showNotice(response.data.message, 'success');
                        // Remove rejected rows
                        $('.cal-user-checkbox:checked').closest('tr').fadeOut(function() {
                            $(this).remove();
                            if ($('.cal-pending-users-list tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                        CAL_Admin.updateBulkActionButtons();
                        $('#cal-bulk-select-all').prop('checked', false);
                    } else {
                        CAL_Admin.showNotice(response.data || 'Failed to reject users.', 'error');
                    }
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                },
                error: function() {
                    CAL_Admin.showNotice('An error occurred while rejecting users.', 'error');
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                }
            });
        },

        migrateExistingUsers: function() {
            var confirmMessage = 'This will set all existing users without approval status to "approved". Are you sure you want to continue?';
            if (!confirm(confirmMessage)) {
                return;
            }

            var $button = $('.cal-migrate-users');
            var originalText = $button.text();
            CAL_Admin.setLoadingState($button, true);
            $button.text('Migrating...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cal_migrate_existing_users',
                    nonce: window.cal_ajax ? window.cal_ajax.nonce : ''
                },
                success: function(response) {
                    if (response.success) {
                        CAL_Admin.showNotice(response.data.message, 'success');
                        // Hide the migration section or reload to show updated status
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        CAL_Admin.showNotice(response.data || 'Migration failed.', 'error');
                    }
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                },
                error: function() {
                    CAL_Admin.showNotice('An error occurred during migration.', 'error');
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                }
            });
        },

        emergencyDisable: function() {
            var confirmMessage = 'This will turn off both "Require Admin Approval" and "Disable WP Login Access" to resolve login issues. Continue?';
            if (!confirm(confirmMessage)) {
                return;
            }

            var $button = $('.cal-emergency-disable');
            var originalText = $button.text();
            CAL_Admin.setLoadingState($button, true);
            $button.text('Disabling...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cal_emergency_disable',
                    nonce: window.cal_ajax ? window.cal_ajax.nonce : ''
                },
                success: function(response) {
                    if (response.success) {
                        CAL_Admin.showNotice(response.data.message, 'success');
                        // Reload page to show updated settings
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        CAL_Admin.showNotice(response.data || 'Emergency disable failed.', 'error');
                    }
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                },
                error: function() {
                    CAL_Admin.showNotice('An error occurred during emergency disable.', 'error');
                    $button.text(originalText);
                    CAL_Admin.setLoadingState($button, false);
                }
            });
        },

        showPagePreview: function(pageId, fieldName) {
            // Show a preview of the selected page
            var $previewContainer = $('.cal-page-preview[data-field="' + fieldName + '"]');
            
            if ($previewContainer.length === 0) {
                $previewContainer = $('<div class="cal-page-preview" data-field="' + fieldName + '"></div>');
                $('select[name="' + fieldName + '"]').after($previewContainer);
            }

            if (pageId) {
                var pageUrl = this.getPageUrl(pageId);
                var previewHtml = '<div class="cal-preview-info">' +
                    '<p><strong>Preview:</strong> <a href="' + pageUrl + '" target="_blank">View Page</a></p>' +
                    '<p><em>Make sure to add the appropriate shortcode to this page.</em></p>' +
                '</div>';
                
                $previewContainer.html(previewHtml).show();
            } else {
                $previewContainer.hide();
            }
        },

        getPageUrl: function(pageId) {
            // This would need to be passed from PHP
            return window.cal_admin && window.cal_admin.site_url ? 
                window.cal_admin.site_url + '/?page_id=' + pageId : 
                '/?page_id=' + pageId;
        },

        setLoadingState: function($element, loading) {
            if (loading) {
                $element.prop('disabled', true);
                
                if (!$element.find('.cal-spinner').length) {
                    $element.append(' <span class="cal-spinner"></span>');
                }
            } else {
                $element.prop('disabled', false);
                $element.find('.cal-spinner').remove();
            }
        },

        showNotice: function(message, type) {
            var noticeClass = 'notice notice-' + type + ' is-dismissible';
            var noticeHtml = '<div class="' + noticeClass + '"><p>' + message + '</p></div>';
            
            $('.wrap h1').after(noticeHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.notice.is-dismissible').fadeOut();
            }, 5000);
        },

        // Validation helpers
        validateSettings: function() {
            var errors = [];
            
            // Check if lockdown is enabled but no pages are allowed
            var lockdownEnabled = $('input[name="cal_options[lockdown_enabled]"]').is(':checked');
            var allowedPages = $('input[name="cal_options[allowed_pages][]"]:checked').length;
            
            if (lockdownEnabled && allowedPages === 0) {
                errors.push('Lockdown is enabled but no pages are allowed. Users will not be able to access any content.');
            }

            // Check if custom pages are selected but shortcodes might be missing
            var customLoginPage = $('select[name="cal_options[custom_login_page]"]').val();
            if (customLoginPage) {
                // We can't easily check for shortcode presence without AJAX
                // This could be enhanced with a server-side check
            }

            return errors;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        CAL_Admin.init();
    });

    // Form submission validation
    $(document).on('submit', '#cal-settings-form', function(e) {
        var errors = CAL_Admin.validateSettings();
        
        if (errors.length > 0) {
            var confirmMessage = 'The following issues were detected:\n\n' + 
                errors.join('\n') + 
                '\n\nDo you want to continue anyway?';
                
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Expose admin object for extensibility
    window.CAL_Admin = CAL_Admin;

})(jQuery);
