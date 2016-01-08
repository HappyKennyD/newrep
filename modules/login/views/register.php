<div>
    <form method="post">
        <div class="modal-header">
            <a href="<?= Url::site('/')?>" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
            <h4 class="form-signin-heading">Укажите данные для регистрации</h4>
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
            Имя пользователя:
            <input type="text" name="username" class="input-block-level" value="<?=$username?>" required>
            Электронная почта:
            <input type="text" name="email" class="input-block-level" value="<?=$email?>" required>
            Пароль:
            <input type="password" name="password" class="input-block-level" required>
            Ещё раз пароль:
            <input type="password" name="password_confirm" class="input-block-level" required>
            <img src="<?=Url::media('/captcha/default')?>" width="150" height="50" alt="Captcha" class="captcha_old" />
            <input type="text" name="captcha" required style="margin: 0; width: 147px; height: 42px; font-size:35px">
            <a onclick="reload()" style="cursor: pointer;"><img src="<?=Url::media('media/theme/refresh.png')?>" width="16px" height="16px"></a>
            <script type="text/javascript">
                function reload(){
                    var id=Math.floor(Math.random()*1000000);
                    $("img.captcha_old").attr("src","<?=Url::media('/captcha/default?id=')?>"+id);
                }
            </script>
        </div>
        <div class="modal-footer">
            <div class="pull-left style-button" style="margin-top: 5px;" id="uLogin2"
                 x-ulogin-params="display=panel;fields=first_name,last_name,photo,photo_big;optional=phone,email;providers=vkontakte,facebook,mailru,odnoklassniki,twitter,yandex,google;hidden=;redirect_uri=<?= URL::site('login/register', true) ?>"></div>
            <script src="http://ulogin.ru/js/ulogin.js"></script>
            <button class="btn btn-large btn-primary" type="submit">Регистрация</button>
        </div>
    </form>
</div>