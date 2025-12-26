<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Page – Bangladesh Travel Diaries Website</title>

<style>
body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #dbe9b7;
}

.admin-container {
    width: 100%;
    padding: 15px 20px;
    box-sizing: border-box;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    color: #1f3d1f;
    margin-bottom: 10px;
}

.admin-header-left {
    font-size: 18px;
}

.admin-header-right {
    font-size: 16px;
    text-decoration: underline;
}

.notification {
    background: #c9dd9a;
    padding: 6px 10px;
    margin-bottom: 6px;
    font-size: 14px;
    line-height: 1.4;
    color: #1f3d1f;
}

.notification-time {
    display: inline-block;
    width: 180px;
    font-size: 13px;
}

.notification strong {
    font-weight: bold;
}

.highlight-user {
    color: #0b4f2f;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="admin-container">

    <div class="admin-header">
        <div class="admin-header-left"><u>Flag Reports</ul></div>
        <div class="admin-header-right">Admin Page – Bangladesh Travel Diaries Website</div>
    </div>

    <div id="notifications">
        <!-- Notifications will be inserted here -->
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let lastFlagId = 0;

function loadNotifications() {
    $.post('notifications.php', { lastFlagId: lastFlagId }, function(data) {

        data.forEach(item => {

            $('#notifications').append(`
                <div class="notification">
                    <span class="notification-time">${item.time} GMT</span>
                    ${item.message}
                </div>
            `);

            lastFlagId = item.flagnumber;
        });

    }, 'json');
}

loadNotifications();
setInterval(loadNotifications, 5000);
</script>

</body>
</html>
