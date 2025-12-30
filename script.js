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


       // Always show username section first on page load
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

    // Post new article live

     $('#post-btn').on('click', function(){
        const title = $('#article-title').val().trim();
        const text = $('#article-text').val().trim();
        if(!title || !text || !currentUser) return;

        $.post('add_article.php', {username: currentUser, title, articletext: text}, function(response){
            const res = JSON.parse(response);
            if(res.status === 'success') loadArticles();
            else alert('Error: '+res.message);
        });
    });

    // $('#post-btn').on('click', function () {
    //     const title = $('#article-title').val().trim();
    //     const text = $('#article-text').val().trim();
    //     if (!title || !text || !currentUser) return;

    //     $.post('add_article.php', {
    //         username: currentUser,
    //         title: title,
    //         articletext: text
    //     }, function (response) {
    //         try {
    //             const res = JSON.parse(response);
    //             if (res.status === 'success') {
    //                 const newArticle = `
    //                     <div class="first-article" data-articlenumber="${res.id}">
    //                         <div class="first-article-content">
    //                             <h3>${title}</h3>
    //                             <p class="meta">${currentUser} | ${res.publishtime}</p>
    //                             <p class="text">${text}</p>
    //                         </div>
    //                         <div class="article-flagging">
    //                             <div class="flag-options">
    //                                 <div class="flag-options-content">
    //                                     <label><input type="checkbox" value="abusive"> Abusive</label>
    //                                     <label><input type="checkbox" value="spam"> Spam</label>
    //                                     <label><input type="checkbox" value="copyright"> Copyrighted</label>
    //                                 </div>
    //                                 <div class="flag-options-symbols">
    //                                     <button class="close-flag">X</button>
    //                                     <button class="report-btn">Report</button>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                     </div>
    //                 `;
    //                 $('#dynamic-articles').prepend(newArticle);
    //                 $('#article-title, #article-text').val('');
    //                 $('#post-btn').prop('disabled', true);
    //             } else {
    //                 alert('Error: ' + res.message);
    //             }
    //         } catch (e) {
    //             console.error('Invalid response from add_article.php:', response);
    //             alert('Error posting article.');
    //         }
    //     });
    // });

    // Load all articles from DB
    function loadArticles() {
        $.getJSON('get_article.php', function (articles) {
            $('#dynamic-articles').empty(); // clear old ones
            articles.forEach(article => {
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
                `;
                $('#dynamic-articles').append(articleHTML);
            });
        }).fail(function(err) {
            console.error("Failed to fetch articles:", err);
        });
    }

    // Flag button actions
    $(document).on('click', '.flag-btn', function () {
        $(this).siblings('.flag-options').fadeToggle(200);
    });
    $(document).on('click', '.close-flag', function () {
        $(this).closest('.flag-options').fadeOut(200);
    });



    
    $(document).on('click', '.report-btn', function () {
        const articleDiv = $(this).closest('.first-article');
        const reportBtn = $(this); // save reference to this button


        $.post('flag_article.php', {
            username: currentUser,
          articlenumber: parseInt(articleDiv.data('articlenumber')),

            flagabusive: articleDiv.find('input[value="abusive"]').is(':checked') ? 1 : 0,
            flagspam: articleDiv.find('input[value="spam"]').is(':checked') ? 1 : 0,
            flagcopyright: articleDiv.find('input[value="copyright"]').is(':checked') ? 1 : 0
        }, function(response) {
            try {
                const res = JSON.parse(response);
                if(res.status === 'success') {
                    alert('Flag submitted');
                  
                // Update button text immediately
                reportBtn.text('Update Flag');

                // Keep the button enabled for future updates
                reportBtn.prop('disabled', false); 
                } else {
                    alert('Error: ' + res.message);
                }
            } catch(e) {
                console.error("Invalid response from flag_article.php:", response);
                alert('Error submitting flag.');
            }
        });
    });
});