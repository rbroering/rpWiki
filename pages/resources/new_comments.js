"use strict";

/**
 * 
 */
function showResponse(comment_body, text = "Lorem ipsum dolor sit amet.") {
    const comment   = comment_body;//document.querySelector(`#comment_${id}`);
    const response  = comment.querySelector('.ajax_response_container');
    const response_text = response.querySelector('.ajax_response_text');

    response_text.innerText = text;
    response.style.display = 'inline-block';
    window.setTimeout(() => response.classList.add('active'), 201);
    window.setTimeout(() => response.classList.remove('active'), 6201);
    window.setTimeout(() => response.style.display = 'none', 6402);
}

function actionSave(comment_body) {
    $.ajax({

    }).done(function() {

    }).catch(function() {
        
    });
}

/**
 * StackOverflow code snippet by Tim Down <96100>
 * https://stackoverflow.com/questions/4811822/#4812022
 */
function getSelectionCharacterOffsetWithin(element) {
    var start = 0;
    var end = 0;
    var doc = element.ownerDocument || element.document;
    var win = doc.defaultView || doc.parentWindow;
    var sel;
    if (typeof win.getSelection != "undefined") {
        sel = win.getSelection();
        if (sel.rangeCount > 0) {
            var range = win.getSelection().getRangeAt(0);
            var preCaretRange = range.cloneRange();
            preCaretRange.selectNodeContents(element);
            preCaretRange.setEnd(range.startContainer, range.startOffset);
            start = preCaretRange.toString().length;
            preCaretRange.setEnd(range.endContainer, range.endOffset);
            end = preCaretRange.toString().length;
        }
    } else if ( (sel = doc.selection) && sel.type != "Control") {
        var textRange = sel.createRange();
        var preCaretTextRange = doc.body.createTextRange();
        preCaretTextRange.moveToElementText(element);
        preCaretTextRange.setEndPoint("EndToStart", textRange);
        start = preCaretTextRange.text.length;
        preCaretTextRange.setEndPoint("EndToEnd", textRange);
        end = preCaretTextRange.text.length;
    }
    return { start: start, end: end };
}

// Move caret to a specific point in a DOM element
// Recursive
function SetCaretPosition(el, pos){
    for (var node of el.childNodes) {
        if (node.nodeType == Node.TEXT_NODE) {
            if (node.length >= pos) {
                var range   = document.createRange(),
                    sel     = window.getSelection();

                range.setStart(node,pos);
                range.collapse(true);

                sel.removeAllRanges();
                sel.addRange(range);

                return -1;
            } else {
                pos -= node.length;
            }
        } else {
            pos = SetCaretPosition(node, pos);

            if (pos == -1) return -1;
        }
    }

    return pos;
}

$(document).ready(function() {
    $('.comment .action_edit, .comment .action_submit, .comment .action_quit').on('click', function() {
        const commentBody   = $(this).parents('.comment_body');
        const railWrapper   = $(this).parents('.rail_wrapper');
        const idRand        = commentBody.attr('data-comment-id');
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
                showResponse(commentBody.get(0), "Your changes were saved!");
                editContainer.find('.comment_text_backup').html(ecText.html());
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
                var offset = getSelectionCharacterOffsetWithin(this).start;
                document.execCommand('insertHTML', false, '<br />');
                SetCaretPosition(this, offset + 1);
                return false;
            }
        });

        this.addEventListener("input", function() {
            var offset = getSelectionCharacterOffsetWithin(this).start;

            this.innerHTML = this.innerHTML.replace(/(\r\n|\r|\n)/g, "<br />");
            this.innerHTML = this.innerHTML.replaceAll(/<(?<tag>\w+)(?<attr> .*?)?>(.*)<\/\k<tag>>/gs, function(match, tag, attr, content) {
                var allowed = [
                    "b",
                    "br",
                    "div",
                    "em",
                    "i",
                    "strong",
                    "u"
                ];

                let alt = allowed.includes(tag);

                if (tag == 'div' && attr && attr.length) alt = false;

                return (alt) ? match : "";
            });

            SetCaretPosition(this, offset);
        });
    });
});
