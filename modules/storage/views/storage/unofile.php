<?php $rnd = rand(100000, 999999); ?>
<div class="uploader" >
    <div class="filelist_holder" id="all_files_<?=$rnd?>"></div>
    <span id="upload_button_<?=$rnd?>">upload button</span>
    <div id="progress_<?=$rnd?>" class="upload_progressbar"></div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        // $("#progress_<?=$rnd?>").progressbar({}).hide();
        var uploader = new SWFUpload({
            post_params          :{
                "user_id":            '<?=$user_id?>'
            },
            upload_url           :"<?=Url::site('storage/upload')?>",
            flash_url            :"<?=Url::media('media/js/swfupload.swf')?>",
            file_post_name       :"Filedata",
            button_placeholder_id:"upload_button_<?=$rnd?>",
            button_image_url     :"<?=Url::media('media/theme/upload_button.png')?>",
            button_width         :100,
            button_height        :30,
            button_window_mode : SWFUpload.WINDOW_MODE.OPAQUE,
            file_types           : "*.pdf;",
            file_size_limit    : '<?=Storage::to_byte(ini_get('upload_max_filesize'))?>',
            upload_complete_handler     :function () {
                this.startUpload();
                $("#progress_<?=$rnd?>").hide();
            },
            upload_start_handler        :function () {
                $("#progress_<?=$rnd?>").show();
            },
            upload_success_handler      :function (file, data, response) {
                getFileName(data);
            },
            upload_progress_handler     :function (file, bc, bs) {
                var pgs = Math.round((bc / bs) * 100);
                //$("#progress_<?=$rnd?>").progressbar({value:pgs});
            },
            upload_error_handler        :function (file, error, message) {
                alert("Ошибка загрузки файла: " + file.name + "\nОшибка: " + error + "\n" + message);
            },
            file_dialog_complete_handler:function () {
                this.startUpload();
            },
            file_queue_error_handler :function(file, error, message)
            {
                if (error==SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT)
                {
                    alert('Файл превысил допустимый размер');
                }
            }
        });

        var storage;

        function getFileName(id)
        {
            var str;
            $.ajax({
                url    :'<?=Url::site('ajax/filename')?>',
                data   :{storage_id:id,token: "<?=Security::token(true)?>"},
                type   :'POST',
                success:function (result)
                {
                    if (result != '')
                    {
                        /*str = '<div class="clear"></div><div class="file_holder">' +
                            '<i class="unset close icon-remove"></i>' + result + '<input type="hidden" name="attachment[]" value="' + id + '" />' +
                            '</div>';
                        $("#all_files_<?=$rnd?>").append(str);*/
                        $("#pdfname").text(result);
                        $('#attachments').attr('value',id);
                    }
                },
                error  :function ()
                {
                    alert('Произошла ошибка запроса имени файла!');
                }
            });
        }

        function getCoverName(id) {

    /*         $image = new Imagick();
             $image - > readImage('***.pdf[0]');
             $image - > setImageResolution(72, 72);
             $image - > resampleImage(72, 72, imagick::FILTER_UNDEFINED, 0);
             $image - > writeImage('image72.jpg');*/

            var str;
            $.ajax({
                url: '<?=Url::site('ajax/filename')?>',
                data: {storage_id: id, token: "<?=Security::token(true)?>"},
                type: 'POST',
                success: function (result) {
                    if (result != '') {
                        /*str = '<div class="clear"></div><div class="file_holder">' +
                            '<i class="unset close icon-remove"></i>' + result + '<input type="hidden" name="attachments[]" value="' + id + '" />' +
                            '</div>';
                            */
                        $("#pdfname").text(result);
                        $('#attachments').attr('value',id);
                    }
                },
                error: function () {
                    alert('Произошла ошибка запроса имени файла!');
                }
            });
        }


        $(".unset").live('click', function()
        {
            var el = $(this).parent();
            $(el).animate({ width: ['hide', 'swing'] }, { duration: 300, complete: function(){ $(this).remove(); } })
        })
    });
</script>
