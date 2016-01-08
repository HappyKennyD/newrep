
function showMoreThumbs() {
    var count = 0;
    var log = false;

    $('.thumbs-block').each(function() {
        if($(this).css('display') == 'none') {
            count++;

            if(!log) {
                log = true;

                $(this).slideDown(500);
            }
        }
    });

    if(count == 1) {
        $('.more-thumbs, #thumbs-line').hide();
    }
}

$(document).ready(function() {
    if($('.more-thumbs').length) {
        $('.dash').css('margin-left', Math.round(($('.dash').parent().width() - $('.dash').width()) / 2));
        $('.more-thumbs').css('visibility', 'visible');
    }

    $('.thumbs-block').each(function() {
        var i = 0;
        var t = this;

        $(t).children('a').children('img').each(function() {
            i++;

            if(i == 3) {
                i = 0;

                $(this).addClass('no-margin-right');
            }
        });
    });

    $('.thumbs-block').first().show();

    $('#main-person-image').mouseover(function() {
        $('#main-person-announce').show();
    }).mouseout(function() {
            $('#main-person-announce').hide();
        });

    $('#thumb-images a').click(function() {
        var media_url   = $(this).attr('href');
        var media_title = $(this).attr('title');
        var page_url    = $(this).attr('alt');

        var img = new Image();

        $('#thumbs-loader').show();

        $(img).load(function () {
            $('#main-person-image').html('');
            $('#main-person-image').append(this);
            $('#main-person-image').append('<div id="main-person-announce"><a href="' + page_url + '">' + media_title + '</a></div>');

            $('#thumbs-loader').hide();
        }).error(function () {}).attr('src', media_url);

        return false;
    });

  $('.change-category').click(function() {
        $('.search-category').toggle('normal');
    });



});
