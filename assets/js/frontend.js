/**
 * Frontend JavaScript for Custom Auth & Lockdown plugin
 * 
 * @package CustomAuthLockdown
 * @author Surefire Studios
 * @link https://www.surefirestudios.io
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Plugin object
    var CAL = {
        init: function() {
            this.bindEvents();
            this.setupFormValidation();
        },

        bindEvents: function() {
            // Login form submission
            $(document).on('submit', '.cal-login-form', this.handleLoginForm);
            
            // Register form submission
            $(document).on('submit', '.cal-register-form', this.handleRegisterForm);
            
            // Forgot password form submission
            $(document).on('submit', '.cal-forgot-password-form', this.handleForgotPasswordForm);
            
            // Password reset form submission
            $(document).on('submit', '.cal-password-reset-form', this.handlePasswordResetForm);
            
            // Logout link clicks
            $(document).on('click', '.cal-logout-link', this.handleLogout);
            
            // Form input focus/blur for better UX
            $(document).on('focus', '.cal-form input', this.handleInputFocus);
            $(document).on('blur', '.cal-form input', this.handleInputBlur);
        },

        setupFormValidation: function() {
            // Real-time validation for password confirmation
            $(document).on('input', 'input[name="password_confirm"]', function() {
                var password = $(this).closest('form').find('input[name="password"]').val();
                var confirmPassword = $(this).val();
                
                if (confirmPassword && password !== confirmPassword) {
                    $(this).addClass('error');
                    CAL.showFieldError($(this), cal_ajax.messages.passwords_dont_match || 'Passwords do not match.');
                } else {
                    $(this).removeClass('error');
                    CAL.clearFieldError($(this));
                }
            });

            // Email validation
            $(document).on('blur', 'input[type="email"]', function() {
                var email = $(this).val();
                if (email && !CAL.isValidEmail(email)) {
                    $(this).addClass('error');
                    CAL.showFieldError($(this), 'Please enter a valid email address.');
                } else {
                    $(this).removeClass('error');
                    CAL.clearFieldError($(this));
                }
            });
        },

        handleLoginForm: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('.cal-submit-btn');
            var formData = CAL.getFormData($form);

            // Validate required fields
            if (!formData.username || !formData.password) {
                CAL.showMessage($form, 'error', 'Please enter both username and password.');
                return;
            }

            CAL.setLoadingState($submitBtn, true);
            CAL.clearMessages($form);

            $.ajax({
                url: cal_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cal_login',
                    nonce: formData.cal_nonce,
                    username: formData.username,
                    password: formData.password,
                    remember: formData.remember ? 1 : 0,
                    redirect_to: formData.redirect_to || ''
                },
                success: function(response) {
                    if (response.success) {
                        CAL.showMessage($form, 'success', response.data.message);
                        
                        // Redirect after success
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                location.reload();
                            }
                        }, 1000);
                    } else {
                        CAL.showMessage($form, 'error', response.data.message);
                    }
                },
                error: function() {
                    CAL.showMessage($form, 'error', 'An error occurred. Please try again.');
                },
                complete: function() {
                    CAL.setLoadingState($submitBtn, false);
                }
            });
        },

        handleRegisterForm: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('.cal-submit-btn');
            var formData = CAL.getFormData($form);

            // Validate required fields
            if (!formData.username || !formData.email || !formData.password) {
                CAL.showMessage($form, 'error', 'Please fill in all required fields.');
                return;
            }

            // Validate password confirmation
            if (formData.password !== formData.password_confirm) {
                CAL.showMessage($form, 'error', 'Passwords do not match.');
                return;
            }

            // Validate email
            if (!CAL.isValidEmail(formData.email)) {
                CAL.showMessage($form, 'error', 'Please enter a valid email address.');
                return;
            }

            CAL.setLoadingState($submitBtn, true);
            CAL.clearMessages($form);

            $.ajax({
                url: cal_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cal_register',
                    nonce: formData.cal_nonce,
                    username: formData.username,
                    email: formData.email,
                    password: formData.password,
                    password_confirm: formData.password_confirm
                },
                success: function(response) {
                    if (response.success) {
                        CAL.showMessage($form, 'success', response.data.message);
                        
                        // Redirect after success
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                location.reload();
                            }
                        }, 1500);
                    } else {
                        CAL.showMessage($form, 'error', response.data.message);
                    }
                },
                error: function() {
                    CAL.showMessage($form, 'error', 'An error occurred. Please try again.');
                },
                complete: function() {
                    CAL.setLoadingState($submitBtn, false);
                }
            });
        },

        handleForgotPasswordForm: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('.cal-submit-btn');
            var formData = CAL.getFormData($form);

            if (!formData.user_login) {
                CAL.showMessage($form, 'error', 'Please enter your username or email address.');
                return;
            }

            CAL.setLoadingState($submitBtn, true);
            CAL.clearMessages($form);

            $.ajax({
                url: cal_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cal_forgot_password',
                    nonce: formData.cal_nonce,
                    user_login: formData.user_login
                },
                success: function(response) {
                    if (response.success) {
                        CAL.showMessage($form, 'success', response.data.message);
                        $form[0].reset();
                    } else {
                        CAL.showMessage($form, 'error', response.data.message);
                    }
                },
                error: function() {
                    CAL.showMessage($form, 'error', 'An error occurred. Please try again.');
                },
                complete: function() {
                    CAL.setLoadingState($submitBtn, false);
                }
            });
        },

        handlePasswordResetForm: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('.cal-submit-btn');
            var formData = CAL.getFormData($form);

            if (!formData.password || !formData.password_confirm) {
                CAL.showMessage($form, 'error', 'Please enter and confirm your new password.');
                return;
            }

            if (formData.password !== formData.password_confirm) {
                CAL.showMessage($form, 'error', 'Passwords do not match.');
                return;
            }

            CAL.setLoadingState($submitBtn, true);
            CAL.clearMessages($form);

            $.ajax({
                url: cal_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cal_reset_password',
                    nonce: formData.cal_nonce,
                    key: formData.key,
                    login: formData.login,
                    password: formData.password,
                    password_confirm: formData.password_confirm
                },
                success: function(response) {
                    if (response.success) {
                        CAL.showMessage($form, 'success', response.data.message);
                        
                        // Redirect after success
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            }
                        }, 2000);
                    } else {
                        CAL.showMessage($form, 'error', response.data.message);
                    }
                },
                error: function() {
                    CAL.showMessage($form, 'error', 'An error occurred. Please try again.');
                },
                complete: function() {
                    CAL.setLoadingState($submitBtn, false);
                }
            });
        },

        handleLogout: function(e) {
            e.preventDefault();
            
            var $link = $(this);
            var redirectUrl = $link.data('redirect') || '';

            $.ajax({
                url: cal_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cal_logout',
                    nonce: cal_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            location.reload();
                        }
                    }
                },
                error: function() {
                    // Fallback to WordPress logout
                    window.location.href = wp_logout_url || '/wp-login.php?action=logout';
                }
            });
        },

        handleInputFocus: function() {
            $(this).removeClass('error');
            CAL.clearFieldError($(this));
        },

        handleInputBlur: function() {
            var $input = $(this);
            var value = $input.val();
            
            // Check required fields
            if ($input.prop('required') && !value) {
                $input.addClass('error');
                CAL.showFieldError($input, 'This field is required.');
            }
        },

        // Utility functions
        getFormData: function($form) {
            var formArray = $form.serializeArray();
            var formData = {};
            
            $.each(formArray, function(i, field) {
                if (formData[field.name]) {
                    if (!formData[field.name].push) {
                        formData[field.name] = [formData[field.name]];
                    }
                    formData[field.name].push(field.value || '');
                } else {
                    formData[field.name] = field.value || '';
                }
            });
            
            // Handle checkboxes
            $form.find('input[type="checkbox"]').each(function() {
                if (!this.checked) {
                    formData[this.name] = false;
                }
            });
            
            return formData;
        },

        showMessage: function($form, type, message) {
            var $messageContainer = $form.find('.cal-form-messages');
            var messageHtml = '<div class="cal-message ' + type + '">' + message + '</div>';
            
            $messageContainer.html(messageHtml);
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: $messageContainer.offset().top - 100
            }, 300);
        },

        clearMessages: function($form) {
            $form.find('.cal-form-messages').empty();
        },

        showFieldError: function($field, message) {
            var $errorContainer = $field.siblings('.cal-field-error');
            
            if ($errorContainer.length === 0) {
                $errorContainer = $('<div class="cal-field-error"></div>');
                $field.after($errorContainer);
            }
            
            $errorContainer.text(message).show();
        },

        clearFieldError: function($field) {
            $field.siblings('.cal-field-error').hide();
        },

        setLoadingState: function($button, loading) {
            if (loading) {
                $button.addClass('loading').prop('disabled', true);
            } else {
                $button.removeClass('loading').prop('disabled', false);
            }
        },

        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        CAL.init();
    });

    // Expose CAL object globally for extensibility
    window.CAL = CAL;

})(jQuery);
