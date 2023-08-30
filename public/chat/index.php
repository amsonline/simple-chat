<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>
<body>
<div class="page">
    <div class="header">
        Chat app
        <div class="logout">
            <img src="/img/logout.png" />
        </div>
    </div>
    <div class="content">
        <div class="sidebar">
            <div class="title">
                Groups
                <div class="create-group" onclick="openCreateGroupDialog()">+</div>
            </div>
            <div class="groups">
                <div id="sample-group-box">
                    <div class="group-name">Group name</div>
                    <div class="group-join">
                        <div class="group-join-button" onclick="joinGroup()">
                            Join
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="messages no-group">
            <div id="sample-message-box">
                <div class="sender">Name</div>
                <div class="content">Hello guys!</div>
                <div class="date">2023-08-27 04:00:00</div>
            </div>
            <div class="no-group-selected">Select a group to view messages</div>
            <div class="messages-list">
            </div>
            <div class="add-message">
                <input type="text" id="messageText" placeholder="Write message to send!" />
                <button onclick="sendMessage()">Send!</button>
            </div>
        </div>
    </div>
</div>
<div class="backdrop">
    <div class="login-box">
        Enter your name:
        <br />
        <input type="text" id="chatName" />
        <br />
        <button onclick="getUserId()">Login/Register</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let activeGroupId = null;
    let userId = localStorage.getItem("userId");
    if (userId != null) {
        loadPage();
    }
    let intervalHandler;
    let lastMessageId = null;

    function loadPage() {
        $(".backdrop").fadeOut();
        $(".page").fadeIn();
        loadGroups();
    }

    function openCreateGroupDialog() {
        const name = prompt("Enter group name");
        if (name) {
            createGroup(name);
        }
    }

    function createGroup(groupName) {
        if (groupName) {
            $.post("/groups", { name: groupName }, function(data, status) {
                if (status === "success") {
                    loadGroups();
                    $("#groupName").val("");
                }
            });
        }
    }

    function getUserId() {
        const name = $("#chatName").val();
        if (name) {
            $.post("/users", { name: name }, function(data, status) {
                if (status === "success") {
                    localStorage.setItem("userId", data.data.user_id);
                    userId = data.data.user_id;
                    loadPage();
                }
            });
        }
    }

    function loadGroups() {
        $.get(`/groups?user_id=${userId}`, function(data, status) {
            if (status === "success") {
                $(".groups .group-box").remove();
                data.data.forEach(group => {
                    var clone = $("#sample-group-box").clone();
                    clone.addClass("group-box");
                    clone.attr("id", `group-${group.id}`);
                    clone.attr("data-id", group.id);
                    clone.find(".group-name").text(group.name);
                    if (group.isJoined == 1) {
                        clone.find(".group-join").remove();
                    }else{
                        clone.find(".group-join-button").data("id", group.id);
                    }
                    clone.appendTo(".groups");
                });
            }
        });
    }

    function setActiveGroup(groupId) {
        console.log(`Setting active group to ${groupId}`);
        activeGroupId = groupId;
        $(".groups .group-box").removeClass("active");
        lastMessageId = null;
        $(".messages-list").empty();
        clearTimeout(intervalHandler);
        if (groupId) {
            $(`#group-${groupId}`).addClass("active");
            $(".messages").removeClass("no-group");
            loadMessages();
        }
    }

    function joinGroup(groupId) {
        if (groupId) {
            $.post(`/groups/${groupId}/join`, { user_id: userId }, function(data, status) {
                if (status === "success") {
                    loadGroups();
                }
            });

            setActiveGroup(groupId);
            loadMessages(groupId);
        }
    }

    function loadMessages() {
        if (activeGroupId == null) {
            return;
        }
        $.get(`/groups/${activeGroupId}/messages` + (lastMessageId ? `?last_message=${lastMessageId}` : ""), function(data, status) {
            if (status === "success") {
                let previousMessageId = null;
                data.data.forEach(message => {
                    var clone = $("#sample-message-box").clone();
                    clone.addClass("message-bubble");
                    if (message.user_id == userId) {
                        clone.addClass("sent");
                    }
                    clone.attr("id", `message-${message.id}`);
                    clone.attr("data-id", message.id);
                    clone.find(".sender").text(message.username);
                    clone.find(".content").text(message.content);
                    clone.find(".date").text(message.timestamp);
                    if (previousMessageId) {
                        clone.appendTo(".messages-list").insertBefore((previousMessageId ? `#message-${previousMessageId}` : ".add-message"));
                    }else {
                        clone.appendTo(".messages-list");
                    }
                    previousMessageId = message.id;
                    if (message.id > lastMessageId) {
                        lastMessageId = message.id;
                    }
                });
            }
        });

        intervalHandler = setTimeout(function(){
            loadMessages();
        }, 10000);
    }

    function sendMessage() {
        const messageText = $("#messageText").val();

        if (messageText && activeGroupId) {
            const requestData = {
                user_id: userId,
                message: messageText
            };

            $.post(`/groups/${activeGroupId}/messages`, requestData, function(data, status) {
                if (status === "success") {
                    loadMessages();
                    $("#messageText").val(""); // Clear message text area
                }
            });
        }
    }

    $(document).ready(function() {
        $(document).on('click', '.group-join-button', function() {
            const groupId = $(this).data("id");
            joinGroup(groupId);
        });

        $(document).on('click', '.group-name', function() {
            setActiveGroup($(this).parent().data("id"));
        });

        $(".logout").click(function(){
            if (confirm("Are you sure you want to logout?")) {
                localStorage.removeItem("userId");
                location.href = location.href;
            }
        });
    });
</script>
</body>
</html>
