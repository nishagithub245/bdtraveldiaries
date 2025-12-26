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
    $('#username-section').addClass('hidden').css('pointer-events', 'none');
    $('#articles-section').removeClass('hidden').css('pointer-events', 'auto');

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
            title: title,
            articletext: text
        }, function (response) {

          const res = JSON.parse(response);
    if(res.status === 'success') {
        const newArticleId = res.id;

            // Expect PHP to return {status:'success', id: newID} for proper articlenumber
            // try {
            //     const res = JSON.parse(response);
            //     if (res.status === 'success') {
            //         const newArticleId = res.id; 
            //         $('#article-title').val('');
            //         $('#article-text').val('');
            //         $('#post-btn').prop('disabled', true);

                    const newArticle = $(`
                        <div class="first-article" data-articlenumber="${newArticleId}" data-author="${currentUser}">
                            <div class="first-article-content">
                                <h3>${title}</h3>
                                <p class="meta">${currentUser} | ${timeString}</p>
                                <p class="text">${text}</p>
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


        // Hide Flag button for own article
        newArticle.find(".flag-btn").remove();


    $('#dynamic-articles').prepend(newArticle.hide().fadeIn(300));
 } else {
               alert('Error posting article');
           }
    //    }catch(e){
    //        alert('Error posting article');
    //    }
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

    // Flag buttons mechanism
    //Toggle flag options
    $(document).on('click', '.flag-btn', function () {
        $(this).siblings('.flag-options').fadeToggle(200);
    });


    // Close flag box
    $(document).on('click', '.close-flag', function () {
        $(this).closest('.flag-options').fadeOut(200);
    });

    // $(document).on('click', '.report-btn', function () {
    //     alert('Article reported successfully.');
    //     $(this).closest('.flag-options').hide();
    // });



    // Submit flag
 
 $(document).on('click', '.report-btn', function () {
        
    
const articleDiv = $(this).closest('.first-article');

        // const anyChecked = articleDiv.find('input[type="checkbox"]:checked').length > 0;

        // if (!anyChecked) {
        //     alert("Please select at least one reason.");
        //     return;
        // }

        // const flags = {
        //     abusive: articleDiv.find('input[value="abusive"]').prop('checked') ? 1 : 0,
        //     spam: articleDiv.find('input[value="spam"]').prop('checked') ? 1 : 0,
        //     copyright: articleDiv.find('input[value="copyright"]').prop('checked') ? 1 : 0
        // };



//    $(document).on('click', '.report-btn', function () {

    

    $.post('flag_article.php', {
        username: currentUser,
        articlenumber: articleDiv.data('articlenumber'),
        flagabusive: articleDiv.find('input[value="abusive"]').is(':checked') ? 1 : 0,
        flagspam: articleDiv.find('input[value="spam"]').is(':checked') ? 1 : 0,
        flagcopyright: articleDiv.find('input[value="copyright"]').is(':checked') ? 1 : 0
    }, function (response) {
        alert('Flag submitted');
    });
});





    // Load articles from DB (dynamic)
    function loadArticles() {
        $.getJSON('get_articles.php', function(data) {
            $('#dynamic-articles').empty();
            data.forEach(article => {
             const newArticle = $(`
    <div class="first-article" data-articlenumber="${article.articlenumber}">
        <div class="first-article-content">
            <h3>${article.username}</h3>
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



                // Hide Flag button if the article belongs to the current user
                if (article.username === currentUser) {
                    newArticle.find(".flag-btn").remove();
                }

                
                $('#dynamic-articles').append(newArticle);
            });
        });
    }

});