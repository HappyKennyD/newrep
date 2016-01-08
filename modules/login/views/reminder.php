<div class="modal">
    <?php if (!$reminder):?>
    <form method="post" >
        <!--<input type="hidden" name="token_auth" value="">-->
        <div class="modal-header">
            <a href="<?=Url::site($return)?>" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
            <h4 class="form-signin-heading">Восстановление пароля</h4>
        </div>
        <div class="modal-body">
            <?php if (isset($errors)):?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $ee):?>
                        <strong>Ошибка!</strong>
                        <?=$ee?><br>
                    <?php endforeach;?>
                </div>
            <?php endif;?>
            Заполните форму, указав свой логин (никнейм), после чего ждите письмо на тот email, который вы использовали при регистрации.<br><br>
            Введите логин:
            <input type="text" name="username" class="input-block-level" required>
            <img src="<?=Url::media('/captcha/default')?>" width="150" height="50" alt="Captcha" class="captcha_old"/>
            <input type="text" name="captcha" required style="margin: 0; width: 147px; height: 42px; font-size:35px">
            <a onclick="reload()" style="cursor: pointer;"><img src="<?=Url::media('media/theme/refresh.png')?>" width="16px" height="16px"></a>
            <script type="text/javascript">
                function reload(){
                    id=Math.floor(Math.random()*1000000);
                    $("img.captcha_old").attr("src","<?=Url::media('/captcha/default?id=')?>"+id);
                }
            </script>
        </div>
        <div class="modal-footer">
            <button class="btn btn-large btn-primary" type="submit">Восстановить пароль</button>
        </div>
    </form>
    <?php else:?>
    <div class="modal-header">
        <a href="<?=Url::site($return)?>" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
        <h4 class="form-signin-heading">Восстановление пароля</h4>
    </div>
    <div class="modal-body">
        Ссылка для восстановления пароля отправлена на указанный при регистрации адрес электронной почты.
    </div>
    <? endif;?>
</div>