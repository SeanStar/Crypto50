jQuery(function($) {
    var last_id = 0,
        update, addMessage, onsuccess, unread = 0,
        $chat = $('.chat'),
        $room = $('.chatbox'),
        $ul, $form = $chat.find('form'),
        $textarea = $form.find('textarea'),
        last_read = parseInt($.cookie('last_read')),
        message_ids = [];
    if (!last_read) {
        last_read = 0;
    }
    $room.mCustomScrollbar({
        scrollInertia: false,
        scrollButtons: {
            enable: false
        },
        callbacks: {
            onScrollStart: function() {
                $ul.data('bottom', false);
            },
            onTotalScroll: function() {
                $ul.data('bottom', true);
            }
        },
        theme: "dark",
        updateOnContentResize: true
    }).on('mousewheel', function(event) {
        event.preventDefault();
        event.stopPropagation();
        return false;
    }).css('position', 'absolute');
    $ul = $chat.find('.mCSB_container');
    onsuccess = function(data) {
        if (!data || data.error || data.length === 0) return;
        var is_new = last_id === 0,
            $ul_clone = $ul.clone(),
            html = '',
            tmp;
        last_id = data[0].id;
        if ($('body').is('.chat-on')) {
            last_read = last_id;
        }
        data = data.reverse();
        $.each(data, function(i, e) {
            tmp = addMessage(e);
            if (typeof tmp !== 'undefined') {
                html += tmp;
            }
        });
        $ul.append(html);
        $room.mCustomScrollbar("update");
        if (is_new || $ul.data('bottom')) {
            $room.mCustomScrollbar("scrollTo", "bottom");
        }
    };
    oncomplete = function() {
        setTimeout(update, 1000);
    }
    update = function() {
        var data = {};
        if (last_id === 0) {
            data.limit = 100;
        } else {
            data.id = last_id;
        }
        $.ajax({
            url: '/api/chat.php',
            type: 'post',
            dataType: 'json',
            data: data,
            success: onsuccess,
            complete: oncomplete
        });
    }
	
    addMessage = function(message) {
        if ($.inArray(message.id, message_ids) !== -1) return;
        message_ids.push(message.id);
        var li = '<li class="chat__entry message-' + message.id + '">' + '<a href="/modals/stats.html?id=' + message.user_id + '" class="action-fancybox"><span>[U]</span></a> ' + '<a class="chat__sender">' + message.username + '</a> ' + '<span class="chat__content">' + message.msg + '</span>' + '<time class="chat__time">' + message.time + '</time>' + '<input type="hidden" name="id" value="' + message.id + '" />' + '</li>';
        return li;
    }

    function send_message(text) {
        $.ajax({
            url: '/api/chat.php',
            type: 'post',
            dataType: 'json',
            data: {
                chat_message: text
            },
            success: onsuccess
        });
    }
    $textarea.keyup(function(event) {
        if (event.which !== 13) return;
        event.preventDefault();
        $form.submit();
    })
    $form.submit(function(event) {
        event.preventDefault();
        send_message($textarea.val());
        $textarea.val('');
        return false;
    })
    $ul.html('');
    update();
});