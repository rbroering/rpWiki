"use strict";

/**
 * 
 */
function showResponse(commentBody, text) {
    const comment   = commentBody;
    const response  = comment.querySelector('.ajax_response_container');
    const response_text = response.querySelector('.ajax_response_text');

    response_text.innerText = text;
    response.style.display = 'inline-block';
    window.setTimeout(() => response.classList.add('active'), 201);
    window.setTimeout(() => response.classList.remove('active'), 6201);
    window.setTimeout(() => response.style.display = 'none', 6402);
}

/**
 * 
 */
function fixTitleAfterEditing(commentBody) {
    const commentContent    = commentBody.find('.comment_content');
    const commentTitle      = commentContent.find('.comment_title').text().trim();

    console.log(commentTitle.length);

    if (commentTitle.length)
        commentContent.removeClass('comment_untitled');
    else
        commentContent.addClass('comment_untitled');
}

/**
 * 
 * @param {} commentBody
 * @param {string} idPage Currently, the page's address is used instead of its ID (@todo)
 * @param {string} idRand 
 * @param {string} ecText 
 * @param {string} ecTitle 
 */
function actionSave(commentBody, idPage, idRand, ecText, ecTitle) {
    /* Empty content */
    if (!ecText.trim().length) {
        showResponse(commentBody.get(0), "Your comment can't be empty.");
    }

    if (ecText.trim() == commentBody.find('.comment_text_backup').html().trim()) {
        showResponse(commentBody.get(0), "There weren't any changes.");
    }

    /* AJAX call for saving the changes */
    $.ajax({
        method: 'POST',
        url:    'senddata.php',
        data:   `category=e__Comment&action=edit&page=${idPage}&pagetype=page&id=${idRand}&c_Title=${ecTitle.trim()}&c_Content=${ecText.trim()}`
    }).done(function(response) {
        switch (response) {
            case 'success':
                showResponse(commentBody.get(0), "Your changes were saved!");
                fixTitleAfterEditing(commentBody);
                commentBody.find('.comment_text_backup').html(ecText);
            break;
            default:
            case 'error_Permission':
                showResponse(commentBody.get(0), "There was an error saving your changes.");
            break;
        }
    }).fail(function() {
        showResponse(commentBody.get(0), "There was an error saving your changes.");
    });
}

/**
 * 
 * @param {} commentBody
 * @param {*} idPage Currently, the page's address is used instead of its ID (@todo)
 * @param {*} idRand 
 */
function actionChangeVisibility(commentBody, idPage, idRand) {
    /* AJAX call for saving the changes */
    $.ajax({
        method: 'POST',
        url:    'senddata.php',
        data:   `category=e__Comment&action=hide&page=${idPage}&pagetype=page&id=${idRand}`
    }).done(function(response) {
        switch (response) {
            case 'success':
                showResponse(commentBody.get(0), "The comment has been hidden.");
            break;
            default:
            case 'error_Permission':
                showResponse(commentBody.get(0), "There was an error hiding the comment.");
            break;
        }
    }).fail(function() {
        showResponse(commentBody.get(0), "There was an error hiding the comment.");
    });
}

$(document).ready(function() {
    $('.comment .action_edit, .comment .action_submit, .comment .action_quit').on('click', function() {
        const commentBody   = $(this).parents('.comment_body');
        const railWrapper   = $(this).parents('.rail_wrapper');
        const idRand        = commentBody.attr('data-comment-id');
        const idPage        = commentBody.attr('data-page-address');
        const editContainer = commentBody.find('.comment_editable_content.user-can-edit');
        
        const ecBoth        = editContainer.find('.comment_text, .comment_title');
        const ecTitle       = editContainer.find('.comment_title');
        const ecText        = editContainer.find('.comment_text');

        var isActive        = editContainer.hasClass('edit-active');

        if (!isActive) {
            ecBoth.attr('contenteditable', 'true');
        } else {
            ecBoth.removeAttr('contenteditable');

            // If quitting, insert old content, else update backup
            if ($(this).hasClass('action_quit')) {
                ecText.html(editContainer.find('.comment_text_backup').html());
            } else {
                actionSave(commentBody, idPage, idRand, ecText.html().trim() || "", ecTitle.text().trim() || "");
            }
        }

        $(railWrapper.find('.switch-text')).each(function(q, elem) {
            window.setTimeout(function() {
                $(elem).toggleClass('switch-text-shown')
            }, 200);
        });

        editContainer.toggleClass('edit-active');
        window.setTimeout(function() {
            railWrapper.toggleClass('scroll')
        }, 200);
    });

    $('.comment_editable_content.user-can-edit .comment_text').each(function() {
        $(this).keydown(function(e) {
            if (e.keyCode === 13) {
                document.execCommand('insertHTML', false, '<br />\r\n');
                return false;
            }
        });

        $(this).on("input inputchange paste", function() {
            var allowed = [
                "B",
                "BR",
                "DIV",
                "EM",
                "I",
                "S",
                "STRONG",
                "U"
            ];

            for (var el of this.getElementsByTagName("*")) {
                var remove = !allowed.includes(el.tagName);

                if (el.tagName == "DIV" && el.attributes.length) remove = true;

                if (remove) el.remove();
            }
        });
    });

    $('.comment .action_hide').on('click', function() {
        const commentBody = $(this).parents('.comment_body');
        const idRand = commentBody.attr('data-comment-id');
        const idPage = commentBody.attr('data-page-address');

        actionChangeVisibility(commentBody, idPage, idRand);
    });

    $('.comment .action_reply').on('click', function() {
        const commentBody = $(this).parents('.comment_body');
        const comment = commentBody.parents('.comment');
        const newreply = comment.get(0).querySelector('.comment_new_reply_editor');
        const idRand = commentBody.attr('data-comment-id');
        const idPage = commentBody.attr('data-page-address');

        $(newreply).slideDown(200);
        newreply.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
