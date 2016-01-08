<?php $rnd = rand(100000, 999999); ?>
<div class="uploader">
    <div class="filelist_holder" id="all_files_<?= $rnd ?>"></div>
    <span id="upload_button_<?= $rnd ?>">upload button</span>
    <div class="progress progress-info progress-striped" id="block_progress_<?= $rnd ?>" style="display: none">
        <div class="bar" id="bar_<?= $rnd ?>" style="width: 0%"></div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        // $("#progress_<?=$rnd?>").progressbar({}).hide();
        var uploader = new SWFUpload({
            post_params: {
                "user_id": '<?=$user_id?>'
            },
            upload_url: "<?=Url::site('storage/upload')?>",
            flash_url: "<?=Url::media('media/js/swfupload.swf')?>",
            file_post_name: "Filedata",
            button_placeholder_id: "upload_button_<?=$rnd?>",
            button_image_url     :"<?=Url::media('media/images/v1/pixel.png')?>",
            button_width: 458,
            button_height: 34,
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
            file_types: "*.mp3;*.jpg;*.jpeg;*.png;*.jpe;*.gif;*.xls;*.xlsx;*.doc;*.docx;*.ppt;*.pptx;*.rar;*.zip;*.7zip;*.7z;*.flv;*.mp4;*.3gp;*.wmv'*.avi;*.pdf;*.psd;*.odt;*.ods;*.odp;",
            file_size_limit: '<?=Storage::to_byte(ini_get('upload_max_filesize'))?>',
            upload_complete_handler: function () {

                this.startUpload();
                $("#progress_<?=$rnd?>").hide();
            },
            upload_start_handler: function () {
                $("#progress_<?=$rnd?>").show();
            },
            upload_success_handler: function (file, data, response) {
                getFileName(data);
                $("#bar_<?=$rnd?>").css({width: 0});
                $("#block_progress_<?=$rnd?>").css({'display': 'none'});
            },
            upload_progress_handler: function (file, bc, bs) {
                var pgs = Math.round(bc / bs * 100) + '%';
                $("#block_progress_<?=$rnd?>").css({'display': 'block'});
                $("#bar_<?=$rnd?>").css({width: pgs});
            },
            upload_error_handler: function (file, error, message) {
                alert("Ошибка загрузки файла: " + file.name + "\nОшибка: " + error + "\n" + message);
            },
            file_dialog_complete_handler: function () {
                this.startUpload();
            },
            file_queue_error_handler: function (file, error, message) {
                if (error == SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT) {
                    alert('Файл превысил допустимый размер');
                }
            }
        });

        var storage;
        var input_id = 0;

        function getFileName(id) {
            var str;
            $.ajax({
                url: '<?=Url::site('ajax/filename')?>',
                data: {storage_id: id, token: "<?=Security::token()?>"},
                type: 'POST',
                success: function (result) {
                    if (result != '') {
                        //var limit = parseInt($("#material_id").attr('value'));
                        var count = $('#material-files .is-selected-file').length + 1;
                        
                        input_id++;
                        
                        if (count >= 8) {
                            $("#media").hide();
                        }
                        
                        //$("#material_id").attr('value', limit)
                            var str = "<div id='media_" + input_id + "' class='inputFile is-selected-file'>";
                        str = str + "<div class='input-file-name'>" + result + "</div>";
                        str = str + "<div class='media-size-" + input_id + " media-size-block'></div>";
                        //str = str + "<div class='file-cover'></div>";
                        str = str + "<input type='hidden' name='files[]' value='" + id + "'>";
                        str = str + "<a href='javascript:cancelLoad(" + input_id + ")'></a>";
                        str = str + "</div>";

                        $("#material-files").append(str);
                        getFileSize(id, input_id);
                    }
                },
                error: function () {
                    alert('Произошла ошибка запроса имени файла!');
                }
            });
        }

        function getFileSize(id,limit) {
            var str;
            var lang = $('#lang').val();
            $.ajax({
                url: '<?=Url::site('ajax/filesize')?>',
                data: {storage_id: id, token: "<?=Security::token(true)?>", lang: lang},
                type: 'POST',
                success: function (result) {
                    if (result != '') {
                        $(".media-size-"+limit).append(result);

                    }
                },
                error: function () {
                    alert('Произошла ошибка запроса размера файла!');
                }
            });
        }

        $(".unset").live('click', function () {
            var el = $(this).parent();
            $(el).animate({ width: ['hide', 'swing'] }, { duration: 300, complete: function () {
                $(this).remove();
            } })
        })
    });
</script>
