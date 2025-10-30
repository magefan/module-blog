/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
(function () {
    'use strict';
    
    var msgLifetime = 4000;
    var $hd = document.querySelector('#post-comments');
    var getMessageHtml = function (msg, type) {
        var h = document.createElement('div');
        h.classList.add('message-' + type, type, 'message');
        h.innerHTML = '<div>' + msg + '</div>';
        return h;
    };

    var processError = function ($form, msg) {
        $form.querySelector('[type=submit]').removeAttribute('disabled');
        var $h = getMessageHtml(msg, 'error');
        $form.insertBefore($h, $form.children[0]);
        setTimeout(function () {
            if ($h.parentNode !== null) {
                $h.parentNode.removeChild($h);
            }
        }, msgLifetime);
    };

    var processSuccess = function ($form, msg) {
        $form.querySelector('[type=submit]').removeAttribute('disabled');
        var $h = getMessageHtml(msg, 'success');
        $form.parentElement.insertBefore($h, $form.parentElement.children[0]);
        $form.style.display = 'none';
        setTimeout(function () {
            if ($h.parentNode !== null) {
                $h.parentNode.removeChild($h);
            }
        }, msgLifetime);
    };

    Array.prototype.forEach.call($hd.querySelectorAll('form'), function(el, i) {
        if (el.dataset.submitAttached === 'true') return; 

        el.addEventListener('submit', function (event) {
            event.preventDefault();
            var $form = event.target;

            if ($form.classList.contains('comment-form-blog-recaptcha')){
                $form.querySelector('button[type=submit]').setAttribute('disabled', 'disabled');
            }
            fetch($form.getAttribute('action'), {
                method: 'POST',
                body: new URLSearchParams(new FormData($form)).toString(),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (response) {
                if (response.ok) {
                    return response.json();
                }
                return Promise.reject(response);
            }).then(function (response) {
                if (response.success) {
                    processSuccess($form, response.message);
                } else {
                    processError($form, response.message);
                }
            }).catch(function (error) {
                console.warn(error);
            });

            return false;
        });
        el.dataset.submitAttached = 'true';
    });


    Array.prototype.forEach.call($hd.querySelectorAll('.more-comments-action'), function(el, i) {
        el.addEventListener('click', function (event) {
            event.preventDefault();
            let loadMore = event.target

            if (!event.target.dataset.comment) {
                loadMore = event.target.parentElement;
            }

            let id = loadMore.dataset.comment

            var comments = document.querySelectorAll('.c-comment-parent-' + id);

            Array.prototype.forEach.call(comments, function (el, i) {
                fadeIn(el);
            });

            loadMore.style.display = 'none';
            return false;
        });
    });

    function fadeIn(element, duration = 800) {
        element.style.display = '';
        element.style.opacity = 0;
        var last = +new Date();
        var tick = function() {
            element.style.opacity = +element.style.opacity + (new Date() - last) / duration;
            last = +new Date();
            if (+element.style.opacity < 1) {
                (window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16);
            }
        };
        tick();
    }

    $hd.querySelector('form textarea').addEventListener('click', function (event) {
        if (event.target.closest('form').querySelector('.c-btn-hld')) {
            event.target.closest('form').querySelector('.c-btn-hld').classList.remove('c-btn-hld');
        }
        if (event.target.closest('form').parentElement) {
            event.target.closest('form').parentElement.classList.remove('no-active');
        }
    });

    var $rf = document.querySelector('#c-replyform-comment');


    Array.prototype.forEach.call(document.querySelectorAll('.reply-action'), function(el, i) {
        if (el.dataset.clickAttached === 'true') return;
        el.addEventListener('click', function (event) {
            event.preventDefault();
            let id = event.target.dataset.comment

            $rf.style.display = 'none';

            /*document.querySelector('.c-post-'+id).append($rf);*/
            event.target.parentElement.insertAdjacentElement('afterend', $rf);

            $rf.querySelector('.refresh-value').value = '';
            $rf.querySelector('.refresh-value').innerHTML = '';
            $rf.querySelector('[name=parent_id]').value = id;
            $rf.querySelector('form').style.display = '';
            fadeIn($rf);

            return false;
        });
        el.dataset.clickAttached = 'true';
    });

    if ($hd.querySelector('.reply-cancel-action')) {
        $hd.querySelector('.reply-cancel-action').addEventListener('click', function (event) {
            $rf.style.display = 'none';
        });
    }
})();
