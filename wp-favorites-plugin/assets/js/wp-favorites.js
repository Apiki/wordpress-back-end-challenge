/**
 * WP Favorites Plugin JavaScript
 */

(function($) {
    'use strict';

    // WP Favorites Plugin namespace
    window.WPFavorites = window.WPFavorites || {};

    // Configuration
    WPFavorites.config = {
        restUrl: wpFavorites.restUrl || '/wp-json/wp-favorites/v1',
        nonce: wpFavorites.nonce || '',
        messages: {
            favorited: 'Post added to favorites!',
            unfavorited: 'Post removed from favorites!',
            error: 'An error occurred. Please try again.',
            loginRequired: 'Please log in to favorite posts.'
        }
    };

    // Main class
    WPFavorites.FavoritesButton = function(element, options) {
        this.element = $(element);
        this.options = $.extend({}, WPFavorites.config, options);
        this.postId = this.element.data('post-id');
        this.isFavorited = false;
        this.isLoading = false;

        this.init();
    };

    WPFavorites.FavoritesButton.prototype = {
        init: function() {
            this.bindEvents();
            this.checkFavoriteStatus();
        },

        bindEvents: function() {
            var self = this;
            this.element.on('click', function(e) {
                e.preventDefault();
                self.toggleFavorite();
            });
        },

        checkFavoriteStatus: function() {
            var self = this;
            $.ajax({
                url: self.options.restUrl + '/favorites',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.options.nonce);
                },
                success: function(response) {
                    if (response.success && response.favorites.includes(parseInt(self.postId))) {
                        self.setFavorited(true);
                    }
                },
                error: function() {
                    // Silent fail for status check
                }
            });
        },

        toggleFavorite: function() {
            if (this.isLoading) return;

            if (this.isFavorited) {
                this.unfavorite();
            } else {
                this.favorite();
            }
        },

        favorite: function() {
            var self = this;
            this.setLoading(true);

            $.ajax({
                url: self.options.restUrl + '/favorite',
                method: 'POST',
                data: JSON.stringify({
                    post_id: self.postId
                }),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.options.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        self.setFavorited(true);
                        self.showMessage(self.options.messages.favorited, 'success');
                    } else {
                        self.showMessage(response.message || self.options.messages.error, 'error');
                    }
                },
                error: function(xhr) {
                    var message = self.options.messages.error;
                    if (xhr.status === 401) {
                        message = self.options.messages.loginRequired;
                    }
                    self.showMessage(message, 'error');
                },
                complete: function() {
                    self.setLoading(false);
                }
            });
        },

        unfavorite: function() {
            var self = this;
            this.setLoading(true);

            $.ajax({
                url: self.options.restUrl + '/unfavorite',
                method: 'POST',
                data: JSON.stringify({
                    post_id: self.postId
                }),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.options.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        self.setFavorited(false);
                        self.showMessage(self.options.messages.unfavorited, 'success');
                    } else {
                        self.showMessage(response.message || self.options.messages.error, 'error');
                    }
                },
                error: function() {
                    self.showMessage(self.options.messages.error, 'error');
                },
                complete: function() {
                    self.setLoading(false);
                }
            });
        },

        setFavorited: function(favorited) {
            this.isFavorited = favorited;
            this.element.toggleClass('favorited', favorited);
            
            var icon = this.element.find('.wp-favorites-icon');
            var text = this.element.find('.wp-favorites-text');
            
            if (favorited) {
                icon.text('♥');
                text.text('Favorited');
            } else {
                icon.text('♡');
                text.text('Favorite');
            }
        },

        setLoading: function(loading) {
            this.isLoading = loading;
            this.element.toggleClass('loading', loading);
            this.element.prop('disabled', loading);
        },

        showMessage: function(message, type) {
            var messageElement = $('<div>')
                .addClass('wp-favorites-message ' + type)
                .text(message);

            $('body').append(messageElement);

            setTimeout(function() {
                messageElement.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // jQuery plugin
    $.fn.wpFavorites = function(options) {
        return this.each(function() {
            if (!$(this).data('wp-favorites')) {
                $(this).data('wp-favorites', new WPFavorites.FavoritesButton(this, options));
            }
        });
    };

    // Auto-initialize on document ready
    $(document).ready(function() {
        $('.wp-favorites-button').wpFavorites();
    });

})(jQuery); 