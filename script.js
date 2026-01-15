$(document).ready(function () {
    let currentUser = localStorage.getItem('username') || '';

    function showUsernameSection() {
        $('#username-section').show();
        $('#articles-section').hide();
    }

    function showArticlesSection() {
        $('#username-section').hide();
        $('#articles-section').show();
    }

    // Page load: check if user signed in
    if (currentUser) {
        $('#signed-user').text(currentUser);
        showArticlesSection();
        loadArticles();
    } else {
        showUsernameSection();
    }

    // Sign in button
    $('#signin-btn').on('click', function () {
        const username = $('#username').val().trim();
        if (!username) return alert("Enter username");
        currentUser = username;
        localStorage.setItem('username', username);
        $('#signed-user').text(username);
        showArticlesSection();
        loadArticles();
    });

    // Enable/disable Post button
    $('#article-title, #article-text').on('input', function () {
        const title = $('#article-title').val().trim();
        const text = $('#article-text').val().trim();
        $('#post-btn').prop('disabled', !(title && text));
    });

    // Post new article
    $('#post-btn').on('click', function () {
        const title = $('#article-title').val().trim();
        const text = $('#article-text').val().trim();
        if (!title || !text || !currentUser) return;

        $.ajax({
            url: 'add_article.php',
            type: 'POST',
            dataType: 'json', // auto parse JSON
            data: { username: currentUser, title, articletext: text },
            success: function (res) {
                if (res.status === 'success') loadArticles();
                else alert('Error: ' + res.message);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Server error occurred while posting article');
            }
        });
    });

    // Load all articles from DB
    function loadArticles() {
        $.getJSON('get_article.php', { username: currentUser }, function (articles) {
            $('#dynamic-articles').empty();
            articles.forEach(article => {
                const flags = article.userFlags || {};
                const articleHTML = `
                <div class="first-article" data-articlenumber="${article.articlenumber}">
                    <div class="first-article-content">
                        <h3>${article.title}</h3>
                        <p class="meta">${article.username} | ${article.publishtime}</p>
                        <p class="text">${article.articletext}</p>
                    </div>
                    <div class="article-flagging">
                        ${article.username !== currentUser ? `<button class="flag-btn">Flag</button>` : ''}
                        <div class="flag-options">
                            <div class="flag-options-content">
                                <label><input type="checkbox" value="abusive" ${flags.abusive ? 'checked' : ''}> Abusive</label>
                                <label><input type="checkbox" value="spam" ${flags.spam ? 'checked' : ''}> Spam</label>
                                <label><input type="checkbox" value="copyright" ${flags.copyright ? 'checked' : ''}> Copyrighted</label>
                            </div>
                            <div class="flag-options-symbols">
                                <button class="close-flag">X</button>
                                <button class="report-btn">${flags.any ? 'Update Flag' : 'Report'}</button>
                            </div>
                        </div>
                    </div>
                </div>`;
                $('#dynamic-articles').append(articleHTML);
            });
        });
    }

    // Flag button toggle
    $(document).on('click', '.flag-btn', function () {
        $(this).siblings('.flag-options').fadeToggle(200);
    });

    $(document).on('click', '.close-flag', function () {
        $(this).closest('.flag-options').fadeOut(200);
    });

    // REPORT / UPDATE FLAG button
    $(document).on('click', '.report-btn', function () {
        const articleDiv = $(this).closest('.first-article');
        const reportBtn = $(this);

        // Get current checkbox state
        const flagAbusive = articleDiv.find('input[value="abusive"]').is(':checked') ? 1 : 0;
        const flagSpam = articleDiv.find('input[value="spam"]').is(':checked') ? 1 : 0;
        const flagCopyright = articleDiv.find('input[value="copyright"]').is(':checked') ? 1 : 0;

        // Temporarily disable button while sending
        reportBtn.prop('disabled', true);

        $.ajax({
            url: 'flag_article.php',
            type: 'POST',
            dataType: 'json', // 👈 ensures JSON parsing
            data: {
                username: currentUser,
                articlenumber: parseInt(articleDiv.data('articlenumber')),
                flagabusive: flagAbusive,
                flagspam: flagSpam,
                flagcopyright: flagCopyright
            },
            success: function (res) {
                if (res.status === 'success') {
                    alert(res.message || 'Flags updated successfully');

                    // Update button text dynamically
                    if (flagAbusive || flagSpam || flagCopyright) {
                        reportBtn.text('Update Flag');
                    } else {
                        reportBtn.text('Report'); // all unchecked
                    }
                } else {
                    alert(res.message || 'Error occurred while updating flags');
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Server error occurred while updating flags');
            },
            complete: function () {
                // Re-enable button so user can click anytime
                reportBtn.prop('disabled', false);
            }
        });
    });
});
