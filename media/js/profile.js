    
    var submit_count = 0;
    var global_files_count = 3;
    
    function loadSelectChildren() {
        $('#select_child_block ul').empty();
        
        var total = $('#parent_id').val();
        var lang = $('#lang').val();
        $.ajax({
            url: $('#site_url').val(),
            data: { total: total, lang:lang },
            dataType : 'json',
            type: 'POST',
            success: function (html) {
                $.each(html, function(key, value) {
                    $('#select_child_block ul').append($('<li>' + value + '</li>').attr({ alt : key, onclick : 'javascript:childClick(this)' }));
                });
                
                $('#select_child_block .select-element-text').html($('#select_child_block li').first().html());
                $('#child_id').val($('#select_child_block li').first().attr('alt'));
            }
        });
    }
    
    function childClick(t) {
        $('.select-element-list').hide();
        $(t).parent().parent().parent().parent().children('.select-element-text').html($(t).html());
        $('#child_id').val($(t).attr('alt'));
    }
    
    function selectProfilePhoto() {
        $('#media').trigger('click');
    }
    
    function addMoreFiles() {
        var log = false;
        var all_count = $('.inputFile').length;
        
        $('.inputFile').each(function() {
            if(($(this).css('display') == 'none') && (!log)) {
                log = true;
                $(this).show();
            }
        });
        
        global_files_count++;
        
        var files_text = new Array();
        
        files_text[4] = 'Ещё 4 файла';
        files_text[5] = 'Ещё 3 файла';
        files_text[6] = 'Ещё 2 файла';
        files_text[7] = 'Ещё 1 файл';
        
        if(global_files_count == all_count) {
            $('#add-more-files').hide();
        }
        else {
            $('#add-more-files span').html(files_text[global_files_count]);
        }
    }
    
    function addMoreSocials() {
        var count = $('#material-files .inputText').length;
        var i = 0;
        var log = false;
        
        $('#material-files .inputText').each(function() {
            if(($(this).css('display') == 'none') && (!log)) {
                log = true;
                
                $(this).show();
            }
            
            if(!log) {
                i++;
            }
        });
        
        i++;
        
        if(i == count) {
            $('#add-more-files').hide();
        }
    }
    
    function changePassword() {
        $('#password-field').show();
        $('#password-field').focus();
    }
    
    $(document).ready(function() {
        $('#user-photo-block').mouseover(function() {
            if($('#user-photo-block a').length) {
                $('#user-photo-block a').show();
            }
        }).mouseout(function() {
            if($('#user-photo-block a').length) {
                $('#user-photo-block a').hide();
            }
        });
        
        $('#media-form').live('submit', function(e) {
            submit_count++;
            
            if(submit_count == 3) {
                this.submit();
            }
        });
        
        $('#media').change(function() {
            if($.browser.msie) {
                $('#media-form').submit();
                $('#media-form').submit();
                $('#media-form').submit();
            }
            else {
                $('#media-form').submit();
            }
        });
        
        $('.select-click-area').click(function() {
            $(this).parent().children('.select-element-list').show();
        })
        
        $('.select-element-block').mouseleave(function() {
            $(this).children('.select-element-list').hide();
        });
        
        $('.select-element-list li').click(function() {
            $('.select-element-list').hide();
            $(this).parent().parent().parent().parent().children('.select-element-text').html($(this).html());
        });
        
        $('#select_parent_block li').click(function() {
            $('#parent_id').val($(this).attr('alt'));
            
            loadSelectChildren();
        });
        
        $('#select_child_block li').click(function() {
            childClick(this);
        });
        
        /* Profile material JS */
        
        $('.file-cover').click(function() {
            $('#' + $(this).parent().attr('id') + '-input').trigger('click');
        });
        
        $('.hiddenInput').live('change', function() {
            var file_size  = this.files[0].size;
            var sizeInMB   = (file_size / (1024 * 1024)).toFixed(2) + ' Mb';
            var media_id   = this.id.substr(0, this.id.length - 6);
            var media_name = $(this).val().split('\\').pop();
            
            if(media_id == 'media1') {
                $('#media1').addClass('m-top-15');
            }
            
            $('#' + media_id + ' .input-file-name').html(media_name);
            $('#' + media_id + ' .media-size').html(sizeInMB);
            $('#' + media_id).addClass('is-selected-file');
        });
        
        $('.material-added-title a').click(function() {
            var lst = $(this).parent().parent().children('.material-files-list');
            
            if(lst.css('display') == 'none') {
                $(this).addClass('active-file-lnk');
                lst.slideDown(300);
            }
            else {
                $(this).removeClass('active-file-lnk');
                lst.slideUp(300);
            }
        });
    });