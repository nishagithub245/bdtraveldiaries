$(document).ready(function () {

      let currentUser = '';

    // sign in part
    $('#signin-btn').on('click', function () {
        const username = $('#username').val().trim();

        if (username === '') return;

       $('#username-section').addClass('hidden');
        $('#articles-section').removeClass('hidden');


        $('#signed-user').text(username);
    });


    // load existing articles from server(get request)
     $.get('articles.php', function (data)
     {
        $('#articles-section').prepend(data);
    });



    // enable / disable Post button based on input
$('#article-title, #article-text').on('input', function () {
    const title = $('#article-title').val().trim();
    const text = $('#article-text').val().trim();

    $('#post-btn').prop('disabled', !(title && text));
});



// post article
$('#post-btn').on('click', function () {

    const title = $('#article-title').val().trim();
    const text = $('#article-text').val().trim();
    // const username = $('#signed-user').text();

    if (!title || !text) return;


    // generate publish time
    const now = new Date();
    const timeString = now.toLocaleString('en-GB') + ' GMT';


     // send to DB asynchronously
        $.post('add_article.php', {
            username: currentUser,
            title: title,
            text: text
        });

    // live DOM insert
    const newArticle = `
        <div class="first-article">
            <div class="first-article-content">
                <h3>${title}</h3>
                <p class="meta">${username} | ${timeString}</p>
                <p class="text">${text}</p>
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
    `;

    // insert article at the top
    $('.article-title').after(articleHTML);

    // reset form
    $('#article-title').val('');
    $('#article-text').val('');
    $('#post-btn').prop('disabled', true);
});


});

// // flag button mechanism

// $('.flag-btn').on('click', function () {
// $(this).closest('.article-flagging').find('.flag-options').show();

// });

// // X button mechanism
// $('.close-flag').on('click', function () {
//     $(this).closest('.flag-options').hide();
// });



// flag button mechanism
$(document).on('click', '.flag-btn', function () {
    $(this).siblings('.flag-options').show();
});

$(document).on('click', '.close-flag', function () {
    $(this).closest('.flag-options').hide();
});

$(document).on('click', '.report-btn', function () {
    alert('Article reported successfully.');
    $(this).closest('.flag-options').hide();
});



