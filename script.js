$(document).ready(function () {

    $('#signin-btn').on('click', function () {
        const username = $('#username').val().trim();

        if (username === '') return;

       $('#username-section').addClass('hidden');
        $('#articles-section').removeClass('hidden');


        $('#signed-user').text(username);
    });

});

// flag button mechanism

$('.flag-btn').on('click', function () {
$(this).closest('.article-flagging').find('.flag-options').show();

});

// X button mechanism
$('.close-flag').on('click', function () {
    $(this).closest('.flag-options').hide();
});


// test
$('.report-btn').on('click', function () {
    alert('Article reported successfully.');
    $(this).parent('.flag-options').hide();
});
