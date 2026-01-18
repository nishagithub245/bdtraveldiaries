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


    showUsernameSection();



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
            dataType: 'json',
            data: { username: currentUser, title, articletext: text },
            success: function (res) {
                if (res.status === 'success') {
                    $('#article-title').val('');
                    $('#article-text').val('');
                    $('#post-btn').prop('disabled', true);
                    loadArticles();
                }
                else alert('Error: ' + res.message);
            },
            error: function (xhr, status, error) {
                console.error('Post article error:', xhr.responseText);
                alert('Server error occurred while posting article. Check console for details.');
            }
        });
    });

    // Load all articles from DB
    function loadArticles() {
        $.ajax({
            url: 'get_article.php',
            type: 'GET',
            dataType: 'json',
            data: { username: currentUser },
            success: function (articles) {
                if (!Array.isArray(articles)) {
                    console.error('Invalid articles response:', articles);
                    return;
                }
                
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
                            <div class="flag-options" style="display:none;">
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
            },
            error: function (xhr, status, error) {
                console.error('Load articles error:', xhr.responseText);
            }
        });
    }

    // Flag toggle buttons
    $(document).on('click', '.flag-btn', function () {
        const flagOptions = $(this).siblings('.flag-options');
        // Close all other open flag options
        $('.flag-options').not(flagOptions).fadeOut(200);
        flagOptions.fadeToggle(200);
    });
    
    $(document).on('click', '.close-flag', function () {
        $(this).closest('.flag-options').fadeOut(200);
    });

    // REPORT / UPDATE FLAG button
    $(document).on('click', '.report-btn', function () {
        const articleDiv = $(this).closest('.first-article');
        const reportBtn = $(this);
        const flagOptions = articleDiv.find('.flag-options');

        const flagAbusive = articleDiv.find('input[value="abusive"]').is(':checked') ? 1 : 0;
        const flagSpam = articleDiv.find('input[value="spam"]').is(':checked') ? 1 : 0;
        const flagCopyright = articleDiv.find('input[value="copyright"]').is(':checked') ? 1 : 0;

        reportBtn.prop('disabled', true);
        // reportBtn.text('Processing...');

        $.ajax({
            url: 'flag_article.php',
            type: 'POST',
            dataType: 'json',
            data: {
                username: currentUser,
                articlenumber: parseInt(articleDiv.data('articlenumber')),
                flagabusive: flagAbusive,
                flagspam: flagSpam,
                flagcopyright: flagCopyright
            },
            success: function (res) {
                if (res.status === 'success') {
                    alert(res.message);
                    
                    // Update button text based on current flag state
                    const hasAnyFlag = flagAbusive || flagSpam || flagCopyright;
                    reportBtn.text(hasAnyFlag ? 'Update Flag' : 'Report');
                    
                    // Close the flag options
                    flagOptions.fadeOut(200);
                    
                    // Reload articles to update all flag states
                    loadArticles();
                } else {
                    alert('Error: ' + res.message);
                    // Reset button text
                    const hasAnyFlag = flagAbusive || flagSpam || flagCopyright;
                    reportBtn.text(hasAnyFlag ? 'Update Flag' : 'Report');
                }
            },
            error: function (xhr, status, error) {
                console.error('Flag article error:', xhr.responseText);
                alert('Server error occurred while updating flags. Check console for details.');
                // Reset button text
                const hasAnyFlag = flagAbusive || flagSpam || flagCopyright;
                reportBtn.text(hasAnyFlag ? 'Update Flag' : 'Report');
            },
            complete: function () {
                reportBtn.prop('disabled', false);
            }
        });
    });

    // Close flag options when clicking outside
    // $(document).on('click', function (event) {
    //     if (!$(event.target).closest('.article-flagging').length) {
    //         $('.flag-options').fadeOut(200);
    //     }
    // });
});