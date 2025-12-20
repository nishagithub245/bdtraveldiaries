$(document).ready(function () {


    let currentUser = '';

    // Always show the username section first
    $('#username-section').removeClass('hidden');
    $('#articles-section').addClass('hidden');

    // If a username exists in localStorage, pre-fill the input
    // const savedUser = localStorage.getItem('username');
    // if(savedUser) {
    //     $('#username').val(savedUser);
    // }

    // Sign in
    $('#signin-btn').on('click', function () {
        const username = $('#username').val().trim();
        if (!username) return;

        currentUser = username;
        localStorage.setItem('username', username);

        $('#signed-user').text(username);
        $('#username-section').addClass('hidden');
        $('#articles-section').removeClass('hidden');

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
        if (!title || !text) return;

        if (!currentUser) {
            alert('Please sign in first');
            return;
        }

        // Get current time
        const now = new Date();
        const timeString = now.toLocaleString('en-GB') + ' GMT';

        // Send to DB
        $.post('add_article.php', {
            username: currentUser,
            articletext: title + ": " + text
        }, function(response) {
            if(response.trim() === 'success') {
                // Reset inputs
                $('#article-title').val('');
                $('#article-text').val('');
                $('#post-btn').prop('disabled', true);

                // Add the new article immediately to DOM (live insert)

               



      const newArticle = $(`
    <div class="first-article">
        <div class="first-article-content">
            <h3>${title}</h3>
            <p class="meta">${currentUser} | ${timeString}</p>
            <p class="text">${text}</p>
        </div>
        <div class="article-flagging">
            <button class="flag-btn">Flag</button>
            <div class="flag-options">
                <div class="flag-options-content">
                    <label><input type="checkbox"> Abusive</label>
                    <label><input type="checkbox"> Spam</label>
                    <label><input type="checkbox"> Copyrighted</label>
                </div>
                <div class="flag-options-symbols">
                    <button class="close-flag">X</button>
                    <button class="report-btn">Report</button>
                </div>
            </div>
        </div>
    </div>
`);
          $('#dynamic-articles').prepend(newArticle.hide().fadeIn(300));
 } else {
               alert('Error posting article');
           }
       });
     });


    //             const newArticle = `
    //                 <div class="first-article">
    //                     <div class="first-article-content">
    //                         <h3>${title}</h3>
    //                         <p class="meta">${currentUser} | ${timeString}</p>
    //                         <p class="text">${text}</p>
    //                     </div>
    //                     <div class="article-flagging">
    //                         <button class="flag-btn">Flag</button>
    //                         <div class="flag-options" style="display:none;">
    //                             <div class="flag-options-content">
    //                                 <label><input type="checkbox"> Abusive</label><br>
    //                                 <label><input type="checkbox"> Spam</label><br>
    //                                 <label><input type="checkbox"> Copyrighted</label><br>
    //                             </div>
    //                             <div class="flag-options-symbols">
    //                                 <button class="close-flag">X</button>
    //                                 <button class="report-btn">Report</button>
    //                             </div>
    //                         </div>
    //                     </div>
    //                 </div>
    //             `;
    //             $('#dynamic-articles').prepend(newArticle);
    //         } else {
    //             alert('Error posting article');
    //         }
    //     });
    // });

    // Flag buttons
    $(document).on('click', '.flag-btn', function () {
        $(this).siblings('.flag-options').fadeToggle(200);
    });

    $(document).on('click', '.close-flag', function () {
        $(this).closest('.flag-options').fadeOut(200);
    });

    $(document).on('click', '.report-btn', function () {
        alert('Article reported successfully.');
        $(this).closest('.flag-options').hide();
    });

    // Load articles from DB (dynamic)
    function loadArticles() {
        $.getJSON('get_articles.php', function(data) {
            $('#dynamic-articles').empty();
            data.forEach(article => {
                const newArticle = $(`
                    <div class="first-article">
                        <div class="first-article-content">
                            <h3>${article.username}</h3>
                            <p class="meta">${article.username} | ${article.publishtime}</p>
                            <p class="text">${article.articletext}</p>
                        </div>
                        <div class="article-flagging">
                            <button class="flag-btn">Flag</button>
                            <div class="flag-options" style="display:none;">
                                <div class="flag-options-content">
                                    <label><input type="checkbox"> Abusive</label><br>
                                    <label><input type="checkbox"> Spam</label><br>
                                    <label><input type="checkbox"> Copyrighted</label><br>
                                </div>
                                <div class="flag-options-symbols">
                                    <button class="close-flag">X</button>
                                    <button class="report-btn">Report</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $('#dynamic-articles').append(newArticle);
            });
        });
    }

});
