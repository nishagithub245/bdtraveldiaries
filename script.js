$(document).ready(function () {

    // Get current user from localStorage
    let currentUser = localStorage.getItem('username') || '';

    // Functions to show/hide sections
    function showUsernameSection() {
        $('#username-section').removeClass('hidden').css('pointer-events', 'auto');
        $('#articles-section').addClass('hidden').css('pointer-events', 'none');
    }

    function showArticlesSection() {
        $('#username-section').addClass('hidden').css('pointer-events', 'none');
        $('#articles-section').removeClass('hidden').css('pointer-events', 'auto');
    }

    // Always show username section first on page load
    showUsernameSection();

    // Sign in
    $('#signin-btn').on('click', function () {
        const username = $('#username').val().trim();
        if (!username) {
            alert('Please enter a username');
            return;
        }
        currentUser = username;
        localStorage.setItem('username', username);
        $('#signed-user').text(username);
        showArticlesSection();
        loadArticles();
        $('#post-btn').prop('disabled', true); // Initially disabled until inputs are filled
    });

    // Enable/disable Post button based on inputs
    function checkPostButton() {
        const title = $('#article-title').val().trim();
        const text = $('#article-text').val().trim();
        $('#post-btn').prop('disabled', !(title && text));
    }

    $('#article-title, #article-text').on('input', checkPostButton);

    // Post new article
    $('#post-btn').on('click', function () {
        const title = $('#article-title').val().trim();
        const text = $('#article-text').val().trim();

        if (!title || !text) return;
        if (!currentUser) {
            alert('Please sign in first');
            return;
        }

        $.post('add_article.php', {
            username: currentUser,
            title: title,
            articletext: text
        }, function (response) {
            try {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    const newArticleId = res.id;
                    $('#article-title').val('');
                    $('#article-text').val('');
                    $('#post-btn').prop('disabled', true);

                    const newArticle = $(`
                        <div class="first-article" data-articlenumber="${newArticleId}" data-author="${currentUser}">
                            <div class="first-article-content">
                                <h3>${title}</h3>
                                <p class="meta">${currentUser} | ${new Date().toLocaleString('en-GB')} GMT</p>
                                <p class="text">${text}</p>
                            </div>
                            <div class="article-flagging">
                                <div class="flag-options">
                                    <div class="flag-options-content">
                                        <label><input type="checkbox" value="abusive"> Abusive</label>
                                        <label><input type="checkbox" value="spam"> Spam</label>
                                        <label><input type="checkbox" value="copyright"> Copyrighted</label>
                                    </div>
                                    <div class="flag-options-symbols">
                                        <button class="close-flag">X</button>
                                        <button class="report-btn">Report</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);

                    // Hide flag button for own article
                    newArticle.find(".flag-btn").remove();
                    $('#dynamic-articles').prepend(newArticle.hide().fadeIn(300));
                } else {
                    alert('Error posting article: ' + res.message);
                }
            } catch (e) {
                console.error("Invalid response from add_article.php:", response);
                alert('Error posting article. Check console.');
            }
        });
    });

    // Toggle flag options
    $(document).on('click', '.flag-btn', function () {
        $(this).siblings('.flag-options').fadeToggle(200);
    });

    // Close flag box
    $(document).on('click', '.close-flag', function () {
        $(this).closest('.flag-options').fadeOut(200);
    });

    // Submit flag
    $(document).on('click', '.report-btn', function () {
        const articleDiv = $(this).closest('.first-article');
        $.post('flag_article.php', {
            username: currentUser,
            articlenumber: articleDiv.data('articlenumber'),
            flagabusive: articleDiv.find('input[value="abusive"]').is(':checked') ? 1 : 0,
            flagspam: articleDiv.find('input[value="spam"]').is(':checked') ? 1 : 0,
            flagcopyright: articleDiv.find('input[value="copyright"]').is(':checked') ? 1 : 0
        }, function (response) {
            try {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    alert('Flag submitted');
                    articleDiv.find('.report-btn').prop('disabled', true).text('Flagged');
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (e) {
                console.error("Invalid response from flag_article.php:", response);
                alert('Error submitting flag. Check console.');
            }
        });
    });

    // Load articles from DB
    function loadArticles() {
        $.getJSON('get_articles.php', function (data) {
            $('#dynamic-articles').empty();
            data.forEach(article => {
                const newArticle = $(`
                    <div class="first-article" data-articlenumber="${article.articlenumber}" data-author="${article.username}">
                        <div class="first-article-content">
                            <h3>${article.title}</h3>
                            <p class="meta">${article.username} | ${article.publishtime}</p>
                            <p class="text">${article.articletext}</p>
                            <p class="flag-count">Flags: ${article.flag_count}</p>
                        </div>
                        <div class="article-flagging">
                            <button class="flag-btn">Flag</button>
                            <div class="flag-options">
                                <div class="flag-options-content">
                                    <label><input type="checkbox" value="abusive"> Abusive</label>
                                    <label><input type="checkbox" value="spam"> Spam</label>
                                    <label><input type="checkbox" value="copyright"> Copyrighted</label>
                                </div>
                                <div class="flag-options-symbols">
                                    <button class="close-flag">X</button>
                                    <button class="report-btn">Report</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                // Hide flag button if the article belongs to current user
                if (article.username === currentUser) {
                    newArticle.find(".flag-btn").remove();
                }

                $('#dynamic-articles').append(newArticle);
            });
        });
    }

});
