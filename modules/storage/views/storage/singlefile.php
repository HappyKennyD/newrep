<?php $rnd = rand(100000, 999999); ?>
<div class="uploader">
    <div id="all_files_<?=$rnd?>"></div>
    <span id="upload_button_<?=$rnd?>">upload button</span>
    <div id="progress_<?=$rnd?>" class="upload_progressbar"></div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
	$(document).ready(function ()
	                  {
		                  $("#progress_<?=$rnd?>").progressbar({}).hide();
		                  var uploader = new SWFUpload({
                                post_params          :{
                                    "user_id":'<?=$user_id?>'
                                },
                                file_queue_limit     : 1,
                                file_upload_limit    : 1,
                                upload_url           :"<?=Url::site('storage/upload')?>",
                                flash_url            :"<?=Url::site('media/js/swfupload.swf')?>",
                                file_post_name       :"Filedata",
                                button_placeholder_id:"upload_button_<?=$rnd?>",
                                button_image_url     :"<?=Url::site('media/theme/upload_button.png')?>",
                                button_width         :100,
                                button_height        :30,
                                button_window_mode : SWFUpload.WINDOW_MODE.OPAQUE,
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
                                    $("#progress_<?=$rnd?>").progressbar({value:pgs});
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
                                 url    :'<?=Url::site('ajax/getFileName')?>',
                                 data   :{storage_id:id},
                                 type   :'POST',
                                 success:function (result)
                                 {
                                     if (result != '')
                                     {
                                         str = "<div><img src='<?=Url::site('media/icons/cross.png')?>' class='unset' /><b>" + result + "<b><input type='hidden' id='attachment' name='attachment' value='" + id + "' /></div>";
                                         $("#all_files_<?=$rnd?>").html(str);
                                     }
                                 },
                                 error  :function ()
                                 {
                                     alert('Произошла ошибка запроса имени файла!');
                                 }
                              });
		                  }

		                  $(".unset").live('click', function()
		                  {
			                  $(this).parent().remove();
		                  })

	                  });
</script>